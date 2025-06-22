<?php

namespace App\Notifications;

use App\Models\MostFollowerAd;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMostFollowersAdRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public $mostFollowersAd;

    /**
     * Create a new notification instance.
     */
    public function __construct(MostFollowerAd $mostFollowersAd)
    {
        $this->mostFollowersAd = $mostFollowersAd;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //         ->line('The introduction to the notification.')
    //         ->action('Notification Action', url('/'))
    //         ->line('Thank you for using our application!');
    // }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'most_followers_ad_id' => $this->mostFollowersAd->id,
            'user_id' => $this->mostFollowersAd->user_id,
            'status' => $this->mostFollowersAd->status,
            'preferred_duration' => $this->mostFollowersAd->preferred_duration,
            'requested_at' => $this->mostFollowersAd->requested_at,
            'message' => 'A new Most Followers Ad request has been created.',
            'time'=> now()
        ];
    }
}
