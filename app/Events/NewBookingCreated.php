<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewBookingCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $booking;
    public $businessId;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking->load(['customer', 'service']);
        $this->businessId = $booking->business_id;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('bookings.' . $this->businessId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'new-booking';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->booking->id,
            'status' => $this->booking->status,
            'booking_date' => $this->booking->booking_date,
            'start_time' => $this->booking->start_time,
            'end_time' => $this->booking->end_time,
            'customer_name' => $this->booking->customer->name ?? '',
            'customer_phone' => $this->booking->customer->phone ?? '',
            'service_name' => $this->booking->service->name ?? '',
        ];
    }
}