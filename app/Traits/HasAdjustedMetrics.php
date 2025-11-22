<?php

namespace App\Traits;

use App\Models\MetricAdjustment;

trait HasAdjustedMetrics
{
    /**
     * Polymorphic relationship to the metric_adjustments table.
     */
    public function metricAdjustments()
    {
        return $this->morphMany(MetricAdjustment::class, 'adjustable');
    }

    /**
     * Helper to calculate Total (Real Count + Adjusted Count).
     *
     * @param string $metricType  'follower' or 'heart'
     * @param int    $realCount   The count from existing real table
     * @return int
     */
    public function getAdjustedTotal(string $metricType, int $realCount): int
    {
        // Fetch the fake count for this specific metric type
        $adjustment = $this->metricAdjustments
                           ->where('metric_type', $metricType)
                           ->first();

        $fakeCount = $adjustment ? $adjustment->adjustment_count : 0;

        return $realCount + $fakeCount;
    }
}
