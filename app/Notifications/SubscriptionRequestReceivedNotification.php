<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRequestReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Subscription $subscription) {}

    public function via($notifiable)
    {
        // return ['mail', 'database'];
        return ['database'];
    }

    public function toMail($notifiable)
    {
       return (new MailMessage)
        ->subject('New Subscription Request')
        ->markdown('emails.admin_subscription_request', [
            'subscription' => $this->subscription,
        ]);

    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Subscription Request',
            'subscription_id' => $this->subscription->id,
            'user_name' => $this->subscription->user->full_name,
            'message' => 'New subscription request received.',
        ];
    }
}
