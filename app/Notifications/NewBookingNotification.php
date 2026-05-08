<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewBookingNotification extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast', WebPushChannel::class];
    }
    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('New Booking')
            ->body($this->booking->customer_name . ' booked ' . $this->booking->service->name);
    }


    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'New Booking',
            'message' => $this->booking->customer_name . ' booked ' . $this->booking->service->name,
            'booking_id' => $this->booking->id,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Booking',
            'message' => $this->booking->customer_name . ' booked ' . $this->booking->service->name,
        ]);
    }
}