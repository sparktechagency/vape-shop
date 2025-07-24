<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class UserSuspendedNotification extends Notification
{
    use Queueable;

    protected string $reason;
    protected Carbon $suspendedUntil;

    public function __construct(string $reason, Carbon $suspendedUntil)
    {
        $this->reason = $reason;
        $this->suspendedUntil = $suspendedUntil;
    }

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
            'title' => 'Account Suspended',
            'message' => "Your account has been suspended. You will be able to access your account again on " . $this->suspendedUntil->format('d M, Y h:i A') . ".",
            'reason' => $this->reason,
            'suspended_until' => $this->suspendedUntil->toDateTimeString(),
        ];
    }
}
