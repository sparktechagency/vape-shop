<?php

namespace App\Observers;

use App\Models\Slider;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class SliderObserver
{
    /**
     * Handle the Slider "created" event.
     */
    public function created(Slider $slider): void
    {
        $this->clearSliderRelatedCache($slider, 'created');
    }

    /**
     * Handle the Slider "updated" event.
     */
    public function updated(Slider $slider): void
    {
        $this->clearSliderRelatedCache($slider, 'updated');
    }

    /**
     * Handle the Slider "deleted" event.
     */
    public function deleted(Slider $slider): void
    {
        $this->clearSliderRelatedCache($slider, 'deleted');
    }

    /**
     * Handle the Slider "restored" event.
     */
    public function restored(Slider $slider): void
    {
        $this->clearSliderRelatedCache($slider, 'restored');
    }

    /**
     * Handle the Slider "force deleted" event.
     */
    public function forceDeleted(Slider $slider): void
    {
        $this->clearSliderRelatedCache($slider, 'force_deleted');
    }

    /**
     * Clear cache related to slider changes
     */
    private function clearSliderRelatedCache(Slider $slider, string $action): void
    {
        try {
            // Clear slider related cache using CacheService
            CacheService::clearSliderCache();

            Log::info("Cache cleared for slider: {$slider->id} after {$action}");

        } catch (\Exception $e) {
            Log::error("Failed to clear cache for slider: {$slider->id} after {$action}. Error: " . $e->getMessage());
        }
    }
}
