<?php

namespace App\Observers;

use App\Models\Follower;
use Illuminate\Support\Facades\Cache;

class FollowerObserver
{
    /**
     * Handle the Follower "created" event.
     */
    public function created(Follower $follower): void
    {
        $this->clearFollowersCache();
    }

    /**
     * Handle the Follower "deleted" event.
     */
    public function deleted(Follower $follower): void
    {
        $this->clearFollowersCache();
    }

    /**
     * Handle the Follower "restored" event.
     */
    public function restored(Follower $follower): void
    {
        $this->clearFollowersCache();
    }

    /**
     * Handle the Follower "force deleted" event.
     */
    public function forceDeleted(Follower $follower): void
    {
        $this->clearFollowersCache();
    }

    /**
     * Clear all followers-related cache
     */
    private function clearFollowersCache(): void
    {
        try {
            // Clear cache tags related to followers, users, trending, and feed
            Cache::tags(['users', 'trending', 'followers', 'feed'])->flush();
        } catch (\Exception $e) {
            // Log error if needed
            logger('Failed to clear followers cache: ' . $e->getMessage());
        }
    }
}
