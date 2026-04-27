<x-layouts.app>
<x-slot name="title">Bookings</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Owner Panel</p>

<a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
<a class="nav-link active" href="{{ route('owner.bookings.index') }}">{{ __('messages.bookings') }}</a>
<a class="nav-link" href="{{ route('owner.services.index') }}">{{ __('messages.services') }}</a>
<a class="nav-link" href="{{ route('owner.schedules.index') }}">{{ __('messages.schedules') }}</a>
<a class="nav-link" href="{{ route('owner.qrcode') }}">{{ __('messages.qr_code') }}</a>
<a class="nav-link" href="{{ route('owner.subscription.index') }}">{{ __('messages.subscription') }}</a>
<a class="nav-link" href="{{ route('owner.public-page.index') }}">{{ __('messages.public_page_design') }}</a>
<a class="nav-link" href="{{ route('owner.settings.index') }}">{{ __('messages.settings') }}</a>
<form method="POST" action="{{ route('logout') }}" style="margin-top:18px;">
    @csrf
    <button class="btn" style="width:100%;">{{ __('messages.logout') }}</button>
</form>
</x-slot>

<style>

.completed-btn{
    background:#111827;
    color:#fff;
    border:none;
    padding:10px 16px;
    border-radius:999px;
    font-weight:700;
    text-decoration:none;
    transition:all .2s ease;
}

.completed-btn:hover{
    background:#000;
    color:#fff;
}
.booking-sections{
    display:flex;
    flex-direction:column;
    gap:18px;
}

.booking-section{
    overflow:hidden;
}

.section-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:14px;
}

.section-header h2{
    margin:0;
    font-size:20px;
}

.section-count{
    background:#f3f4f6;
    color:#374151;
    padding:6px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:600;
}

.empty-state{
    padding:18px;
    text-align:center;
    color:#6b7280;
    background:#f9fafb;
    border-radius:12px;
}

.booking-tabs{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:18px;
}

.booking-tab{
    border:none;
    background:#f3f4f6;
    color:#374151;
    padding:10px 14px;
    border-radius:999px;
    font-size:14px;
    font-weight:600;
    cursor:pointer;
    transition:all .2s ease;
}

.booking-tab:hover{
    background:#e5e7eb;
}

.booking-tab.active{
    background:#111827;
    color:#fff;
}

.booking-panel{
    display:none;
}

.booking-panel.active{
    display:block;
}

@media (max-width: 768px){
    .section-header{
        flex-direction:column;
        align-items:flex-start;
    }

    .booking-tabs{
        gap:8px;
    }

    .booking-tab{
        font-size:13px;
        padding:9px 12px;
    }
}
</style>

<div class="topbar">
    <div>
        <h1 class="page-title">{{ __('messages.bookings') }}</h1>
        <p class="page-subtitle">{{ __('messages.manage_bookings_desc') }}</p>
    </div>

    <a class="btn completed-btn" href="{{ route('owner.bookings.completed') }}">{{ __('messages.completed_bookings') }}</a>
</div>

<div id="bookings-list">
    <div class="booking-tabs">
        <button type="button" class="booking-tab active" data-tab="today">{{ __('messages.today') }}</button>
        <button type="button" class="booking-tab" data-tab="tomorrow">{{ __('messages.tomorrow') }}</button>
        <button type="button" class="booking-tab" data-tab="week">{{ __('messages.this_week') }}</button>
        <button type="button" class="booking-tab" data-tab="month">{{ __('messages.this_month') }}</button>
        <button type="button" class="booking-tab" data-tab="year">{{ __('messages.this_year') }}</button>
    </div>

    <div class="booking-panel active" data-panel="today">
        @include('owner.bookings.partials.section', [
            'title' => __('messages.today'),
            'bookings' => $todayBookings,
        ])
    </div>

    <div class="booking-panel" data-panel="tomorrow">
        @include('owner.bookings.partials.section', [
            'title' => __('messages.tomorrow'),
            'bookings' => $tomorrowBookings,
        ])
    </div>

    <div class="booking-panel" data-panel="week">
        @include('owner.bookings.partials.section', [
            'title' => __('messages.this_week'),
            'bookings' => $thisWeekBookings,
        ])
    </div>

    <div class="booking-panel" data-panel="month">
        @include('owner.bookings.partials.section', [
            'title' => __('messages.this_month'),
            'bookings' => $thisMonthBookings,
        ])
    </div>

    <div class="booking-panel" data-panel="year">
        @include('owner.bookings.partials.section', [
            'title' => __('messages.this_year'),
            'bookings' => $thisYearBookings,
        ])
    </div>
</div>

<audio id="new-booking-sound" preload="auto">
    <source src="{{ asset('sounds/new-order.mp3') }}" type="audio/mp3">
</audio>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const businessId = @json(auth()->guard('business')->user()->business_id);
    const bookingsList = document.getElementById('bookings-list');
    const sound = document.getElementById('new-booking-sound');

    function initBookingTabs() {
        const tabs = document.querySelectorAll('.booking-tab');
        const panels = document.querySelectorAll('.booking-panel');

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                const target = this.dataset.tab;

                tabs.forEach(item => item.classList.remove('active'));
                panels.forEach(item => item.classList.remove('active'));

                this.classList.add('active');

                const targetPanel = document.querySelector(`.booking-panel[data-panel="${target}"]`);
                if (targetPanel) {
                    targetPanel.classList.add('active');
                }
            });
        });
    }

    async function refreshBookings() {
        try {
            const activeTab = document.querySelector('.booking-tab.active')?.dataset.tab || 'today';

            const response = await fetch("{{ route('owner.bookings.partial') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                console.error('Failed to refresh bookings. Status:', response.status);
                return;
            }

            const html = await response.text();
            bookingsList.innerHTML = html;

            initBookingTabs();

            const tabs = document.querySelectorAll('.booking-tab');
            const panels = document.querySelectorAll('.booking-panel');

            tabs.forEach(item => item.classList.remove('active'));
            panels.forEach(item => item.classList.remove('active'));

            const selectedTab = document.querySelector(`.booking-tab[data-tab="${activeTab}"]`);
            const selectedPanel = document.querySelector(`.booking-panel[data-panel="${activeTab}"]`);

            if (selectedTab && selectedPanel) {
                selectedTab.classList.add('active');
                selectedPanel.classList.add('active');
            } else {
                document.querySelector('.booking-tab[data-tab="today"]')?.classList.add('active');
                document.querySelector('.booking-panel[data-panel="today"]')?.classList.add('active');
            }
        } catch (error) {
            console.error('Refresh error:', error);
        }
    }

    initBookingTabs();

    window.Echo.private(`bookings.${businessId}`)
        .subscribed(() => {
            console.log(`Subscribed successfully to bookings.${businessId}`);
        })
        .error((error) => {
            console.error('Channel subscription error:', error);
        })
        .listen('.new-booking', async (e) => {
            console.log('New booking event received:', e);

            await refreshBookings();

            if (sound) {
                sound.currentTime = 0;
                sound.play().catch(() => {});
            }
        });
});
</script>

</x-layouts.app>