<?php

namespace App\Observers;

use App\Models\ManageProduct;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class ManageProductObserver
{
    /**
     * Handle the ManageProduct "created" event.
     */
    public function created(ManageProduct $manageProduct): void
    {
        $this->clearProductRelatedCache($manageProduct, 'created');
    }

    /**
     * Handle the ManageProduct "updated" event.
     */
    public function updated(ManageProduct $manageProduct): void
    {
        $this->clearProductRelatedCache($manageProduct, 'updated');
    }

    /**
     * Handle the ManageProduct "deleted" event.
     */
    public function deleted(ManageProduct $manageProduct): void
    {
        $this->clearProductRelatedCache($manageProduct, 'deleted');
    }

    /**
     * Handle the ManageProduct "restored" event.
     */
    public function restored(ManageProduct $manageProduct): void
    {
        $this->clearProductRelatedCache($manageProduct, 'restored');
    }

    /**
     * Handle the ManageProduct "force deleted" event.
     */
    public function forceDeleted(ManageProduct $manageProduct): void
    {
        $this->clearProductRelatedCache($manageProduct, 'force_deleted');
    }

    /**
     * Clear cache related to product changes
     */
    private function clearProductRelatedCache(ManageProduct $product, string $action): void
    {
        try {
            // Clear product related cache using CacheService
            CacheService::clearProductCache();

            Log::info("Cache cleared for ManageProduct: {$product->id} after {$action}");

        } catch (\Exception $e) {
            Log::error("Failed to clear cache for ManageProduct: {$product->id} after {$action}. Error: " . $e->getMessage());
        }
    }
}
