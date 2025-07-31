<?php

namespace App\Notifications;

use App\Models\FeaturedAd;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFeturedAdRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public $featuredAd;
    /**
     * Create a new notification instance.
     */
    public function __construct(FeaturedAd $featuredAd)
    {
        $this->featuredAd = $featuredAd;
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
            'featured_ad_id' => $this->featuredAd->id,
            'featured_article_id' => $this->featuredAd->featured_article_id,
            'region_id' => $this->featuredAd->region_id,
            'preferred_duration' => $this->featuredAd->preferred_duration,
            'amount' => $this->featuredAd->amount,
            'slot' => $this->featuredAd->slot,
            'user_id' => $this->featuredAd->user_id,
            'user_name' => $this->featuredAd->user ? $this->featuredAd->user->name : null,
            'message' => 'A new featured ad request has been made by ' . ($this->featuredAd->user ? $this->featuredAd->user->name : 'Unknown User'),
            'time' => now(),
        ];
    }
}
