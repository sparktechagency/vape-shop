<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Order;

class OrderStatusUpdatedNotification extends Notification
{
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $storeName = $this->order->store->full_name;
        $status = ucfirst($this->order->status);

        return (new MailMessage)
            ->subject("Update on your order from {$storeName}")
            ->greeting("Hello {$notifiable->full_name},")
            ->line("We have an update on your order request placed with {$storeName}.")
            ->line("The status has been updated to: **{$status}**")
            ->action('View Order Details', url('/user/checkouts/' . $this->order->checkout_id))
            ->line('Thank you for your patience.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "Your order request from {$this->order->store->full_name} has been {$this->order->status}.",
            'link' => '/user/checkouts/' . $this->order->checkout_id,
            'order_id' => $this->order->id,
        ];
    }
}
