<?php

namespace App\Notifications;

use App\Models\B2bConnection;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class B2bStatusRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;
    protected B2bConnection $B2bconnection;
    protected User $provieder;
    /**
     * Create a new notification instance.
     */
    public function __construct(B2bConnection $B2bconnection, User $provieder)
    {
        $this->B2bconnection = $B2bconnection;
        $this->provieder = $provieder;
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
            'title' => 'B2B Connection Request Status Update',
            'connection_id' => $this->B2bconnection->id,
            'status' => $this->B2bconnection->status,
            'message' => "Your B2B connection request has been {$this->B2bconnection->status} by {$this->provieder->first_name}.",
        ];
    }
}
