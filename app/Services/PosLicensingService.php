<?php
namespace App\Services;

use App\Exceptions\PosLicenseException;
use App\Models\{Business, BusinessUser, PosActivationCode, PosDevice, PosSubscription};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\{DB, Hash};
use Illuminate\Support\Str;

class PosLicensingService
{
    public function __construct(private PosLicenseSigner $signer) {}
    private function business(int $id): Business
    {
        $business = Business::whereKey($id)->lockForUpdate()->firstOrFail();
        if (!$business->is_active) throw new PosLicenseException('business_disabled', 'This business is disabled. Contact Bookli support.');
        return $business;
    }
    private function subscription(int $businessId): PosSubscription
    {
        return PosSubscription::firstOrCreate(['business_id' => $businessId], ['status' => 'inactive', 'device_limit' => 1]);
    }
    private function requireValid(PosSubscription $subscription): void
    {
        if ($subscription->status === 'suspended') throw new PosLicenseException('pos_suspended', 'POS access is suspended. Contact Bookli support.');
        if (!$subscription->is_valid) throw new PosLicenseException('pos_expired', 'Activate or renew your POS subscription.', 402);
    }
    public function owner(string $phone, string $password): BusinessUser
    {
        $user = BusinessUser::where('phone', $phone)->first();
        if (!$user || !Hash::check($password, $user->password) || !$user->is_active || !$user->is_owner)
            throw new PosLicenseException('invalid_credentials', 'Invalid owner credentials.', 401);
        return $user;
    }
    public function configure(Business $business, int $limit, bool $suspended): void
    {
        if ($limit < 1 || $limit > 100) throw new PosLicenseException('invalid_device_limit', 'Choose between 1 and 100 devices.', 422);
        DB::transaction(function () use ($business, $limit, $suspended) {
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $subscription = $this->subscription($business->id);
            if (PosDevice::where('business_id', $business->id)->where('status', 'active')->count() > $limit)
                throw new PosLicenseException('devices_exceed_limit', 'Revoke extra devices before reducing the device limit.', 409);
            $subscription->device_limit = $limit;
            $subscription->status = $suspended ? 'suspended' : ($subscription->ends_at && now()->lt($subscription->ends_at) ? 'active' : 'inactive');
            $subscription->license_version++;
            $subscription->save();
        }, 3);
    }
    public function createCode(Business $business, ?CarbonImmutable $expires): string
    {
        return DB::transaction(function () use ($business, $expires) {
            $this->business($business->id); $this->subscription($business->id);
            $code = 'POS-'.implode('-', str_split(strtoupper(bin2hex(random_bytes(16))), 4));
            PosActivationCode::create(['business_id' => $business->id, 'code_hash' => hash('sha256', $code),
                'hint' => 'POS-****-'.substr($code, -4), 'expires_at' => $expires]);
            return $code;
        }, 3);
    }
    public function redeem(BusinessUser $owner, string $input): PosSubscription
    {
        if (!$owner->is_owner || !$owner->is_active) throw new PosLicenseException('owner_required', 'An active business owner is required.');
        return DB::transaction(function () use ($owner, $input) {
            $this->business($owner->business_id);
            $subscription = $this->subscription($owner->business_id);
            if ($subscription->status === 'suspended') throw new PosLicenseException('pos_suspended', 'Contact Bookli support to resume POS access.');
            $normalized = strtoupper(preg_replace('/\s+/', '', trim($input)));
            $code = PosActivationCode::where('code_hash', hash('sha256', $normalized))->where('business_id', $owner->business_id)->lockForUpdate()->first();
            if (!$code || !$code->is_active || ($code->expires_at && now()->gte($code->expires_at)))
                throw new PosLicenseException('invalid_pos_code', 'This POS code is invalid, expired or belongs to another business.', 422);
            if ($code->redeemed_at) throw new PosLicenseException('pos_code_used', 'This POS code was already used. Check the current subscription.', 409);
            $now = CarbonImmutable::now('UTC');
            $previous = $subscription->ends_at;
            $base = $previous && $previous->gt($now) ? $previous : $now;
            $newEnd = $base->addYearNoOverflow();
            $claimed = PosActivationCode::whereKey($code->id)->whereNull('redeemed_at')->update(['redeemed_at' => $now]);
            if ($claimed !== 1) throw new PosLicenseException('pos_code_used', 'This POS code was already used.', 409);
            $subscription->fill(['status' => 'active', 'starts_at' => $subscription->starts_at ?? $now, 'ends_at' => $newEnd]);
            $subscription->license_version++; $subscription->save();
            DB::table('pos_code_redemptions')->insert(['pos_activation_code_id' => $code->id, 'business_id' => $owner->business_id,
                'business_user_id' => $owner->id, 'previous_ends_at' => $previous, 'new_ends_at' => $newEnd, 'redeemed_at' => $now]);
            return $subscription;
        }, 3);
    }
    public function activate(BusinessUser $owner, string $installation, string $name, string $role): array
    {
        if (!$owner->is_owner || !$owner->is_active) throw new PosLicenseException('owner_required', 'An active business owner is required.');
        if (!in_array($role, ['primary', 'terminal'], true)) throw new PosLicenseException('invalid_role', 'Choose a primary or terminal device.', 422);
        $this->signer->assertReady();
        return DB::transaction(function () use ($owner, $installation, $name, $role) {
            $business = $this->business($owner->business_id);
            $subscription = $this->subscription($business->id); $this->requireValid($subscription);
            $device = PosDevice::where('business_id', $business->id)->where('installation_id', $installation)->first();
            if ($device && $device->role !== $role) throw new PosLicenseException('device_role_conflict', 'A registered installation cannot change its device role.', 409);
            $count = PosDevice::where('business_id', $business->id)->where('status', 'active')->when($device, fn($query) => $query->where('id', '<>', $device->id))->count();
            if ($count >= $subscription->device_limit) throw new PosLicenseException('device_limit_reached', 'This business has reached its POS device limit.', 409);
            $primary = PosDevice::where('business_id', $business->id)->whereKey($subscription->primary_device_id)->where('status', 'active')->first();
            if ($role === 'primary' && $primary && $primary->id !== $device?->id)
                throw new PosLicenseException('primary_already_registered', 'Revoke the previous primary device before registering a replacement.', 409);
            if ($role === 'terminal' && !$primary) throw new PosLicenseException('primary_required', 'Activate the primary device first.', 409);
            $token = bin2hex(random_bytes(32));
            $device ??= new PosDevice(['uuid' => (string) Str::uuid(), 'business_id' => $business->id, 'installation_id' => $installation, 'role' => $role]);
            $device->fill(['name' => trim($name), 'status' => 'active', 'revoked_at' => null, 'token_hash' => hash('sha256', $token)]); $device->save();
            if ($role === 'primary' && $subscription->primary_device_id !== $device->id) {
                $subscription->primary_device_id = $device->id; $subscription->license_version++; $subscription->save();
            }
            return ['device_token' => $token] + $this->issue($business, $subscription, $device);
        }, 3);
    }
    public function validateDevice(PosDevice $known, string $tokenHash): array
    {
        return DB::transaction(function () use ($known, $tokenHash) {
            $business = $this->business($known->business_id);
            $subscription = $this->subscription($business->id);
            $device = PosDevice::whereKey($known->id)->lockForUpdate()->firstOrFail();
            if ($device->status !== 'active' || !$device->token_hash || !hash_equals($device->token_hash, $tokenHash))
                throw new PosLicenseException('device_revoked', 'This device token is no longer valid.', 401);
            $this->requireValid($subscription);
            if ($device->role === 'terminal' && !PosDevice::whereKey($subscription->primary_device_id)->where('business_id', $business->id)->where('status', 'active')->exists())
                throw new PosLicenseException('primary_required', 'The primary device is not registered as active.', 409);
            return $this->issue($business, $subscription, $device);
        }, 3);
    }
    private function issue(Business $business, PosSubscription $subscription, PosDevice $device): array
    {
        $now = CarbonImmutable::now('UTC');
        $deadline = $now->addDays(config('pos.offline_days'))->min($subscription->ends_at);
        $claims = ['iss' => config('pos.issuer'), 'aud' => config('pos.audience'), 'sub' => $device->uuid,
            'business_id' => $business->id, 'device_id' => $device->uuid, 'installation_id' => $device->installation_id,
            'role' => $device->role, 'primary_device_id' => PosDevice::whereKey($subscription->primary_device_id)->value('uuid'),
            'device_limit' => $subscription->device_limit, 'license_version' => $subscription->license_version,
            'iat' => $now->timestamp, 'nbf' => $now->timestamp, 'exp' => $deadline->timestamp,
            'subscription_ends_at' => $subscription->ends_at->timestamp, 'offline_until' => $deadline->timestamp];
        $license = $this->signer->sign($claims);
        $device->last_verified_at = $now; $device->save();
        return ['license' => $license, 'server_time' => $now->toIso8601String(),
            'subscription_ends_at' => $subscription->ends_at->toIso8601String(), 'offline_until' => $deadline->toIso8601String(),
            'device' => ['id' => $device->uuid, 'name' => $device->name, 'role' => $device->role],
            'business' => ['id' => $business->id, 'name' => $business->name], 'signing_key_id' => $this->signer->keyId()];
    }
    public function revoke(Business $business, PosDevice $device): void
    {
        DB::transaction(function () use ($business, $device) {
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $current = PosDevice::whereKey($device->id)->where('business_id', $business->id)->firstOrFail();
            $current->update(['status' => 'revoked', 'token_hash' => null, 'revoked_at' => now()]);
            $subscription = $this->subscription($business->id);
            if ($subscription->primary_device_id == $current->id) $subscription->primary_device_id = null;
            $subscription->license_version++; $subscription->save();
        }, 3);
    }
}
