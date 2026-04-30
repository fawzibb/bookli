@php
    $isBooking = $user->business->mode === 'booking';
@endphp

<x-layouts.app>
    <x-slot name="title">{{ __('messages.owner_dashboard') }}</x-slot>

    <x-slot name="sidebar">
        <div class="brand">Bookli</div>
        <p>{{ $user->business->name }}</p>

        <a class="nav-link active" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>

        @if($isBooking)
            <a class="nav-link" href="{{ route('owner.bookings.index') }}">{{ __('messages.bookings') }}</a>
            <a class="nav-link" href="{{ route('owner.services.index') }}">{{ __('messages.services') }}</a>
            <a class="nav-link" href="{{ route('owner.service-groups.index') }}">{{ __('messages.service_groups') }}</a>
            <a class="nav-link" href="{{ route('owner.schedules.index') }}">{{ __('messages.schedules') }}</a>
        @endif

        @if(!$isBooking)
            <a class="nav-link" href="{{ route('owner.categories.index') }}">{{ __('messages.categories') }}</a>
            <a class="nav-link" href="{{ route('owner.menu_items.index') }}">{{ __('messages.menu_items') }}</a>
            <a class="nav-link" href="{{ route('owner.orders.index') }}">{{ __('messages.orders') }}</a>
            <a class="nav-link" href="{{ route('owner.schedules.index') }}">{{ __('messages.opening_hours') }}</a>
        @endif

        <a class="nav-link" href="{{ route('owner.qrcode') }}">{{ __('messages.qr_code') }}</a>
        <a class="nav-link" href="{{ route('owner.subscription.index') }}">{{ __('messages.subscription') }}</a>
        <a class="nav-link" target="_blank" href="{{ route('business.public', $user->business->slug) }}">{{ __('messages.public_page') }}</a>
        <a class="nav-link" href="{{ route('owner.public-page.index') }}">{{ __('messages.public_page_design') }}</a>
        <a class="nav-link" href="{{ route('owner.settings.index') }}">{{ __('messages.settings') }}</a>

        <form method="POST" action="{{ route('logout') }}" style="margin-top:18px;">
            @csrf
            <button class="btn" style="width:100%;">{{ __('messages.logout') }}</button>
        </form>
    </x-slot>

    <div class="topbar">
        <div>
            <h1 class="page-title">{{ __('messages.owner_dashboard') }}</h1>
            <p class="page-subtitle">{{ __('messages.welcome_back') }}, {{ $user->first_name }}</p>
        </div>
    </div>

    <div class="grid-3">
        <div class="stat">
            <div class="stat-label">{{ __('messages.business') }}</div>
            <div class="stat-value" style="font-size:22px;">
                {{ $user->business->name }}
            </div>
        </div>

        <div class="stat">
            <div class="stat-label">{{ __('messages.mode') }}</div>
            <div class="stat-value" style="font-size:22px;">
                {{ $user->business->mode === 'menu' ? __('messages.menu') : __('messages.booking') }}
            </div>
        </div>

        <div class="stat">
            <div class="stat-label">{{ __('messages.status_now') }}</div>

            <div class="stat-value" style="font-size:22px; color:{{ $statusNow ? '#166534' : '#991b1b' }};">
                @if($user->business->mode === 'menu')
                    {{ $statusNow ? __('messages.open_now') : __('messages.closed_now') }}
                @else
                    {{ $statusNow ? __('messages.accepting_bookings') : __('messages.closed_today') }}
                @endif
            </div>
        </div>
    </div>

    <div class="grid" style="margin-top:20px;">
        @if($isBooking)
            <div class="card dash-card">
                <h3>{{ __('messages.manage_bookings') }}</h3>
                <p class="page-subtitle">{{ __('messages.manage_bookings_desc') }}</p>
                <a class="btn" href="{{ route('owner.bookings.index') }}">{{ __('messages.open') }}</a>
            </div>

            <div class="card dash-card">
                <h3>{{ __('messages.manage_services') }}</h3>
                <p class="page-subtitle">{{ __('messages.manage_services_desc') }}</p>
                <a class="btn" href="{{ route('owner.services.index') }}">{{ __('messages.open') }}</a>
            </div>
            <div class="card dash-card">
                <h3>{{ __('messages.service_groups') }}</h3>
                <p class="page-subtitle">{{ __('messages.manage_service_groups_desc') }}</p>
                <a class="btn" href="{{ route('owner.service-groups.index') }}">{{ __('messages.open') }}</a>
            </div>

            <div class="card dash-card">
                <h3>{{ __('messages.manage_schedules') }}</h3>
                <p class="page-subtitle">{{ __('messages.manage_schedules_desc') }}</p>
                <a class="btn" href="{{ route('owner.schedules.index') }}">{{ __('messages.open') }}</a>
            </div>
        @else
            <div class="card dash-card">
                <h3>{{ __('messages.manage_categories') }}</h3>
                <p class="page-subtitle">{{ __('messages.manage_categories_desc') }}</p>
                <a class="btn" href="{{ route('owner.categories.index') }}">{{ __('messages.open') }}</a>
            </div>

            <div class="card dash-card">
                <h3>{{ __('messages.manage_menu_items') }}</h3>
                <p class="page-subtitle">{{ __('messages.manage_menu_items_desc') }}</p>
                <a class="btn" href="{{ route('owner.menu_items.index') }}">{{ __('messages.open') }}</a>
            </div>

            <div class="card dash-card">
                <h3>{{ __('messages.manage_orders') }}</h3>
                <p class="page-subtitle">{{ __('messages.manage_orders_desc') }}</p>
                <a class="btn" href="{{ route('owner.orders.index') }}">{{ __('messages.open') }}</a>
            </div>
        @endif

        <div class="card dash-card">
            <h3>{{ __('messages.public_page_design') }}</h3>
            <p class="page-subtitle">{{ __('messages.public_page_design_desc') }}</p>
            <a class="btn" href="{{ route('owner.public-page.index') }}">{{ __('messages.open') }}</a>
        </div>

        <div class="card dash-card">
            <h3>{{ __('messages.subscription') }}</h3>
            <p class="page-subtitle">{{ __('messages.subscription_desc') }}</p>
            <a class="btn" href="{{ route('owner.subscription.index') }}">{{ __('messages.open') }}</a>
        </div>

        <div class="card dash-card">
            <h3>{{ __('messages.opening_hours') }}</h3>
            <p class="page-subtitle">{{ __('messages.opening_hours_desc') }}</p>
            <a class="btn" href="{{ route('owner.schedules.index') }}">{{ __('messages.open') }}</a>
        </div>

        <div class="card dash-card">
            <h3>{{ __('messages.qr_code') }}</h3>
            <p class="page-subtitle">{{ __('messages.qr_code_desc') }}</p>
            <a class="btn" href="{{ route('owner.qrcode') }}">{{ __('messages.open') }}</a>
        </div>

        <div class="card dash-card">
            <h3>{{ __('messages.public_page') }}</h3>
            <p class="page-subtitle">{{ __('messages.public_page_desc') }}</p>
            <a class="btn" target="_blank" href="{{ route('business.public', $user->business->slug) }}">{{ __('messages.open') }}</a>
        </div>

        <div class="card dash-card">
            <h3>{{ __('messages.settings') }}</h3>
            <p class="page-subtitle">{{ __('messages.settings_desc') }}</p>
            <a class="btn" href="{{ route('owner.settings.index') }}">{{ __('messages.open') }}</a>
        </div>
    </div>

    <style>
        .dash-card{
            display:flex;
            flex-direction:column;
            min-height:175px;
        }

        .dash-card p{
            flex:1;
            margin-bottom:18px;
        }

        .dash-card .btn{
            align-self:flex-start;
            min-width:90px;
            text-align:center;
        }
    </style>
</x-layouts.app>