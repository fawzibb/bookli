<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\NewBookingCreated;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('business')->user();
        $businessId = $user->business_id;

        $today = Carbon::today('Asia/Beirut');
        $tomorrow = Carbon::tomorrow('Asia/Beirut');

        $baseQuery = Booking::with(['customer', 'service'])
            ->where('business_id', $businessId)
            ->whereIn('status', ['pending', 'confirmed']);

        $todayBookings = (clone $baseQuery)
            ->whereDate('booking_date', $today->toDateString())
            ->orderBy('start_time')
            ->get();

        $tomorrowBookings = (clone $baseQuery)
            ->whereDate('booking_date', $tomorrow->toDateString())
            ->orderBy('start_time')
            ->get();

        $thisWeekBookings = (clone $baseQuery)
            ->whereBetween('booking_date', [
                $today->copy()->startOfWeek()->toDateString(),
                $today->copy()->endOfWeek()->toDateString(),
            ])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        $thisMonthBookings = (clone $baseQuery)
            ->whereYear('booking_date', $today->year)
            ->whereMonth('booking_date', $today->month)
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        $thisYearBookings = (clone $baseQuery)
            ->whereYear('booking_date', $today->year)
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        return view('owner.bookings.index', compact(
            'todayBookings',
            'tomorrowBookings',
            'thisWeekBookings',
            'thisMonthBookings',
            'thisYearBookings'
        ));
    }

    public function completed(Request $request)
{
    $user = Auth::guard('business')->user();
    $businessId = $user->business_id;

    $from = $request->get('from', Carbon::today('Asia/Beirut')->toDateString());
    $to = $request->get('to', Carbon::today('Asia/Beirut')->toDateString());

    $bookings = Booking::with(['customer', 'service'])
        ->where('business_id', $businessId)
        ->where('status', 'completed')
        ->whereBetween('booking_date', [$from, $to])
        ->orderBy('booking_date')
        ->orderBy('start_time')
        ->get();

    $totalAmount = $bookings->sum(function ($booking) {
        return $booking->service->price ?? 0;
    });

    return view('owner.bookings.completed', compact(
        'bookings',
        'from',
        'to',
        'totalAmount'
    ));
}
    

    public function updateStatus(Request $request, Booking $booking)
    {
        $user = Auth::guard('business')->user();

        abort_if($booking->business_id !== $user->business_id, 403);

        $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
        ]);

        $booking->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Booking status updated successfully.');
    }

    public function destroy(Booking $booking)
    {
        $user = Auth::guard('business')->user();

        abort_if($booking->business_id !== $user->business_id, 403);

        $booking->delete();

        return back()->with('success', 'Booking deleted successfully.');
    }

public function partial()
{
    $user = Auth::guard('business')->user();
    $businessId = $user->business_id;

    $today = Carbon::today('Asia/Beirut');
    $tomorrow = Carbon::tomorrow('Asia/Beirut');

    $baseQuery = Booking::with(['customer', 'service'])
        ->where('business_id', $businessId)
        ->whereIn('status', ['pending', 'confirmed']);

    $todayBookings = (clone $baseQuery)
        ->whereDate('booking_date', $today->toDateString())
        ->orderBy('start_time')
        ->get();

    $tomorrowBookings = (clone $baseQuery)
        ->whereDate('booking_date', $tomorrow->toDateString())
        ->orderBy('start_time')
        ->get();

    $thisWeekBookings = (clone $baseQuery)
        ->whereBetween('booking_date', [
            $today->copy()->startOfWeek()->toDateString(),
            $today->copy()->endOfWeek()->toDateString(),
        ])
        ->orderBy('booking_date')
        ->orderBy('start_time')
        ->get();

    $thisMonthBookings = (clone $baseQuery)
        ->whereYear('booking_date', $today->year)
        ->whereMonth('booking_date', $today->month)
        ->orderBy('booking_date')
        ->orderBy('start_time')
        ->get();

    $thisYearBookings = (clone $baseQuery)
        ->whereYear('booking_date', $today->year)
        ->orderBy('booking_date')
        ->orderBy('start_time')
        ->get();

    return view('owner.bookings.partials.list', compact(
        'todayBookings',
        'tomorrowBookings',
        'thisWeekBookings',
        'thisMonthBookings',
        'thisYearBookings'
    ));
}

}