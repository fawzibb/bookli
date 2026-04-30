<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicPageController extends Controller
{
    public function index()
    {
        $user = Auth::guard('business')->user();
        $business = $user->business;

        $settings = BusinessSetting::firstOrCreate(
            ['business_id' => $business->id],
            [
                'booking_enabled' => $business->mode === 'booking',
                'ordering_enabled' => $business->mode === 'menu',
                'public_theme' => 'default',
                'group_services_on_public_page' => false, // 👈 جديد
            ]
        );

        $themes = ['default', 'modern', 'elegant', 'luxury'];

        return view('owner.public-page.index', compact('business', 'settings', 'themes'));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('business')->user();
        $business = $user->business;

        $request->validate([
            'public_theme' => ['required', 'in:default,modern,elegant,luxury'],
            'public_tagline' => ['nullable','string','max:255'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'background_color' => ['nullable', 'string', 'max:20'],
            'text_color' => ['nullable', 'string', 'max:20'],
            'card_color' => ['nullable', 'string', 'max:20'],
            'button_color' => ['nullable', 'string', 'max:20'],
            'font_family' => ['nullable', 'string', 'max:100'],
            'border_radius' => ['nullable', 'string', 'max:20'],

            // 👇 الجديد
            'group_services_on_public_page' => ['nullable', 'boolean'],
        ]);

        $settings = BusinessSetting::firstOrCreate(
            ['business_id' => $business->id],
            [
                'booking_enabled' => $business->mode === 'booking',
                'ordering_enabled' => $business->mode === 'menu',
                'public_theme' => 'default',
                'group_services_on_public_page' => false,
            ]
        );

        $settings->update([
            'public_theme' => $request->public_theme,
            'primary_color' => $request->primary_color,
            'secondary_color' => $request->secondary_color,
            'background_color' => $request->background_color,
            'text_color' => $request->text_color,
            'card_color' => $request->card_color,
            'button_color' => $request->button_color,
            'font_family' => $request->font_family,
            'border_radius' => $request->border_radius,
            'public_tagline' => $request->public_tagline,

            // 👇 الجديد
            'group_services_on_public_page' => $request->boolean('group_services_on_public_page'),
        ]);

        return back()->with('success', 'Public page design updated successfully.');
    }
}