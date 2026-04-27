<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $businessId;

    public function __construct(Order $order)
    {
        $this->order = $order->load(['customer', 'items']);
        $this->businessId = $order->business_id;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.' . $this->businessId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'new-order';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'total_amount' => $this->order->total_amount,
            'notes' => $this->order->notes,
            'customer_name' => $this->order->customer->name,
            'customer_phone' => $this->order->customer->phone,
            'created_at' => $this->order->created_at->format('d-m-Y H:i'),
        ];
    }
}