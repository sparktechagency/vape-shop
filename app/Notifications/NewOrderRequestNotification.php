<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;
use App\Models\User;

class NewOrderRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $customer;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, User $customer)
    {
        $this->order = $order;
        $this->customer = $customer;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     */
     public function toMail(object $notifiable): MailMessage
    {

        $storeOwnerName = $notifiable->full_name;
        $customerName =  $this->customer->full_name;
        $orderUrl = url('/store/orders/' . $this->order->id);

        return (new MailMessage)
                    ->subject("You Have a New Order Request! (Order #" . $this->order->id . ")")
                    ->greeting("Hello {$storeOwnerName},")
                    ->line("Congratulations! You have received a new order request from **{$customerName}**.")
                    ->line("Here are the order details:")
                    ->line('**Order ID:** ' . $this->order->id)
                    ->line('**Total Amount:** $' . $this->order->subtotal)
                    ->action('View Order Request', $orderUrl)
                    ->line('Please review the order and accept or reject it at your earliest convenience.');
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
            'customer_name' => $this->customer->full_name,
            'message' => "You have a new order request from {$this->customer->full_name}.",
            'link' => '/store/orders/' . $this->order->id,
        ];
    }
}
