<div class="booking-section card">
    <div class="section-header">
        <h2>{{ $title }}</h2>
        <span class="section-count">
            {{ $bookings->count() }} {{ __('messages.bookings') }}
        </span>
    </div>

    @if($bookings->count())
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('messages.customer') }}</th>
                        <th>{{ __('messages.service') }}</th>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.time') }}</th>
                        <th>{{ __('messages.phone') }}</th>
                        <th style="width:180px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td>{{ $booking->customer_name ?? $booking->customer->name ?? '-' }}</td>
                            <td>{{ $booking->service->name ?? '-' }}</td>
                            <td>{{ $booking->booking_date }}</td>
                            <td>{{ $booking->start_time }}</td>
                            <td>{{ $booking->customer_phone ?? $booking->customer->phone ?? '-' }}</td>

                            <td>
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">

                                    <form method="POST" action="{{ route('owner.bookings.updateStatus', $booking) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="completed">

                                        <button type="submit" class="btn btn-success">
                                            {{ __('messages.complete') }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('owner.bookings.updateStatus', $booking) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">

                                        <button type="submit" class="btn btn-danger">
                                        {{ __('messages.delete') }}
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            {{ __('messages.no_bookings_section') }}
        </div>
    @endif
</div>