@php
    $firstWorkingSchedule = $weeklySchedules->first(function ($item) {
        return !$item->is_off;
    });

    $sameIs24Hours = $firstWorkingSchedule
        && $firstWorkingSchedule->open_time === '00:00:00'
        && $firstWorkingSchedule->close_time === '23:59:00';
@endphp

<x-layouts.app>
<x-slot name="title">Schedules</x-slot>

<x-slot name="sidebar">
<div class="brand">Bookli</div>
<p>Owner Panel</p>
<a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>

        @if(auth()->guard('business')->user()->business->mode === 'booking')
            <a class="nav-link" href="{{ route('owner.bookings.index') }}">{{ __('messages.bookings') }}</a>
            <a class="nav-link" href="{{ route('owner.services.index') }}">{{ __('messages.services') }}</a>
            <a class="nav-link" href="{{ route('owner.service-groups.index') }}">{{ __('messages.service_groups') }}</a>
            <a class="nav-link active" href="{{ route('owner.schedules.index') }}">{{ __('messages.schedules') }}</a>
        @else
            <a class="nav-link" href="{{ route('owner.categories.index') }}">{{ __('messages.categories') }}</a>
            <a class="nav-link" href="{{ route('owner.menu_items.index') }}">{{ __('messages.menu_items') }}</a>
            <a class="nav-link" href="{{ route('owner.orders.index') }}">{{ __('messages.orders') }}</a>
            <a class="nav-link active" href="{{ route('owner.schedules.index') }}">{{ __('messages.opening_hours') }}</a>
        @endif
        <a class="nav-link" href="{{ route('owner.qrcode') }}">{{ __('messages.qr_code') }}</a>
        <a class="nav-link" href="{{ route('owner.subscription.index') }}">{{ __('messages.subscription') }}</a>
        <a class="nav-link" href="{{ route('owner.public-page.index') }}">{{ __('messages.public_page_design') }}</a>
        <a class="nav-link" href="{{ route('owner.settings.index') }}">{{ __('messages.settings') }}</a>

<form method="POST" action="{{ route('logout') }}" style="margin-top:18px;">
@csrf
<button class="btn" style="width:100%;">{{ __('messages.logout') }}</button>
</form>
</x-slot>

@php
$mode = auth()->guard('business')->user()->business->mode;

$isBooking = $mode === 'booking';

$pageTitle = $isBooking
    ? __('messages.appointment_hours')
    : __('messages.opening_hours');

$pageSub = $isBooking
    ? __('messages.appointment_hours_desc')
    : __('messages.opening_hours_page_desc');
@endphp

<div class="topbar">
    <div>
        <h1 class="page-title">{{ $pageTitle }}</h1>
        <p class="page-subtitle">{{ $pageSub }}</p>
    </div>
</div>

<div class="card">
    <h3>{{ $isBooking ? __('messages.weekly_appointment_schedule') : __('messages.weekly_opening_schedule') }}</h3>

    <form method="POST" action="{{ route('owner.schedules.updateWeekly') }}">
    @csrf

    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px;">
        <label>
            <input type="radio" name="schedule_type" value="same_all_days" checked>
            {{ __('messages.same_time_all_days') }}
        </label>

        <label>
            <input type="radio" name="schedule_type" value="custom_days">
            {{ __('messages.custom_days') }}
        </label>
    </div>

    <div id="same-days-box">
        <div class="grid" style="grid-template-columns:1fr 1fr;">
            <input class="input" type="time" name="same_open_time"
    value="{{ $firstWorkingSchedule?->open_time ? \Carbon\Carbon::parse($firstWorkingSchedule->open_time)->format('H:i') : '' }}">

<input class="input" type="time" name="same_close_time"
    value="{{ $firstWorkingSchedule?->close_time ? \Carbon\Carbon::parse($firstWorkingSchedule->close_time)->format('H:i') : '' }}">
        </div>

        <label style="display:block; margin-top:12px;">
            <input type="checkbox" name="same_is_24_hours" style="width:auto;" {{ $sameIs24Hours ? 'checked' : '' }}>
            {{ __('messages.open-24') }}
        </label>

        <div style="margin-top:14px;">
            <strong>{{ __('messages.off_days') }}</strong>

            <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:10px;">
                @foreach($days as $dayNumber => $dayName)
                    <label>
                       <input type="checkbox" name="off_days[]" value="{{ $dayNumber }}" style="width:auto;"
                        {{ isset($weeklySchedules[$dayNumber]) && $weeklySchedules[$dayNumber]->is_off ? 'checked' : '' }}>
                        {{ __('messages.day_' . $dayNumber) }}
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <div id="custom-days-box" style="display:none;">
        <div class="table-wrap">
            <table>
                <tr>
                    <th>{{ __('messages.day') }}</th>
                    <th>24/24</th>
                    <th>{{ $isBooking ? __('messages.start') : __('messages.open') }}</th>
                    <th>{{ $isBooking ? __('messages.end') : __('messages.close') }}</th>
                    <th>{{ __('messages.off') }}</th>
                </tr>

                @foreach($days as $dayNumber => $dayName)
                    @php $item = $weeklySchedules[$dayNumber] ?? null; @endphp

                    <tr>
                        <td>{{ __('messages.day_' . $dayNumber) }}</td>

                        <td>
                            <input type="checkbox"
                                name="days[{{ $dayNumber }}][is_24_hours]"
                                style="width:auto;"
                                {{ $item && !$item->is_off && $item->open_time === '00:00:00' && $item->close_time === '23:59:00' ? 'checked' : '' }}>
                        </td>

                        <td>
                            <input class="input" type="time"
                                name="days[{{ $dayNumber }}][open_time]"
                                value="{{ $item?->open_time ? \Carbon\Carbon::parse($item->open_time)->format('H:i') : '' }}">
                        </td>

                        <td>
                            <input class="input" type="time"
                                name="days[{{ $dayNumber }}][close_time]"
                                value="{{ $item?->close_time ? \Carbon\Carbon::parse($item->close_time)->format('H:i') : '' }}">
                        </td>

                        <td>
                            <input type="checkbox"
                                name="days[{{ $dayNumber }}][is_off]"
                                style="width:auto;"
                                {{ $item && $item->is_off ? 'checked' : '' }}>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <button class="btn" style="margin-top:15px;">
        {{ $isBooking ? __('messages.save_schedule') : __('messages.save_opening_hours') }}
    </button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sameBox = document.getElementById('same-days-box');
    const customBox = document.getElementById('custom-days-box');
    const radios = document.querySelectorAll('input[name="schedule_type"]');

    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'same_all_days') {
                sameBox.style.display = 'block';
                customBox.style.display = 'none';
            } else {
                sameBox.style.display = 'none';
                customBox.style.display = 'block';
            }
        });
    });
});
</script>
</div>

<div class="grid">

    <div class="card">
        <h3>{{ $isBooking ? __('messages.recurring_booking_block') : __('messages.recurring_pause_time') }}</h3>

        <form method="POST" action="{{ route('owner.schedules.storeRecurring') }}">
            @csrf

            <select class="select" name="apply_to">
                <option value="one_day">{{ __('messages.one_day') }}</option>
                <option value="all_working_days">{{ __('messages.all_working_days') }}</option>
            </select>

            <select class="select" name="day_of_week" style="margin-top:10px;">
                @foreach($days as $dayNumber => $dayName)
                    <option value="{{ $dayNumber }}">{{ __('messages.day_' . $dayNumber) }}</option>
                @endforeach
            </select>

            <input class="input" type="time" name="start_time" style="margin-top:10px;">
            <input class="input" type="time" name="end_time" style="margin-top:10px;">

            <input class="input"
                type="text"
                name="reason"
                placeholder="{{ $isBooking ? __('messages.reason_booking_placeholder') : __('messages.reason_pause_placeholder') }}"
                style="margin-top:10px;">

            <button class="btn" style="margin-top:10px;">
                {{ $isBooking ? __('messages.add_booking_block') : __('messages.add_pause_time') }}
            </button>
        </form>
    </div>

    <div class="card">
        <h3>{{ $isBooking ? __('messages.specific_date_block') : __('messages.specific_closed_time') }}</h3>

        <form method="POST" action="{{ route('owner.schedules.storeDate') }}">
            @csrf

            <input class="input" type="date" name="blocked_date">
            <input class="input" type="time" name="start_time" style="margin-top:10px;">
            <input class="input" type="time" name="end_time" style="margin-top:10px;">

            <input class="input"
                type="text"
                name="reason"
                placeholder="{{ __('messages.reason') }}"
                style="margin-top:10px;">
            <label style="display:flex; align-items:center; gap:8px; margin:10px 0 14px;">
                <input type="checkbox" name="full_day" value="1">
                {{ __('messages.full_day') }}
            </label>

            <button class="btn" style="margin-top:10px;">
                {{ $isBooking ? __('messages.add_date_block') : __('messages.add_closed_time') }}
            </button>
        </form>
    </div>
    

</div>
<div class="grid" style="margin-top:24px;">

    <div class="card">
        <h3>{{ __('messages.recurring_pause_time') }}</h3>

        @forelse($groupedRecurringBlockedTimes as $group)
    @php
        $first = $group->first();
        $label = $group->count() > 1
            ? 'All days'
            : __('messages.day_' . $first->day_of_week);
    @endphp

    <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; padding:12px 0; border-bottom:1px solid #e5e7eb;">
        <div>
            <strong>{{ $label }}</strong>

            <div class="page-subtitle" style="margin:4px 0 0;">
                {{ \Carbon\Carbon::parse($first->start_time)->format('H:i') }}
                -
                {{ \Carbon\Carbon::parse($first->end_time)->format('H:i') }}

                @if($first->reason)
                    | {{ $first->reason }}
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('owner.schedules.destroyGroup') }}"
              onsubmit="return confirm('Delete this blocked time?')">
            @csrf
            @method('DELETE')

            <input type="hidden" name="start_time" value="{{ $first->start_time }}">
            <input type="hidden" name="end_time" value="{{ $first->end_time }}">
            <input type="hidden" name="reason" value="{{ $first->reason }}">

            <button class="btn btn-danger" type="submit">
                {{ __('messages.delete') }}
            </button>
        </form>
    </div>
@empty
    <p class="page-subtitle">{{ __('messages.no_items_found') }}</p>
@endforelse
    </div>

    <div class="card">
        <h3>{{ __('messages.specific_closed_time') }}</h3>

        @forelse($datedBlockedTimes as $blocked)
            <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; padding:12px 0; border-bottom:1px solid #e5e7eb;">
                <div>
                    <strong>{{ $blocked->blocked_date }}</strong>
                    <div class="page-subtitle" style="margin:4px 0 0;">
                        {{ \Carbon\Carbon::parse($blocked->start_time)->format('H:i') }}
                        -
                        {{ \Carbon\Carbon::parse($blocked->end_time)->format('H:i') }}
                        @if($blocked->reason)
                            | {{ $blocked->reason }}
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('owner.schedules.blocked-times.destroy', $blocked) }}"
                      onsubmit="return confirm('Delete this blocked time?')">
                    @csrf
                    @method('DELETE')

                    <button class="btn btn-danger" type="submit">
                        {{ __('messages.delete') }}
                    </button>
                </form>
            </div>
        @empty
            <p class="page-subtitle">{{ __('messages.no_items_found') }}</p>
        @endforelse
    </div>

</div>

</x-layouts.app>