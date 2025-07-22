<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionRequestConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
         return (new MailMessage)
        ->subject('Subscription Request Received')
        ->markdown('emails.subscription_confirmation', [
            'user' => $notifiable,
        ]);
    }
}
