<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::pluck('value', 'key')->toArray();

        return view('admin.settings.index', compact('settings'));
    }

public function update(Request $request)
{
    $data = $request->validate([
        'price_monthly' => 'nullable|numeric|min:0',
        'price_six_months' => 'nullable|numeric|min:0',
        'price_yearly' => 'nullable|numeric|min:0',
    ]);

    foreach ($data as $key => $value) {
        SiteSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    return back()->with('success', 'Settings updated successfully.');
}
}