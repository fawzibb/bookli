<x-layouts.app>
<x-slot name="title">Settings</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Owner Panel</p>

<a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>

@if($business->mode === 'booking')
    <a class="nav-link" href="{{ route('owner.bookings.index') }}">{{ __('messages.bookings') }}</a>
    <a class="nav-link" href="{{ route('owner.services.index') }}">{{ __('messages.services') }}</a>
    <a class="nav-link" href="{{ route('owner.schedules.index') }}">{{ __('messages.schedules') }}</a>
@else
    <a class="nav-link" href="{{ route('owner.categories.index') }}">{{ __('messages.categories') }}</a>
    <a class="nav-link" href="{{ route('owner.menu_items.index') }}">{{ __('messages.menu_items') }}</a>
    <a class="nav-link" href="{{ route('owner.orders.index') }}">{{ __('messages.orders') }}</a>
    <a class="nav-link" href="{{ route('owner.schedules.index') }}">{{ __('messages.opening_hours') }}</a>
@endif

<a class="nav-link" href="{{ route('owner.qrcode') }}">{{ __('messages.qr_code') }}</a>
<a class="nav-link" href="{{ route('owner.subscription.index') }}">{{ __('messages.subscription') }}</a>
<a class="nav-link" href="{{ route('owner.public-page.index') }}">{{ __('messages.public_page_design') }}</a>
<a class="nav-link active" href="{{ route('owner.settings.index') }}">{{ __('messages.settings') }}</a>



<form method="POST" action="{{ route('logout') }}" style="margin-top:18px;">
    @csrf
    <button class="btn" style="width:100%;">{{ __('messages.logout') }}</button>
</form>
</x-slot>

<div class="topbar">
    <div>
        <h1 class="page-title">{{ __('messages.business_settings') }}</h1>
        <p class="page-subtitle">{{ __('messages.business_settings_desc') }}</p>
    </div>
</div>

<div class="card">

    @if(session('success'))
        <div class="alert" style="background:#dcfce7;color:#166534;margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert" style="background:#fee2e2;color:#991b1b;margin-bottom:16px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('owner.settings.update') }}" enctype="multipart/form-data">
    @csrf

    <div class="grid" style="grid-template-columns:1fr 1fr;">

        <div class="form-group">
            <label class="label">{{ __('messages.business_name') }}</label>
            <input class="input" type="text" name="name"
                   value="{{ old('name', $business->name) }}" required>
        </div>

        <div class="form-group">
            <label class="label">{{ __('messages.business_logo') }}</label>

            <input class="input" type="file" name="logo" accept="image/*">

            @if(!empty($business->logo))
                <div style="margin-top:12px;">
                    <img src="{{ asset('storage/' . $business->logo) }}"
                        alt="{{ $business->name }}"
                        style="width:90px;height:90px;object-fit:cover;border-radius:16px;border:1px solid #e5e7eb;">
                </div>

                <label style="display:flex;align-items:center;gap:8px;margin-top:10px;font-size:14px;">
                    <input type="checkbox" name="remove_logo" value="1">
                    Remove current logo
                </label>
            @endif
        </div>
            <div class="form-group">
                <label class="label">{{ __('messages.phone') }}</label>
                <input class="input" type="text" name="phone"
                       value="{{ old('phone', $business->phone) }}">
            </div>

            <div class="form-group">
                <label class="label">{{ __('messages.country') }}</label>
                <input class="input" type="text" name="country"
                       value="{{ old('country', $business->country) }}">
            </div>

            <div class="form-group">
                <label class="label">{{ __('messages.city') }}</label>
                <input class="input" type="text" name="city"
                       value="{{ old('city', $business->city) }}">
            </div>

            <div class="form-group" style="grid-column:1 / -1;">
                <label class="label">{{ __('messages.address') }}</label>
                <input class="input" type="text" name="address"
                       value="{{ old('address', $business->address) }}">
            </div>

            <div class="form-group">
                <label class="label">{{ __('messages.capacity_per_slot') }}</label>
                <input class="input" type="number" name="capacity_per_slot"
                       value="{{ old('capacity_per_slot', $business->capacity_per_slot ?? 1) }}"
                       min="1">
            </div>

            <p class="page-subtitle" style="margin-top:6px;">
                {{ __('messages.capacity_per_slot_desc') }}
            </p>

        </div>

        <hr style="border:none;border-top:1px solid #e5e7eb;margin:24px 0;">

        <h3>{{ __('messages.change_password') }}</h3>

        <div class="grid" style="grid-template-columns:1fr 1fr;">

            <div class="form-group">
                <label class="label">{{ __('messages.new_password') }}</label>
                <input class="input" type="password" name="password"
                       placeholder="{{ __('messages.leave_empty_password') }}">
            </div>

            <div class="form-group">
                <label class="label">{{ __('messages.confirm_password') }}</label>
                <input class="input" type="password" name="password_confirmation"
                       placeholder="{{ __('messages.confirm_new_password') }}">
            </div>

        </div>

        <button class="btn" type="submit" style="margin-top:14px;">
            {{ __('messages.save_changes') }}
        </button>
    </form>
</div>

</x-layouts.app>