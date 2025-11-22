<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MetricAdjustment extends Model
{
    protected $guarded = [];

    public function adjustable(): MorphTo
    {
        return $this->morphTo();
    }
}
