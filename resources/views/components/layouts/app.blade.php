<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Bookli' }}</title>

    @include('components.layouts.partials.app-style')
</head>
<body>

<div class="desktop-top">
    <div class="top-left">
        <button class="menu-btn" onclick="toggleDesktopSidebar()">☰</button>
        <div class="top-brand">Bookli</div>
    </div>

    <div style="display:flex;align-items:center;gap:12px;">

        @auth('business')
            @php
                $unreadCount = auth('business')->user()->unreadNotifications->count();
            @endphp

            <div class="notification-wrapper">
                <button id="notificationBtn" class="notification-btn">
                    🔔

                    <span
                        id="notificationCount"
                        style="{{ $unreadCount == 0 ? 'display:none;' : '' }}"
                    >
                        {{ $unreadCount }}
                    </span>
                </button>

                <div id="notificationsDropdown" class="notifications-dropdown">
                    @forelse(auth('business')->user()->unreadNotifications->take(10) as $notification)
                        <a href="{{ $notification->data['url'] ?? '#' }}" class="notification-item">
                            <strong>{{ $notification->data['title'] ?? 'Notification' }}</strong>
                            <p>{{ $notification->data['message'] ?? '' }}</p>
                        </a>
                    @empty
                        <div class="notification-empty">
                            No new notifications
                        </div>
                    @endforelse
                </div>
            </div>
        @endauth

        <div class="lang-switch">
            <a href="{{ route('language.switch', 'en') }}">English</a>
            <a href="{{ route('language.switch', 'ar') }}">العربية</a>
        </div>
    </div>
</div>

<div class="mobile-top">
    <strong>Bookli</strong>

    <div style="display:flex;align-items:center;gap:8px;">

        @auth('business')
            @php
                $mobileUnreadCount = auth('business')->user()->unreadNotifications->count();
            @endphp

            <div class="notification-wrapper">
                <button id="mobileNotificationBtn" class="notification-btn">
                    🔔

                    <span
                        id="mobileNotificationCount"
                        style="{{ $mobileUnreadCount == 0 ? 'display:none;' : '' }}"
                    >
                        {{ $mobileUnreadCount }}
                    </span>
                </button>

                <div id="mobileNotificationsDropdown" class="notifications-dropdown mobile-notifications-dropdown">
                    @forelse(auth('business')->user()->unreadNotifications->take(10) as $notification)
                        <a href="{{ $notification->data['url'] ?? '#' }}" class="notification-item">
                            <strong>{{ $notification->data['title'] ?? 'Notification' }}</strong>
                            <p>{{ $notification->data['message'] ?? '' }}</p>
                        </a>
                    @empty
                        <div class="notification-empty">
                            No new notifications
                        </div>
                    @endforelse
                </div>
            </div>
        @endauth

        <div class="lang-switch">
            <a href="{{ route('language.switch', 'en') }}">EN</a>
            <a href="{{ route('language.switch', 'ar') }}">AR</a>
        </div>

        <button class="menu-btn" onclick="openMenu()">☰</button>
    </div>
</div>

<div id="mobileOverlay" class="mobile-overlay" onclick="closeMenu()"></div>

<div id="mobileDrawer" class="mobile-drawer">
    <button class="close-btn" onclick="closeMenu()">✕</button>

    <div style="clear:both;margin-top:14px;">
        {!! $sidebar ?? '' !!}
    </div>
</div>

<div class="layout">
    <aside id="desktopSidebar" class="sidebar">
        {!! $sidebar ?? '' !!}
    </aside>

    <main class="content">
        {{ $slot }}
    </main>
</div>

@include('components.layouts.partials.app-scripts')

</body>
</html>