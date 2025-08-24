<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class PostObserver
{
    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        $this->clearPostCache($post);
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        $this->clearPostCache($post);
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        $this->clearPostCache($post);
    }

    /**
     * Handle the Post "restored" event.
     */
    public function restored(Post $post): void
    {
        $this->clearPostCache($post);
    }

    /**
     * Handle the Post "force deleted" event.
     */
    public function forceDeleted(Post $post): void
    {
        $this->clearPostCache($post);
    }

    /**
     * Clear all post-related cache
     */
    private function clearPostCache(Post $post): void
    {
        try {
            // Clear cache tags related to posts and feeds
            Cache::tags(['posts', 'feed', 'users'])->flush();
            
            // Clear user-specific caches if needed
            if ($post->user_id) {
                Cache::tags(['users'])->flush();
            }
        } catch (\Exception $e) {
            // Log error if needed
            logger('Failed to clear post cache: ' . $e->getMessage());
        }
    }
}
