<x-layouts.app>
    <x-slot name="title">Subscription</x-slot>

    <x-slot name="sidebar">
        <div class="brand">Bookli</div>
        <p>Owner Panel</p>

        <a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>

        @if(auth()->guard('business')->user()->business->mode === 'booking')
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
        <a class="nav-link active" href="{{ route('owner.subscription.index') }}">{{ __('messages.subscription') }}</a>
        <a class="nav-link" href="{{ route('owner.public-page.index') }}">{{ __('messages.public_page') }}</a>
        <a class="nav-link" href="{{ route('owner.settings.index') }}">{{ __('messages.settings') }}</a>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:18px;">
            @csrf
            <button class="btn" style="width:100%;">{{ __('messages.logout') }}</button>
        </form>
    </x-slot>

    @php
        $endDate = $subscription?->subscription_ends_at ?? $subscription?->trial_ends_at;
        $isExpired = !$endDate || now()->gt($endDate);
        $statusLabel = $isExpired ? 'Expired' : 'Active';
        $badgeClass = $isExpired ? 'badge-cancelled' : 'badge-completed';

        $remainingText = 'Expired';

        if ($endDate && now()->lt($endDate)) {
            $end = \Carbon\Carbon::parse($endDate);

            if (now()->diffInHours($end) < 24) {
                $remainingText = now()->diffInHours($end) . ' hours';
            } else {
                $remainingText = now()->diffInDays($end) . ' days';
            }
        }
    @endphp

    <div class="topbar">
    <div>
        <h1 class="page-title">{{ __('messages.subscription') }}</h1>
        <p class="page-subtitle">{{ __('messages.subscription_page_desc') }}</p>
    </div>

    <div>
        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
    </div>
</div>

<div class="grid">

    <div class="card">
        <h3 style="margin-top:0;">{{ __('messages.current_subscription') }}</h3>

        <div class="grid" style="grid-template-columns:1fr 1fr;">

            <div class="stat">
                <div class="stat-label">{{ __('messages.status') }}</div>
                <div class="stat-value" style="font-size:22px;">{{ $statusLabel }}</div>
            </div>

            <div class="stat">
                <div class="stat-label">{{ __('messages.remaining_time') }}</div>
                <div class="stat-value" style="font-size:22px;">{{ $remainingText }}</div>
            </div>

        </div>

        <div style="margin-top:20px;">
            <p>
                <strong>{{ __('messages.trial_ends') }}:</strong>
                {{ $subscription?->trial_ends_at ? \Carbon\Carbon::parse($subscription->trial_ends_at)->format('d-m-Y H:i') : '-' }}
            </p>

            <p>
                <strong>{{ __('messages.subscription_ends') }}:</strong>
                {{ $subscription?->subscription_ends_at ? \Carbon\Carbon::parse($subscription->subscription_ends_at)->format('d-m-Y H:i') : '-' }}
            </p>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">{{ __('messages.activate_code') }}</h3>

        <p class="page-subtitle" style="margin-bottom:16px;">
            {{ __('messages.activate_code_desc') }}
        </p>

        @if($errors->any())
            <div class="alert" style="background:#fee2e2;color:#991b1b;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('owner.subscription.redeem') }}">
            @csrf

            <div class="form-group">
                <label class="label">{{ __('messages.activation_code') }}</label>
                <input class="input" type="text" name="code" placeholder="{{ __('messages.enter_your_code') }}" required>
            </div>

            <button class="btn" type="submit">{{ __('messages.activate_code') }}</button>
        </form>
    </div>

</div>

<div class="card" style="margin-top:22px;">
    <h3 style="margin-top:0;">{{ __('messages.pricing_plans') }}</h3>

    <p class="page-subtitle" style="margin-bottom:18px;">
        {{ __('messages.pricing_plans_desc') }}
    </p>

    <div class="pricing-grid">

        <div class="stat pricing-card">
            <div class="stat-label">{{ __('messages.monthly') }}</div>
            <div class="stat-value pricing-price">
                {{ isset($prices['monthly']) ? '$' . $prices['monthly'] : __('messages.coming_soon') }}
            </div>
            <p class="page-subtitle pricing-desc">{{ __('messages.one_month_access') }}</p>
        </div>

        <div class="stat pricing-card">
            <div class="stat-label">{{ __('messages.six_months') }}</div>
            <div class="stat-value pricing-price">
                {{ isset($prices['six_months']) ? '$' . $prices['six_months'] : __('messages.coming_soon') }}
            </div>
            <p class="page-subtitle pricing-desc">{{ __('messages.six_months_access') }}</p>
        </div>

        <div class="stat pricing-card">
            <div class="stat-label">{{ __('messages.yearly') }}</div>
            <div class="stat-value pricing-price">
                {{ isset($prices['yearly']) ? '$' . $prices['yearly'] : __('messages.coming_soon') }}
            </div>
            <p class="page-subtitle pricing-desc">{{ __('messages.twelve_months_access') }}</p>
        </div>

    </div>
</div>

<div class="card" style="margin-top:22px;">
    <h3 style="margin-top:0;">{{ __('messages.contact_us') }}</h3>

    <p class="page-subtitle" style="margin-bottom:18px;">
        {{ __('messages.contact_us_desc') }}
    </p>

    <div class="contact-grid">

        <div class="stat contact-card">
            <div class="stat-label">{{ __('messages.phone_whatsapp') }}</div>
            <div class="stat-value contact-value">
                {{ $contact['phone'] ?? __('messages.add_phone_later') }}
            </div>
        </div>

        <div class="stat contact-card">
            <div class="stat-label">{{ __('messages.email') }}</div>
            <div class="stat-value contact-value">
                {{ $contact['email'] ?? __('messages.add_email_later') }}
            </div>
        </div>

    </div>
</div>
<style>
    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .pricing-card {
        padding: 22px;
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .pricing-price {
        font-size: 26px;
        margin-top: 8px;
    }

    .pricing-desc {
        margin-top: 10px;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .contact-card {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .contact-value {
        font-size: 20px;
        word-break: break-word;
    }

    @media (max-width: 768px) {
        .pricing-grid {
            grid-template-columns: 1fr;
        }

        .contact-grid {
            grid-template-columns: 1fr;
        }

        .pricing-card,
        .contact-card {
            padding: 18px;
        }

        .pricing-price {
            font-size: 30px;
        }
    }
</style>
</x-layouts.app>