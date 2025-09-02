<?php

namespace App\Observers;

use App\Models\Heart;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HeartObserver
{
    /**
     * Handle the Heart "created" event.
     */
    public function created(Heart $heart): void
    {
        $this->clearHeartRelatedCache($heart);
        Log::info("Heart cache cleared after heart created for product ID: " . $this->getProductId($heart));
    }

    /**
     * Handle the Heart "updated" event.
     */
    public function updated(Heart $heart): void
    {
        $this->clearHeartRelatedCache($heart);
        Log::info("Heart cache cleared after heart updated for product ID: " . $this->getProductId($heart));
    }

    /**
     * Handle the Heart "deleted" event.
     */
    public function deleted(Heart $heart): void
    {
        $this->clearHeartRelatedCache($heart);
        Log::info("Heart cache cleared after heart deleted for product ID: " . $this->getProductId($heart));
    }

    /**
     * Handle the Heart "restored" event.
     */
    public function restored(Heart $heart): void
    {
        $this->clearHeartRelatedCache($heart);
        Log::info("Heart cache cleared after heart restored for product ID: " . $this->getProductId($heart));
    }

    /**
     * Handle the Heart "force deleted" event.
     */
    public function forceDeleted(Heart $heart): void
    {
        $this->clearHeartRelatedCache($heart);
        Log::info("Heart cache cleared after heart force deleted for product ID: " . $this->getProductId($heart));
    }

    /**
     * Clear all heart-related cache
     */
    private function clearHeartRelatedCache(Heart $heart): void
    {
        try {
            // Clear trending most hearted products cache with all possible combinations
            Cache::tags(['products', 'trending', 'hearts'])->flush();

            // Clear brand specific cache if applicable
            if ($heart->manage_product_id) {
                Cache::tags(['brand', 'products'])->flush();
            }

            // Clear store specific cache if applicable
            if ($heart->store_product_id) {
                Cache::tags(['store', 'products'])->flush();
            }

            // Clear wholesaler specific cache if applicable
            if ($heart->wholesaler_product_id) {
                Cache::tags(['wholesaler', 'products'])->flush();
            }

            // Clear general product cache
            CacheService::clearByTags([
                CacheService::TAG_PRODUCTS,
                'trending',
                'hearts',
                'fevorite'  // Also clear favorite cache as used in frontend
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to clear heart-related cache: " . $e->getMessage());
        }
    }

    /**
     * Get product ID based on heart type
     */
    private function getProductId(Heart $heart): ?int
    {
        if ($heart->manage_product_id) {
            return $heart->manage_product_id;
        }
        if ($heart->store_product_id) {
            return $heart->store_product_id;
        }
        if ($heart->wholesaler_product_id) {
            return $heart->wholesaler_product_id;
        }
        return null;
    }
}
