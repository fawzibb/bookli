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