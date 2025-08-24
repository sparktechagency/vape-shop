<?php

namespace App\Observers;

use App\Models\MostFollowerAd;
use Illuminate\Support\Facades\Cache;

class MostFollowerAdObserver
{
    /**
     * Handle the MostFollowerAd "created" event.
     */
    public function created(MostFollowerAd $mostFollowerAd): void
    {
        $this->clearFollowersAdCache();
    }

    /**
     * Handle the MostFollowerAd "updated" event.
     */
    public function updated(MostFollowerAd $mostFollowerAd): void
    {
        $this->clearFollowersAdCache();
    }

    /**
     * Handle the MostFollowerAd "deleted" event.
     */
    public function deleted(MostFollowerAd $mostFollowerAd): void
    {
        $this->clearFollowersAdCache();
    }

    /**
     * Handle the MostFollowerAd "restored" event.
     */
    public function restored(MostFollowerAd $mostFollowerAd): void
    {
        $this->clearFollowersAdCache();
    }

    /**
     * Handle the MostFollowerAd "force deleted" event.
     */
    public function forceDeleted(MostFollowerAd $mostFollowerAd): void
    {
        $this->clearFollowersAdCache();
    }

    /**
     * Clear all followers ad-related cache
     */
    private function clearFollowersAdCache(): void
    {
        try {
            // Clear cache tags related to trending and followers
            Cache::tags(['users', 'trending', 'followers', 'ads'])->flush();
        } catch (\Exception $e) {
            // Log error if needed
            logger('Failed to clear followers ad cache: ' . $e->getMessage());
        }
    }
}
