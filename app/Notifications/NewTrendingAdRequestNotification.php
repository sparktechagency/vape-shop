<?php

namespace App\Notifications;


use App\Models\TrendingProducts;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTrendingAdRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $trendingRequest;
    /**
     * Create a new notification instance.
     */
    public function __construct(TrendingProducts $trendingRequest)
    {
        $this->trendingRequest = $trendingRequest;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // return ['database', 'mail'];
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
        $this->trendingRequest->loadMissing('product', 'payments');
        $productName = $this->trendingRequest->product->product_image;
        return [
            'trending_request_id' => $this->trendingRequest->id,
            'product_id' => $this->trendingRequest->product_id,
            'product_name' => $productName,
            'amount' => $this->trendingRequest->amount,
            'status' => $this->trendingRequest->status,
            'requested_at' => $this->trendingRequest->requested_at,
            'message' => 'A new trending ad request has been made for product ID: ' . $this->trendingRequest->product_id,
            'time' => now(),
        ];
    }
}
