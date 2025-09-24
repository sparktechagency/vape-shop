<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via($notifiable)
    {
        // return ['mail', 'database'];
        return ['database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Your Subscription Has Been Cancelled')
                    ->markdown('emails.subscription_cancelled', [
                        'subscription' => $this->subscription,
                        'user' => $notifiable
                    ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Subscription Cancelled',
            'message' => "Your subscription has been cancelled",
            'link' => '/subscription',
        ];
    }
}
