<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Subscription is Now Active!')
            ->markdown('emails.subscription_activated', [
                'subscription' => $this->subscription,
                'user' => $notifiable
            ]);
    }

    public function toDatabase($notifiable)
    {
        $planNames = implode(', ', array_column($this->subscription->plan_details, 'name'));
        return [
            'title' => 'Subscription Activated',
            'message' => "Congratulations! Your subscription has been successfully activated.",
            'link' => '/subscription', // User-er subscription page-er link
        ];
    }
}
