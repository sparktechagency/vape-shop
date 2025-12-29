<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class B2bSendRequest extends Notification implements ShouldQueue
{
    use Queueable;
    protected User $requester;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $requester)
    {
        $this->requester = $requester;
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'B2B Connection Request',
            'requester_id' => $this->requester->id,
            'requester_name' => $this->requester->first_name,
            'message' => "{$this->requester->first_name} has sent you a B2B connection request.",
        ];
    }
}
