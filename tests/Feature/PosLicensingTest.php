<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Hash};
use Illuminate\Support\Str;
use App\Models\{Business, BusinessUser, PosSubscription, PosDevice, PosActivationCode};
use App\Services\{PosLicensingService, PosLicenseSigner};
use App\Exceptions\PosLicenseException;

class PosLicensingTest extends TestCase
{
    use RefreshDatabase;
    private PosLicensingService $service;
    private BusinessUser $owner;
    private string $key;
    public function createApplication(): \Illuminate\Foundation\Application
    {
        $app = parent::createApplication();
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        return $app;
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 11, 7)->startOfDay());
        $this->key = storage_path('framework/testing-pos-'.Str::uuid().'.pem');
        $rsa = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'config' => config('pos.openssl_config')]);
        openssl_pkey_export($rsa, $pem, null, ['config' => config('pos.openssl_config')]);
        file_put_contents($this->key, $pem);
        config(['pos.private_key_path' => $this->key]);
        $this->service = app(PosLicensingService::class);
        $this->owner = $this->makeOwner();
    }
    protected function tearDown(): void
    {
        if (isset($this->key) && is_file($this->key)) unlink($this->key);
        parent::tearDown();
    }
    private function makeOwner(): BusinessUser
    {
        $id = (string) Str::uuid();
        $business = Business::create(['name' => 'Test shop', 'slug' => $id, 'business_type' => 'restaurant', 'mode' => 'menu', 'phone' => $id, 'is_active' => true]);
        return BusinessUser::create(['business_id' => $business->id, 'first_name' => 'Test', 'last_name' => 'Owner', 'phone' => $id, 'password' => Hash::make('secret123'), 'is_owner' => true, 'is_active' => true]);
    }
    private function renew(): PosSubscription
    {
        return $this->service->redeem($this->owner, $this->service->createCode($this->owner->business, null));
    }
    private function activate(string $role = 'primary', ?string $installation = null): array
    {
        return $this->service->activate($this->owner, $installation ?? (string) Str::uuid(), 'Till 1', $role);
    }
    private function denial(string $reason, callable $action): void
    {
        try { $action(); $this->fail('Expected denial: '.$reason); }
        catch (PosLicenseException $error) { $this->assertSame($reason, $error->reason); }
    }
    public function test_annual_codes_are_tenant_scoped_single_use_and_independent_of_website(): void
    {
        DB::table('subscriptions')->insert(['business_id' => $this->owner->business_id, 'status' => 'expired', 'subscription_ends_at' => now()->subYear()]);
        $code = $this->service->createCode($this->owner->business, null);
        $other = $this->makeOwner();
        $this->denial('invalid_pos_code', fn() => $this->service->redeem($other, $code));
        $sub = $this->service->redeem($this->owner, strtolower($code));
        $this->assertTrue($sub->ends_at->equalTo(now()->addYear()));
        $this->denial('pos_code_used', fn() => $this->service->redeem($this->owner, $code));
        $this->assertTrue($this->renew()->ends_at->equalTo(now()->addYears(2)));
        $this->assertSame('expired', DB::table('subscriptions')->value('status'));
        $this->assertDatabaseCount('pos_code_redemptions', 2);
        $this->assertStringNotContainsString($code, PosActivationCode::first()->toJson());
    }
    public function test_signed_license_has_seven_day_limit_and_valid_signature(): void
    {
        $this->renew(); $result = $this->activate();
        [$header, $payload, $signature] = explode('.', $result['license']);
        $decode = fn($v) => base64_decode(strtr($v, '-_', '+/'));
        $claims = json_decode($decode($payload), true);
        $this->assertSame(7 * 86400, $claims['exp'] - $claims['iat']);
        $this->assertSame($this->owner->business_id, $claims['business_id']);
        $this->assertSame(1, openssl_verify($header.'.'.$payload, $decode($signature), app(PosLicenseSigner::class)->publicKey(), OPENSSL_ALGO_SHA256));
        $this->assertSame('RS256', json_decode($decode($header), true)['alg']);
        $this->assertNotSame($result['device_token'], PosDevice::first()->token_hash);
        $this->withToken($result['device_token'])->getJson('/api/pos/license')->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    }
    public function test_lease_is_capped_at_subscription_expiry_and_expired_devices_are_denied(): void
    {
        $sub = $this->renew(); $sub->update(['ends_at' => now()->addHours(2)]);
        $result = $this->activate();
        $this->assertSame($result['subscription_ends_at'], $result['offline_until']);
        $this->travel(3)->hours();
        $this->withToken($result['device_token'])->getJson('/api/pos/license')->assertStatus(402)->assertJsonPath('error', 'pos_expired');
    }
    public function test_device_limits_token_rotation_and_primary_rules(): void
    {
        $this->renew(); $installation = (string) Str::uuid();
        $this->denial('primary_required', fn() => $this->activate('terminal'));
        $first = $this->activate('primary', $installation);
        $rotated = $this->activate('primary', $installation);
        $this->assertDatabaseCount('pos_devices', 1);
        $this->withToken($first['device_token'])->getJson('/api/pos/license')->assertUnauthorized();
        $this->denial('device_limit_reached', fn() => $this->activate('terminal'));
        $this->service->configure($this->owner->business, 2, false);
        $this->denial('primary_already_registered', fn() => $this->activate());
        $this->activate('terminal');
        $this->denial('devices_exceed_limit', fn() => $this->service->configure($this->owner->business, 1, false));
        $primary = PosDevice::where('role', 'primary')->first();
        $this->service->revoke($this->owner->business, $primary);
        $this->withToken($rotated['device_token'])->getJson('/api/pos/license')->assertUnauthorized();
        $terminal = PosDevice::where('role', 'terminal')->first();
        $this->denial('primary_required', fn() => $this->service->validateDevice($terminal, $terminal->token_hash));
        $this->activate();
        $this->assertSame(2, PosDevice::where('status', 'active')->count());
    }
    public function test_suspension_disabled_business_and_missing_key_fail_closed(): void
    {
        $this->renew();
        config(['pos.private_key_path' => $this->key.'.missing']);
        try { $this->activate(); $this->fail('Missing key must fail'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) { $this->assertSame(503, $error->getStatusCode()); }
        $this->assertDatabaseCount('pos_devices', 0);
        config(['pos.private_key_path' => $this->key]);
        $this->service->configure($this->owner->business, 1, true);
        $this->denial('pos_suspended', fn() => $this->activate());
        $this->owner->business->update(['is_active' => false]);
        $this->denial('business_disabled', fn() => $this->activate());
    }
    public function test_owner_page_works_without_website_subscription_and_scopes_device_revocation(): void
    {
        $this->renew(); $this->activate();
        $this->withoutVite();
        $this->actingAs($this->owner, 'business')->get('/owner/pos-subscription')->assertOk()->assertSee('POS subscription');
        $other = $this->makeOwner();
        $this->actingAs($other, 'business')->post('/owner/pos-subscription/devices/'.PosDevice::first()->id.'/revoke')->assertNotFound();
        $other->update(['is_owner' => false]);
        $this->actingAs($other->fresh(), 'business')->get('/owner/pos-subscription')->assertForbidden();
    }
    public function test_api_activation_requires_owner_credentials_and_valid_input(): void
    {
        $this->renew();
        $input = ['phone' => $this->owner->phone, 'password' => 'bad', 'installation_id' => (string) Str::uuid(), 'name' => 'Register', 'role' => 'primary'];
        $this->postJson('/api/pos/activate', $input)->assertUnauthorized();
        $input['password'] = 'secret123';
        $this->postJson('/api/pos/activate', $input)->assertOk()->assertJsonStructure(['license', 'device_token', 'offline_until']);
        $this->postJson('/api/pos/activate', ['phone' => 'x'])->assertUnprocessable();
    }
    public function test_expired_and_disabled_codes_cannot_be_redeemed(): void
    {
        $code = $this->service->createCode($this->owner->business, now()->toImmutable()->subMinute());
        $this->denial('invalid_pos_code', fn() => $this->service->redeem($this->owner, $code));
        $code = $this->service->createCode($this->owner->business, null);
        PosActivationCode::where('code_hash', hash('sha256', $code))->update(['is_active' => false]);
        $this->denial('invalid_pos_code', fn() => $this->service->redeem($this->owner, $code));
        $this->assertDatabaseCount('pos_code_redemptions', 0);
    }
    public function test_admin_controls_are_protected_and_render_without_exposing_secrets(): void
    {
        $this->get('/admin/pos')->assertRedirect();
        $admin = \App\Models\Admin::create(['name' => 'Admin', 'username' => 'licensing-test', 'password' => Hash::make('test')]);
        $this->withoutVite();
        $this->actingAs($admin, 'admin')->get('/admin/pos')->assertOk()->assertSee('POS subscriptions');
        $this->actingAs($admin, 'admin')->patch('/admin/pos/'.$this->owner->business_id, ['device_limit' => 3])->assertRedirect();
        $this->assertSame(3, PosSubscription::first()->device_limit);
        $this->actingAs($admin, 'admin')->post('/admin/pos/'.$this->owner->business_id.'/codes')->assertRedirect()->assertSessionHas('new_pos_code');
        $other = $this->makeOwner();
        $this->actingAs($admin, 'admin')->post('/admin/pos/'.$other->business_id.'/codes/'.PosActivationCode::first()->id.'/disable')->assertNotFound();
        $this->get('/admin/pos')->assertOk()->assertDontSee(PosActivationCode::first()->code_hash);
    }
    public function test_failed_redemption_rolls_back_code_and_subscription(): void
    {
        $code = $this->service->createCode($this->owner->business, null);
        DB::statement("CREATE TRIGGER reject_pos_redemption BEFORE INSERT ON pos_code_redemptions BEGIN SELECT RAISE(ABORT, 'test failure'); END");
        try { $this->service->redeem($this->owner, $code); $this->fail('Expected database failure'); }
        catch (\Illuminate\Database\QueryException $error) { $this->assertStringContainsString('test failure', $error->getMessage()); }
        $this->assertNull(PosActivationCode::first()->redeemed_at);
        $this->assertNull(PosSubscription::first()->ends_at);
        DB::statement('DROP TRIGGER reject_pos_redemption');
        $this->assertTrue($this->service->redeem($this->owner, $code)->is_valid);
    }
    public function test_key_generation_never_replaces_existing_keys(): void
    {
        $before = hash_file('sha256', $this->key);
        $this->artisan('pos:generate-keys')->assertFailed();
        $this->assertSame($before, hash_file('sha256', $this->key));
        $temporary = $this->key.'.new';
        config(['pos.private_key_path' => $temporary, 'pos.public_key_path' => $temporary.'.pub']);
        try {
            $this->artisan('pos:generate-keys')->assertSuccessful();
            $this->assertFileExists($temporary.'.pub');
            $this->assertSame(3072, openssl_pkey_get_details(openssl_pkey_get_private(file_get_contents($temporary)))['bits']);
        } finally {
            if (is_file($temporary)) unlink($temporary);
            if (is_file($temporary.'.pub')) unlink($temporary.'.pub');
        }
    }
    public function test_existing_home_and_subscription_pages_render_with_their_database_fixture(): void
    {
        $this->withoutVite();
        $this->get('/')->assertOk();
        DB::table('subscriptions')->insert(['business_id' => $this->owner->business_id, 'status' => 'expired']);
        $this->actingAs($this->owner, 'business')->get('/owner/subscription')->assertOk()->assertSee('POS subscription (separate)');
    }
}
