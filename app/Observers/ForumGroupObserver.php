<?php

namespace App\Observers;

use App\Models\ForumGroup;
use Illuminate\Support\Facades\Cache;

class ForumGroupObserver
{
    /**
     * Handle the ForumGroup "created" event.
     */
    public function created(ForumGroup $forumGroup): void
    {
        $this->clearForumCache();
    }

    /**
     * Handle the ForumGroup "updated" event.
     */
    public function updated(ForumGroup $forumGroup): void
    {
        $this->clearForumCache();
    }

    /**
     * Handle the ForumGroup "deleted" event.
     */
    public function deleted(ForumGroup $forumGroup): void
    {
        $this->clearForumCache();
    }

    /**
     * Handle the ForumGroup "restored" event.
     */
    public function restored(ForumGroup $forumGroup): void
    {
        $this->clearForumCache();
    }

    /**
     * Handle the ForumGroup "force deleted" event.
     */
    public function forceDeleted(ForumGroup $forumGroup): void
    {
        $this->clearForumCache();
    }

    /**
     * Clear all forum-related cache
     */
    private function clearForumCache(): void
    {
        try {
            // Clear cache tags related to forum groups
            Cache::tags(['forum', 'groups'])->flush();
        } catch (\Exception $e) {
            // Log error if needed
            logger('Failed to clear forum cache: ' . $e->getMessage());
        }
    }
}
