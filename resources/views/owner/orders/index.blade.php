<x-layouts.app>
<x-slot name="title">Orders</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Owner Panel</p>

<a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
<a class="nav-link" href="{{ route('owner.categories.index') }}">{{ __('messages.categories') }}</a>
<a class="nav-link" href="{{ route('owner.menu_items.index') }}">{{ __('messages.menu_items') }}</a>
<a class="nav-link active" href="{{ route('owner.orders.index') }}">{{ __('messages.orders') }}</a>
<a class="nav-link" href="{{ route('owner.schedules.index') }}">{{ __('messages.opening_hours') }}</a>
<a class="nav-link" href="{{ route('owner.qrcode') }}">{{ __('messages.qr_code') }}</a>
<a class="nav-link" href="{{ route('owner.subscription.index') }}">{{ __('messages.subscription') }}</a>
<a class="nav-link" href="{{ route('owner.public-page.index') }}">{{ __('messages.public_page') }}</a>
<a class="nav-link" href="{{ route('owner.settings.index') }}">{{ __('messages.settings') }}</a>
<form method="POST" action="{{ route('logout') }}" style="margin-top:18px;">
    @csrf
    <button class="btn" style="width:100%;">{{ __('messages.logout') }}</button>
</form>
</x-slot>

<div class="topbar">
    <div>
        <h1 class="page-title">{{ __('messages.orders') }}</h1>
        <p class="page-subtitle">{{ __('messages.orders_page_desc') }}</p>
    </div>

    <a class="btn-light btn" href="{{ route('owner.orders.completed') }}">
        {{ __('messages.completed_orders') }}
    </a>
</div>

<div id="orders-list">
    @include('owner.orders.partials.list', ['orders' => $orders])
</div>

<audio id="new-order-sound" preload="auto">
    <source src="{{ asset('sounds/new-order.mp3') }}" type="audio/mp3">
</audio>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const businessId = @json(auth()->guard('business')->user()->business_id);
    window.businessId = businessId;

    const ordersList = document.getElementById('orders-list');
    const sound = document.getElementById('new-order-sound');

    async function refreshOrders() {
        try {
            const response = await fetch("{{ route('owner.orders.partial') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                console.error('Failed to refresh orders. Status:', response.status);
                return;
            }

            const html = await response.text();
            ordersList.innerHTML = html;
        } catch (error) {
            console.error('Refresh error:', error);
        }
    }

    console.log(`Connecting to private channel: orders.${businessId}`);

    window.Echo.private(`orders.${businessId}`)
        .subscribed(() => {
            console.log(`Subscribed successfully to orders.${businessId}`);
        })
        .error((error) => {
            console.error('Channel subscription error:', error);
        })
        .listen('.new-order', async (e) => {
            console.log('New order event received:', e);

            await refreshOrders();

            if (sound) {
                sound.currentTime = 0;
                sound.play().catch(() => {});
            }
        });
});
</script>
<script id="bgn6fa">
document.addEventListener('click', function () {
    const audio = document.getElementById('new-order-sound');

    if (audio) {
        audio.play().then(() => {
            audio.pause();
            audio.currentTime = 0;
            console.log('Audio unlocked');
        }).catch(() => {});
    }
}, { once: true });
</script>
</x-layouts.app>