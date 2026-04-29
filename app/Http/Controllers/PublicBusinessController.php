<?php

namespace App\Http\Controllers;

use App\Models\BlockedTime;
use App\Models\Booking;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Service;
use App\Models\WeeklySchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use App\Events\NewOrderCreated;
use App\Events\NewBookingCreated;

class PublicBusinessController extends Controller
{
    public function show(string $slug)
    {
        $business = Business::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $settings = BusinessSetting::firstOrCreate(
            ['business_id' => $business->id],
            [
                'booking_enabled' => $business->mode === 'booking',
                'ordering_enabled' => $business->mode === 'menu',
                'public_theme' => 'default',
            ]
        );

        if ($business->mode === 'booking') {
            $services = Service::where('business_id', $business->id)
                ->where('is_active', true)
                ->get();

            return view('public.booking.index', compact(
                'business',
                'services',
                'settings'
            ));
        }

        if ($business->mode === 'menu') {
            $categories = \App\Models\Category::with(['menuItems' => function ($query) use ($business) {
                $query->where('business_id', $business->id)
                    ->where('is_available', true);
            }])
                ->where('business_id', $business->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            $isOpenNow = $this->isBusinessOpenNow($business);

            return view('public.menu.index', compact(
                'business',
                'categories',
                'isOpenNow',
                'settings'
            ));
        }

        abort(404);
    }

    private function isBusinessOpenNow(Business $business): bool
    {
        $timezone = 'Asia/Beirut';
        $now = now($timezone);

        $todaySchedule = WeeklySchedule::where('business_id', $business->id)
            ->where('day_of_week', $now->dayOfWeek)
            ->first();

        $yesterday = $now->copy()->subDay();

        $yesterdaySchedule = WeeklySchedule::where('business_id', $business->id)
            ->where('day_of_week', $yesterday->dayOfWeek)
            ->first();

        $periods = [];

        if ($todaySchedule && !$todaySchedule->is_off && $todaySchedule->open_time && $todaySchedule->close_time) {
            $open = Carbon::parse($now->toDateString() . ' ' . $todaySchedule->open_time, $timezone);
            $close = Carbon::parse($now->toDateString() . ' ' . $todaySchedule->close_time, $timezone);

            if ($close->lte($open)) {
                $close->addDay();
            }

            $periods[] = [$open, $close, $now->dayOfWeek, $now->toDateString()];
        }

        if ($yesterdaySchedule && !$yesterdaySchedule->is_off && $yesterdaySchedule->open_time && $yesterdaySchedule->close_time) {
            $open = Carbon::parse($yesterday->toDateString() . ' ' . $yesterdaySchedule->open_time, $timezone);
            $close = Carbon::parse($yesterday->toDateString() . ' ' . $yesterdaySchedule->close_time, $timezone);

            if ($close->lte($open)) {
                $close->addDay();
                $periods[] = [$open, $close, $yesterday->dayOfWeek, $yesterday->toDateString()];
            }
        }

        foreach ($periods as [$open, $close, $dayOfWeek, $date]) {
            if (!$now->between($open, $close)) {
                continue;
            }

            $blockedTimes = BlockedTime::where('business_id', $business->id)
                ->where(function ($query) use ($dayOfWeek, $now, $date) {
                    $query->where(function ($q) use ($dayOfWeek) {
                        $q->where('is_recurring', true)
                            ->where('day_of_week', $dayOfWeek);
                    })->orWhere(function ($q) use ($now, $date) {
                        $q->where('is_recurring', false)
                            ->whereIn('blocked_date', [$date, $now->toDateString()]);
                    });
                })
                ->get();

            foreach ($blockedTimes as $blocked) {
                if ($blocked->full_day) {
                    return false;
                }

                if (!$blocked->start_time || !$blocked->end_time) {
                    continue;
                }

                $blockedStart = Carbon::parse($date . ' ' . $blocked->start_time, $timezone);
                $blockedEnd = Carbon::parse($date . ' ' . $blocked->end_time, $timezone);

                if ($blockedEnd->lte($blockedStart)) {
                    $blockedEnd->addDay();
                }

                if ($now->between($blockedStart, $blockedEnd)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public function availableSlots(Request $request, string $slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();

        $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'date' => ['required', 'date'],
        ]);

        $service = Service::where('business_id', $business->id)
            ->findOrFail($request->service_id);

        $slots = $this->getAvailableSlots(
            $business,
            $request->date,
            $service->duration
        );

        return response()->json($slots);
    }

   public function storeBooking(Request $request, string $slug)
{
    $business = Business::where('slug', $slug)->firstOrFail();

    $request->validate([
        'service_id' => ['required'],
        'name' => ['required'],
        'phone' => ['required'],
        'booking_date' => ['required', 'date'],
        'start_time' => ['required'],
    ]);

    $service = Service::where('business_id', $business->id)
        ->findOrFail($request->service_id);

    $timezone = 'Asia/Beirut';

    $start = Carbon::parse($request->booking_date . ' ' . $request->start_time, $timezone);

    // Round service duration up to nearest 15 minutes
    $effectiveDuration = (int) ceil($service->duration / 15) * 15;

    $end = $start->copy()->addMinutes($effectiveDuration);

    $slots = $this->getAvailableSlots(
        $business,
        $request->booking_date,
        $service->duration
    );

    if (!in_array($start->format('H:i'), $slots)) {
        return back()->withErrors([
            'start_time' => 'Selected time is no longer available.',
        ]);
    }

    $capacity = max(1, (int) ($business->capacity_per_slot ?? 1));

    $overlapCount = Booking::where('business_id', $business->id)
        ->whereDate('booking_date', $request->booking_date)
        ->whereIn('status', ['pending', 'confirmed'])
        ->where(function ($query) use ($start, $end) {
            $query->where('start_time', '<', $end->format('H:i:s'))
                ->where('end_time', '>', $start->format('H:i:s'));
        })
        ->count();

    if ($overlapCount >= $capacity) {
        return back()->withErrors([
            'start_time' => 'This time slot is fully booked.',
        ]);
    }

    $customer = Customer::firstOrCreate(
        [
            'business_id' => $business->id,
            'phone' => $request->phone,
        ],
        [
            'name' => $request->name,
        ]
    );

    $booking = Booking::create([
        'business_id' => $business->id,
        'customer_id' => $customer->id,
        'customer_name' => $request->name,
        'customer_phone' => $request->phone,
        'service_id' => $service->id,
        'booking_date' => $request->booking_date,
        'start_time' => $start->format('H:i:s'),
        'end_time' => $end->format('H:i:s'),
        'status' => 'pending',
    ]);

    event(new NewBookingCreated($booking));

    return back()->with('success', 'Booking created successfully.');
}

    public function storeOrder(Request $request, string $slug)
    {
        $business = Business::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        abort_if($business->mode !== 'menu', 404);

        if (!$this->isBusinessOpenNow($business)) {
            return back()->withErrors([
                'order' => 'We are currently closed and not accepting orders right now.',
            ]);
        }

        $request->validate([
            'name' => ['required'],
            'phone' => ['required'],
            'items' => ['required'],
            'notes' => ['nullable'],
        ]);

        $items = json_decode($request->items, true);

        if (!is_array($items) || empty($items)) {
            return back()->withErrors(['items' => 'Invalid order items.']);
        }

        $customer = Customer::firstOrCreate(
            [
                'business_id' => $business->id,
                'phone' => $request->phone,
            ],
            [
                'name' => $request->name,
            ]
        );

        $total = 0;

        foreach ($items as $item) {
            $menuItem = \App\Models\MenuItem::where('business_id', $business->id)
                ->where('is_available', true)
                ->findOrFail($item['id']);

            $total += $menuItem->price * (int) $item['quantity'];
        }

        $order = Order::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'customer_name' => $request->name,
            'customer_phone' => $request->phone,
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'order_type' => 'instant',
            'status' => 'pending',
            'total_amount' => $total,
            'notes' => $request->notes,
        ]);

        foreach ($items as $item) {
            $menuItem = \App\Models\MenuItem::where('business_id', $business->id)
                ->where('is_available', true)
                ->findOrFail($item['id']);

            $quantity = (int) $item['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'unit_price' => $menuItem->price,
                'quantity' => $quantity,
                'total_price' => $menuItem->price * $quantity,
            ]);
        }

        event(new NewOrderCreated($order));

        return back()->with('success', 'Order created successfully.');
    }

    private function getAvailableSlots($business, string $date, int $duration): array
{
    $timezone = 'Asia/Beirut';

    $selectedDate = Carbon::parse($date, $timezone);
    $dayOfWeek = $selectedDate->dayOfWeek;

    $schedule = WeeklySchedule::where('business_id', $business->id)
        ->where('day_of_week', $dayOfWeek)
        ->first();

    if (!$schedule || $schedule->is_off || !$schedule->open_time || !$schedule->close_time) {
        return [];
    }

    // العرض الأساسي كل 30 دقيقة
    $interval = 30;

    // مدة الحجز الفعلية: أقل شيء 15 دقيقة، وتُقرب لأعلى 15
    $effectiveDuration = (int) ceil($duration / 15) * 15;

    $dayStart = Carbon::parse($date . ' ' . $schedule->open_time, $timezone);
    $dayEnd = Carbon::parse($date . ' ' . $schedule->close_time, $timezone);

    if ($dayEnd->lte($dayStart)) {
        $dayEnd->addDay();
    }

    $blockedTimes = BlockedTime::where('business_id', $business->id)
        ->where(function ($query) use ($date, $dayOfWeek) {
            $query->where(function ($q) use ($date) {
                $q->where('is_recurring', false)
                    ->where('blocked_date', $date);
            })->orWhere(function ($q) use ($dayOfWeek) {
                $q->where('is_recurring', true)
                    ->where('day_of_week', $dayOfWeek);
            });
        })
        ->get();

    $bookings = Booking::where('business_id', $business->id)
        ->whereDate('booking_date', $date)
        ->whereIn('status', ['pending', 'confirmed'])
        ->get();

    $capacity = max(1, (int) ($business->capacity_per_slot ?? 1));
    $now = now($timezone)->addMinutes(15);

    $candidates = [];

    // مواعيد أساسية: 09:00 / 09:30 / 10:00
    for ($cursor = $dayStart->copy(); $cursor->lt($dayEnd); $cursor->addMinutes($interval)) {
        $candidates[] = $cursor->copy();
    }

    // أضف وقت نهاية الحجوزات القصيرة مثل 09:15
    foreach ($bookings as $booking) {
        $bookingEnd = Carbon::parse($date . ' ' . $booking->end_time, $timezone);

        if ($bookingEnd->betweenIncluded($dayStart, $dayEnd)) {
            $candidates[] = $bookingEnd->copy();
        }
    }

    $slots = [];

    foreach ($candidates as $slotStart) {
        $slotEnd = $slotStart->copy()->addMinutes($effectiveDuration);

        if ($slotEnd->gt($dayEnd)) {
            continue;
        }

        if ($slotStart->lte($now)) {
            continue;
        }

        $blocked = false;

        foreach ($blockedTimes as $item) {
            if ($item->full_day) {
                $blocked = true;
                break;
            }

            if (!$item->start_time || !$item->end_time) {
                continue;
            }

            $blockedStart = Carbon::parse($date . ' ' . $item->start_time, $timezone);
            $blockedEnd = Carbon::parse($date . ' ' . $item->end_time, $timezone);

            if ($blockedEnd->lte($blockedStart)) {
                $blockedEnd->addDay();
            }

            if ($slotStart < $blockedEnd && $slotEnd > $blockedStart) {
                $blocked = true;
                break;
            }
        }

        if ($blocked) {
            continue;
        }

        $overlapCount = 0;

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($date . ' ' . $booking->start_time, $timezone);
            $bookingEnd = Carbon::parse($date . ' ' . $booking->end_time, $timezone);

            if ($bookingEnd->lte($bookingStart)) {
                $bookingEnd->addDay();
            }

            if ($slotStart < $bookingEnd && $slotEnd > $bookingStart) {
                $overlapCount++;
            }
        }

        if ($overlapCount < $capacity) {
            $slots[] = $slotStart->format('H:i');
        }
    }

    $slots = array_values(array_unique($slots));
    sort($slots);

    return $slots;
}
}