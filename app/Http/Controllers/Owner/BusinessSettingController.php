<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255|unique:business_users,phone,' . auth()->guard('business')->id(),
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
            'capacity_per_slot' => 'required|integer|min:1|max:100',
        ]);

        $password = $data['password'] ?? null;
        unset($data['password']);

        $business->update($data);
        $user = auth()->guard('business')->user();

        if (!empty($data['phone'])) {
            $user->update([
                'phone' => $data['phone']
            ]);
        }       

        if ($password) {
            auth()->guard('business')->user()->update([
                'password' => Hash::make($password),
            ]);
        }
        

        return back()->with('success', 'Settings updated successfully.');
    }
}