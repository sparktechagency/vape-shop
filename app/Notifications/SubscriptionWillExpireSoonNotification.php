<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionWillExpireSoonNotification extends Notification implements ShouldQueue
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
                    ->subject('Your Subscription is Expiring Soon!')
                    ->line("Hello {$notifiable->full_name},")
                    ->line("This is a friendly reminder that your subscription will expire in 3 days on {$this->subscription->ends_at->format('F j, Y')}.")
                    ->line('To avoid any interruption in your service, please renew your subscription.')
                    ->action('Renew Now', url('/subscription'));
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Subscription Expiring Soon',
            'message' => 'Your subscription will expire in 3 days. Renew now to continue enjoying our services.',
            'link' => '/subscription',
        ];
    }
}
