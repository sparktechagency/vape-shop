<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Checkout;

class OrderRequestConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $checkout;

    public function __construct(Checkout $checkout)
    {
        $this->checkout = $checkout;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {

        $customerName = $notifiable->full_name;
        $checkoutUrl = url('/user/checkouts/' . $this->checkout->id);

        return (new MailMessage)
                    ->subject("Your Order Request (#{$this->checkout->checkout_group_id}) Has Been Received!")
                    ->greeting("Hello {$customerName},")
                    ->line('Thank you for your order request. We have received it and sent it to the respective stores for approval.')
                    ->line("You can track the status of all your order requests using the ID: **{$this->checkout->checkout_group_id}**")
                    ->action('Track Your Order', $checkoutUrl)
                    ->line('You will be notified as each store updates the status of your request.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'checkout_id' => $this->checkout->id,
            'checkout_group_id' => $this->checkout->checkout_group_id,
            'message' => "Your order request #{$this->checkout->checkout_group_id} has been submitted successfully.",
            'link' => '/user/checkouts/' . $this->checkout->id,
        ];
    }
}
