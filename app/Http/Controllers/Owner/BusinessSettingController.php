<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class BusinessSettingController extends Controller
{
    public function index()
    {
        $business = auth()->guard('business')->user()->business;

        return view('owner.settings.index', compact('business'));
    }

    public function update(Request $request)
    {
        $business = auth()->guard('business')->user()->business;

        $settings = BusinessSetting::firstOrCreate([
            'business_id' => $business->id,
        ]);

        $user = auth()->guard('business')->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255|unique:business_users,phone,' . $user->id,
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'currency' => 'required|string|max:10',
        ]);

        $password = $data['password'] ?? null;
        $currency = $data['currency'];

        unset($data['password'], $data['currency']);

        if ($request->remove_logo == 1) {
            if ($business->logo && Storage::disk('public')->exists($business->logo)) {
                Storage::disk('public')->delete($business->logo);
            }

            $data['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($business->logo && Storage::disk('public')->exists($business->logo)) {
                Storage::disk('public')->delete($business->logo);
            }

            $data['logo'] = $request->file('logo')->store('business-logos', 'public');
        }

        if (!$request->hasFile('logo') && $request->remove_logo != 1) {
            unset($data['logo']);
        }

        $business->update($data);

        $settings->update([
            'currency' => $currency,
        ]);

        if (!empty($data['phone'])) {
            $user->update([
                'phone' => $data['phone'],
            ]);
        }

        if ($password) {
            $user->update([
                'password' => Hash::make($password),
            ]);
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}