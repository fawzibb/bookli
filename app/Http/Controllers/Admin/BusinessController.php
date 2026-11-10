<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessController extends Controller
{
    public function index()
    {
        $businesses = Business::with('subscription')
            ->orderByDesc('id')
            ->get();

        return view('admin.businesses.index', compact('businesses'));
    }

    public function toggle(Business $business)
    {
        $business->update([
            'is_active' => !$business->is_active,
        ]);

        return back()->with('success', 'Business status updated.');
    }
    public function reduce(Request $request, Business $business)
{
    $request->validate([
        'days' => 'required|integer|min:1'
    ]);

    $subscription = $business->subscription;

    if ($subscription && $subscription->subscription_ends_at) {
        $subscription->subscription_ends_at =
            \Carbon\Carbon::parse($subscription->subscription_ends_at)
                ->subDays($request->days);

        $subscription->save();
    }

    return back()->with('success', 'Days reduced.');
}
public function destroy(Business $business)
{
    DB::transaction(function () use ($business) {
        // Lock the tenant before removing its dependent records.
        Business::whereKey($business->getKey())->lockForUpdate()->firstOrFail();
        // Used POS codes cannot be deleted while redemption records reference them.
        // Remove this tenant's redemptions before the business cascade reaches codes.
        DB::table('pos_code_redemptions')
            ->where('business_id', $business->getKey())
            ->delete();

        $business->subscription()->delete();
        $business->users()->delete();
        $business->bookings()->delete();
        $business->services()->delete();
        $business->orders()->delete();
        $business->menuItems()->delete();
        $business->categories()->delete();
        $business->delete();
    });

    return back()->with('success', 'Business deleted.');
}

    public function extend(Request $request, Business $business)
    {
        $request->validate([
            'days' => ['required', 'integer', 'min:1'],
        ]);

        $subscription = Subscription::firstOrCreate(
            ['business_id' => $business->id],
            [
                'status' => 'active',
            ]
        );

        $baseDate =
            $subscription->subscription_ends_at &&
            now()->lt($subscription->subscription_ends_at)
                ? $subscription->subscription_ends_at
                : now();

        $newEnd = \Carbon\Carbon::parse($baseDate)->addDays($request->days);

        $subscription->update([
            'subscription_starts_at' => now(),
            'subscription_ends_at' => $newEnd,
            'status' => 'active',
        ]);

        return back()->with('success', 'Subscription extended.');
    }
}