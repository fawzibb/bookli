<x-layouts.app>
<x-slot name="title">Public Page Design</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Owner Panel</p>

<a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>

@if($business->mode === 'booking')
    <a class="nav-link" href="{{ route('owner.bookings.index') }}">{{ __('messages.bookings') }}</a>
    <a class="nav-link" href="{{ route('owner.services.index') }}">{{ __('messages.services') }}</a>
    <a class="nav-link" href="{{ route('owner.service-groups.index') }}">{{ __('messages.service_groups') }}</a>
    <a class="nav-link" href="{{ route('owner.schedules.index') }}">{{ __('messages.schedules') }}</a>
@else
    <a class="nav-link" href="{{ route('owner.categories.index') }}">{{ __('messages.categories') }}</a>
    <a class="nav-link" href="{{ route('owner.menu_items.index') }}">{{ __('messages.menu_items') }}</a>
    <a class="nav-link" href="{{ route('owner.orders.index') }}">{{ __('messages.orders') }}</a>
    <a class="nav-link" href="{{ route('owner.schedules.index') }}">{{ __('messages.opening_hours') }}</a>
@endif

<a class="nav-link" href="{{ route('owner.qrcode') }}">{{ __('messages.qr_code') }}</a>
<a class="nav-link" href="{{ route('owner.subscription.index') }}">{{ __('messages.subscription') }}</a>
<a class="nav-link active" href="{{ route('owner.public-page.index') }}">{{ __('messages.public_page_design') }}</a>
<a class="nav-link" href="{{ route('owner.settings.index') }}">{{ __('messages.settings') }}</a>

<form method="POST" action="{{ route('logout') }}" style="margin-top:18px;">
    @csrf
    <button class="btn" style="width:100%;">{{ __('messages.logout') }}</button>
</form>
</x-slot>

@php
    $currentTheme = $settings->public_theme ?? 'default';

    $primaryColor = $settings->primary_color ?? '#111827';
    $secondaryColor = $settings->secondary_color ?? '#1f2937';
    $backgroundColor = $settings->background_color ?? '#f5f7fb';
    $textColor = $settings->text_color ?? '#111827';
    $cardColor = $settings->card_color ?? '#ffffff';
    $buttonColor = $settings->button_color ?? $primaryColor;
    $fontFamily = $settings->font_family ?? 'Arial';
    $borderRadius = $settings->border_radius ?? '18px';
@endphp

<div class="topbar">
    <div>
        <h1 class="page-title">{{ __('messages.public_page_design') }}</h1>
        <p class="page-subtitle">{{ __('messages.public_design_desc') }}</p>
    </div>

    <a class="btn-light btn" target="_blank" href="{{ route('business.public', $business->slug) }}">
        {{ __('messages.preview_public_page') }}
    </a>
</div>

<div class="grid">

    <div class="card">
        <h3>{{ __('messages.current_settings') }}</h3>

        <p><strong>{{ __('messages.business') }}:</strong> {{ $business->name }}</p>
        <p><strong>{{ __('messages.mode') }}:</strong> {{ $business->mode === 'menu' ? __('messages.menu') : __('messages.booking') }}</p>
        <p><strong>{{ __('messages.current_theme') }}:</strong> {{ ucfirst($currentTheme) }}</p>

        <form method="POST" action="{{ route('owner.public-page.update') }}" style="margin-top:18px;">
            @csrf

            <div class="form-group">
                <label class="label">{{ __('messages.select_theme') }}</label>
                <select class="select" name="public_theme" required>
                    @foreach($themes as $theme)
                        <option value="{{ $theme }}" {{ $currentTheme === $theme ? 'selected' : '' }}>
                            {{ ucfirst($theme) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;">
                <div class="form-group">
                    <label class="label">{{ __('messages.primary_color') }}</label>
                    <input type="color" name="primary_color" value="{{ $primaryColor }}">
                </div>

                <div class="form-group">
                    <label class="label">{{ __('messages.secondary_color') }}</label>
                    <input type="color" name="secondary_color" value="{{ $secondaryColor }}">
                </div>

                <div class="form-group">
                    <label class="label">{{ __('messages.background_color') }}</label>
                    <input type="color" name="background_color" value="{{ $backgroundColor }}">
                </div>

                <div class="form-group">
                    <label class="label">{{ __('messages.text_color') }}</label>
                    <input type="color" name="text_color" value="{{ $textColor }}">
                </div>

                <div class="form-group">
                    <label class="label">{{ __('messages.card_color') }}</label>
                    <input type="color" name="card_color" value="{{ $cardColor }}">
                </div>

                <div class="form-group">
                    <label class="label">{{ __('messages.button_color') }}</label>
                    <input type="color" name="button_color" value="{{ $buttonColor }}">
                </div>
            </div>

            <div class="form-group">
                <label class="label">{{ __('messages.font') }}</label>
                <select class="select" name="font_family">
                    <option value="Arial" {{ $fontFamily === 'Arial' ? 'selected' : '' }}>Arial</option>
                    <option value="Tahoma" {{ $fontFamily === 'Tahoma' ? 'selected' : '' }}>Tahoma</option>
                    <option value="Verdana" {{ $fontFamily === 'Verdana' ? 'selected' : '' }}>Verdana</option>
                    <option value="Georgia" {{ $fontFamily === 'Georgia' ? 'selected' : '' }}>Georgia</option>
                </select>
            </div>

            <div class="form-group">
                <label class="label">{{ __('messages.border_radius') }}</label>
                <select class="select" name="border_radius">
                    <option value="8px" {{ $borderRadius === '8px' ? 'selected' : '' }}>{{ __('messages.small') }}</option>
                    <option value="18px" {{ $borderRadius === '18px' ? 'selected' : '' }}>{{ __('messages.medium') }}</option>
                    <option value="28px" {{ $borderRadius === '28px' ? 'selected' : '' }}>{{ __('messages.large') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="label">{{ __('messages.hero_text') }}</label>
                <input type="text"
                    name="public_tagline"
                    class="input"
                    value="{{ $settings->public_tagline }}"
                    placeholder="Write your business message">
            </div>
            <label style="display:flex; gap:8px; align-items:center;">
                    <input type="checkbox"
                        name="group_services_on_public_page"
                        value="1"
                        {{ old('group_services_on_public_page', $settings->group_services_on_public_page ?? false) ? 'checked' : '' }}>

                    {{ __('messages.group_services_on_public_page') }}
                </label>

                <p class="text-muted">
                    {{ __('messages.group_services_on_public_page_desc') }}
                </p>

            <button class="btn" type="submit">{{ __('messages.save_design') }}</button>
        </form>
    </div>


    <div class="card">
        <h3 style="margin-bottom:18px;">{{ __('messages.live_preview') }}</h3>

        <div style="
            background:{{ $backgroundColor }};
            color:{{ $textColor }};
            border:1px solid #e5e7eb;
            border-radius:{{ $borderRadius }};
            padding:18px;
            font-family:{{ $fontFamily }}, sans-serif;
        ">
            <div style="
                background:linear-gradient(135deg,{{ $primaryColor }},{{ $secondaryColor }});
                color:#fff;
                border-radius:{{ $borderRadius }};
                padding:22px;
                margin-bottom:16px;
            ">
                <h2 style="margin:0 0 8px;">{{ $business->name }}</h2>
                <p style="margin:0;opacity:.85;">
                    {{ $business->mode === 'menu' ? 'Browse the menu and place your order easily.' : 'Book your appointment online in a few clicks.' }}
                </p>
            </div>

            <div style="display:grid;grid-template-columns:1.2fr .8fr;gap:14px;">
                <div style="background:{{ $cardColor }};border:1px solid #e5e7eb;border-radius:{{ $borderRadius }};padding:16px;">
                    <h4 style="margin:0 0 10px;">{{ __('messages.main_section') }}</h4>
                    <div style="height:12px;background:#e5e7eb;border-radius:20px;margin-bottom:8px;"></div>
                    <div style="height:12px;background:#e5e7eb;border-radius:20px;width:70%;margin-bottom:14px;"></div>
                    <div style="background:{{ $buttonColor }};color:#fff;text-align:center;border-radius:12px;padding:10px;font-weight:700;">
                        Button
                    </div>
                </div>

                <div style="background:{{ $cardColor }};border:1px solid #e5e7eb;border-radius:{{ $borderRadius }};padding:16px;">
                    <h4 style="margin:0 0 10px;">{{ __('messages.side_card') }}</h4>
                    <div style="height:12px;background:#e5e7eb;border-radius:20px;margin-bottom:8px;"></div>
                    <div style="height:12px;background:#e5e7eb;border-radius:20px;width:60%;"></div>
                </div>
            </div>
        </div>
        
    </div>

</div>
</x-layouts.app>