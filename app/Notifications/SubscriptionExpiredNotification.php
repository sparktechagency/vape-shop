<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionExpiredNotification extends Notification implements ShouldQueue
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
                    ->subject('Your Subscription Has Expired')
                    ->line("Hello {$notifiable->full_name},")
                    ->line('Your subscription has expired. You may lose access to some of our premium features.')
                    ->action('Renew Subscription', url('/subscription'));
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Subscription Expired',
            'message' => 'Your subscription has expired. Please renew to regain access.',
            'link' => '/subscription',
        ];
    }
}
