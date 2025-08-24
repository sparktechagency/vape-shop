<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        $this->clearCategoryRelatedCache($category, 'created');
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        $this->clearCategoryRelatedCache($category, 'updated');
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->clearCategoryRelatedCache($category, 'deleted');
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        $this->clearCategoryRelatedCache($category, 'restored');
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        $this->clearCategoryRelatedCache($category, 'force_deleted');
    }

    /**
     * Clear cache related to category changes
     */
    private function clearCategoryRelatedCache(Category $category, string $action): void
    {
        try {
            // Clear category related cache using CacheService
            CacheService::clearByTags(['categories', 'home', 'products', 'admin']);

            Log::info("Cache cleared for category: {$category->id} after {$action}");

        } catch (\Exception $e) {
            Log::error("Failed to clear cache for category: {$category->id} after {$action}. Error: " . $e->getMessage());
        }
    }
}
