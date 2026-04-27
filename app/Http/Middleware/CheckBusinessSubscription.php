<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBusinessSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('business')->user();

        if (!$user) {
            return redirect('/login');
        }

        if ($request->routeIs('owner.subscription.*')) {
            return $next($request);
        }

        $subscription = Subscription::where('business_id', $user->business_id)->first();

        if (!$subscription) {
            return redirect()->route('owner.subscription.index');
        }

        $now = now();

        $trialValid =
            !empty($subscription->trial_ends_at) &&
            $now->lte($subscription->trial_ends_at);

        $paidValid =
            !empty($subscription->subscription_ends_at) &&
            $now->lte($subscription->subscription_ends_at);

        if ($trialValid || $paidValid) {
            return $next($request);
        }

        return redirect()
            ->route('owner.subscription.index')
            ->withErrors([
                'code' => 'Your subscription has expired.',
            ]);
    }
}