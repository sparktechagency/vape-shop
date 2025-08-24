<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->clearProductRelatedCache($product, 'created');
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->clearProductRelatedCache($product, 'updated');
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->clearProductRelatedCache($product, 'deleted');
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        $this->clearProductRelatedCache($product, 'restored');
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        $this->clearProductRelatedCache($product, 'force_deleted');
    }

    /**
     * Clear cache related to product changes
     */
    private function clearProductRelatedCache(Product $product, string $action): void
    {
        try {
            // Clear product related cache
            CacheService::clearProductCache();

            Log::info("Cache cleared for product: {$product->id} after {$action}");

        } catch (\Exception $e) {
            Log::error("Failed to clear cache for product: {$product->id} after {$action}. Error: " . $e->getMessage());
        }
    }
}
