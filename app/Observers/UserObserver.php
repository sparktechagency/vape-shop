<?php

namespace App\Observers;

use App\Models\User;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->clearUserRelatedCache($user, 'created');
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $this->clearUserRelatedCache($user, 'updated');
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->clearUserRelatedCache($user, 'deleted');
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        $this->clearUserRelatedCache($user, 'restored');
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $this->clearUserRelatedCache($user, 'force_deleted');
    }

    /**
     * Clear cache related to user changes
     */
    private function clearUserRelatedCache(User $user, string $action): void
    {
        try {
            // Clear user related cache using CacheService
            CacheService::clearUserCache($user->id);

            // If user is a store (role 5), also clear store cache
            if ($user->role === 5 || $user->wasChanged('role')) {
                CacheService::clearStoreCache();
            }

            Log::info("Cache cleared for user: {$user->id} after {$action}");

        } catch (\Exception $e) {
            Log::error("Failed to clear cache for user: {$user->id} after {$action}. Error: " . $e->getMessage());
        }
    }
}
