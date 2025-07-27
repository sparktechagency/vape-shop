<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledForStoreNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Notification-ti email ebong database, duti jaygatei pathano hobe.
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Customer-er naam ber kore ana hocche
        $customerName = $this->order->checkout->customer_name;
        $orderId = $this->order->id;
        $storeDashboardUrl = url('/me/orders/');

        return (new MailMessage)
            ->subject("Order Cancelled: #{$orderId}")
            ->markdown('emails.order_cancelled_for_store', [
                'customerName' => $customerName,
                'orderId' => $orderId,
                'url' => $storeDashboardUrl
            ]);
    }

    /**
     * Get the array representation of the notification for the database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'title' => 'Order Cancelled',
            'message' => "An order (#{$this->order->id}) has been cancelled by the customer.",
            'link' => '/me/orders/',
        ];
    }
}
