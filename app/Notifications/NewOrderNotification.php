<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', WebPushChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'new_order',
            'title' => 'New Order',
            'message' => 'New order #' . $this->order->order_number,
            'order_id' => $this->order->id,
            'url' => route('owner.orders.index'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'new_order',
            'title' => 'New Order',
            'message' => 'New order #' . $this->order->order_number,
            'order_id' => $this->order->id,
            'url' => route('owner.orders.index'),
        ]);
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('New Order')
            ->body('New order #' . $this->order->order_number)
            ->icon('/favicon.ico')
            ->action('View Order', route('owner.orders.index'));
    }
}