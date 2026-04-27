<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Subscription;
use Illuminate\Http\Request;

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
    $business->subscription()?->delete();
    $business->users()->delete();
    $business->services()->delete();
    $business->bookings()->delete();
    $business->categories()->delete();
    $business->menuItems()->delete();
    $business->orders()->delete();

    $business->delete();

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