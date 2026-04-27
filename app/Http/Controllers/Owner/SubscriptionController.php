<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ActivationCode;
use App\Models\ActivationCodeRedemption;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SiteSetting;

class SubscriptionController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::pluck('value', 'key')->toArray();
        $businessId = Auth::guard('business')->user()->business_id;

        $subscription = Subscription::where('business_id', $businessId)->first();

        $today = now();

        $remainingDays = 0;
        $prices = [
            'monthly' => $settings['price_monthly'] ?? null,
            'six_months' => $settings['price_six_months'] ?? null,
            'yearly' => $settings['price_yearly'] ?? null,
        ];

        $contact = [
            'phone' => $settings['contact_phone'] ?? null,
            'email' => $settings['contact_email'] ?? null,
        ];

        if ($subscription) {
            $endDate =
                $subscription->subscription_ends_at ??
                $subscription->trial_ends_at;

            if ($endDate && Carbon::parse($endDate)->gte($today)) {
                $remainingDays = $today->diffInDays(Carbon::parse($endDate));
            }
        }

        return view('owner.subscription.index', compact(
            'subscription',
            'remainingDays',
            'prices',
            'contact'
        ));
    }

    public function redeem(Request $request)
    {
        $businessId = Auth::guard('business')->user()->business_id;

        $request->validate([
            'code' => ['required'],
        ]);

        $code = ActivationCode::where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (!$code) {
            return back()->withErrors([
                'code' => 'Invalid code.',
            ]);
        }

        if ($code->expires_at && now()->gt($code->expires_at)) {
            return back()->withErrors([
                'code' => 'This code has expired.',
            ]);
        }

        if ($code->used_count >= $code->max_uses) {
            return back()->withErrors([
                'code' => 'This code reached max uses.',
            ]);
        }

        $subscription = Subscription::where('business_id', $businessId)->firstOrFail();

        $baseDate =
            $subscription->subscription_ends_at &&
            now()->lt($subscription->subscription_ends_at)
                ? Carbon::parse($subscription->subscription_ends_at)
                : now();

        $newEndDate = $baseDate->copy()->addDays($code->days);

        $subscription->update([
            'subscription_starts_at' => now(),
            'subscription_ends_at' => $newEndDate,
            'status' => 'active',
        ]);

        $code->increment('used_count');

        ActivationCodeRedemption::create([
            'activation_code_id' => $code->id,
            'business_id' => $businessId,
            'redeemed_at' => now(),
        ]);

        return back()->with('success', 'Code activated successfully.');
    }
}