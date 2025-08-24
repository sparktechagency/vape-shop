<?php

namespace App\Observers;

use App\Models\ForumThread;
use Illuminate\Support\Facades\Cache;

class ForumThreadObserver
{
    /**
     * Handle the ForumThread "created" event.
     */
    public function created(ForumThread $forumThread): void
    {
        $this->clearForumThreadCache();
    }

    /**
     * Handle the ForumThread "updated" event.
     */
    public function updated(ForumThread $forumThread): void
    {
        $this->clearForumThreadCache();
    }

    /**
     * Handle the ForumThread "deleted" event.
     */
    public function deleted(ForumThread $forumThread): void
    {
        $this->clearForumThreadCache();
    }

    /**
     * Handle the ForumThread "restored" event.
     */
    public function restored(ForumThread $forumThread): void
    {
        $this->clearForumThreadCache();
    }

    /**
     * Handle the ForumThread "force deleted" event.
     */
    public function forceDeleted(ForumThread $forumThread): void
    {
        $this->clearForumThreadCache();
    }

    /**
     * Clear all forum thread-related cache
     */
    private function clearForumThreadCache(): void
    {
        try {
            // Clear cache tags related to forum threads
            Cache::tags(['forum', 'threads', 'groups'])->flush();
        } catch (\Exception $e) {
            // Log error if needed
            logger('Failed to clear forum thread cache: ' . $e->getMessage());
        }
    }
}
