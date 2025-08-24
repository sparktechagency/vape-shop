<?php

namespace App\Observers;

use App\Models\Message;
use Illuminate\Support\Facades\Cache;

class MessageObserver
{
    /**
     * Handle the Message "created" event.
     */
    public function created(Message $message): void
    {
        $this->clearMessageCache();
    }

    /**
     * Handle the Message "updated" event.
     */
    public function updated(Message $message): void
    {
        $this->clearMessageCache();
    }

    /**
     * Handle the Message "deleted" event.
     */
    public function deleted(Message $message): void
    {
        $this->clearMessageCache();
    }

    /**
     * Handle the Message "restored" event.
     */
    public function restored(Message $message): void
    {
        $this->clearMessageCache();
    }

    /**
     * Handle the Message "force deleted" event.
     */
    public function forceDeleted(Message $message): void
    {
        $this->clearMessageCache();
    }

    /**
     * Clear message-related cache
     */
    private function clearMessageCache(): void
    {
        Cache::tags(['messages', 'users'])->flush();
    }
}
