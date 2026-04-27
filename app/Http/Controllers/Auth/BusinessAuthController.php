<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\BusinessType;
use App\Models\BusinessUser;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class BusinessAuthController extends Controller
{
    public function showSignup()
{
    if (auth()->guard('business')->check()) {
        return redirect()->route('dashboard');
    }

    if (auth()->guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }

    $businessTypes = BusinessType::where('is_active', true)
        ->orderBy('name')
        ->get();

    return view('auth.signup', compact('businessTypes'));
}

    public function signup(Request $request)
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_type_id' => ['required', 'exists:business_types,id'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'phone' => ['required', 'unique:business_users,phone'],
            'password' => ['required', 'min:6'],
        ]);

        $businessType = BusinessType::where('id', $request->business_type_id)
            ->where('is_active', true)
            ->firstOrFail();

        $mode = $businessType->mode;

        $slug = Str::slug($request->business_name);
        $originalSlug = $slug;
        $counter = 1;

        while (Business::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $business = Business::create([
            'business_type_id' => $businessType->id,
            'business_type' => $businessType->slug,
            'name' => $request->business_name,
            'slug' => $slug,
            'mode' => $mode,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        $user = BusinessUser::create([
            'business_id' => $business->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_owner' => true,
            'is_active' => true,
        ]);

        Subscription::create([
            'business_id' => $business->id,
            'trial_starts_at' => now(),
            'trial_ends_at' => now()->addDays(20),
            'status' => 'trial',
        ]);

        BusinessSetting::create([
            'business_id' => $business->id,
            'booking_enabled' => $mode === 'booking',
            'ordering_enabled' => $mode === 'menu',
        ]);

        Auth::guard('business')->login($user);

        return redirect()->route('dashboard');
    }

    public function showLogin()
    {
        if (auth()->guard('business')->check()) {
            return redirect()->route('dashboard');
        }

        if (auth()->guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::guard('business')->attempt($credentials, true)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'phone' => 'Invalid credentials',
        ]);
    }

    public function dashboard()
    {
        $user = auth()->guard('business')->user();
        $business = $user->business;

        $statusNow = $this->isBusinessOpenNow($business);

        return view('dashboard.index', compact('user', 'statusNow'));
    }

    public function logout(Request $request)
    {
        Auth::guard('business')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function isBusinessOpenNow($business): bool
    {
        $now = now('Asia/Beirut');
        $day = $now->dayOfWeek;

        $schedule = \App\Models\WeeklySchedule::where('business_id', $business->id)
            ->where('day_of_week', $day)
            ->first();

        if (!$schedule || $schedule->is_off || !$schedule->open_time || !$schedule->close_time) {
            return false;
        }

        $open = \Carbon\Carbon::parse($now->toDateString() . ' ' . $schedule->open_time, 'Asia/Beirut');
        $close = \Carbon\Carbon::parse($now->toDateString() . ' ' . $schedule->close_time, 'Asia/Beirut');

        if ($now->lt($open) || $now->gte($close)) {
            return false;
        }

        return true;
    }
}