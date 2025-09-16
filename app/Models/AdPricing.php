<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdPricing extends Model
{
    protected $guarded = ['id'];

    public function adSlot()
    {
        return $this->belongsTo(AdSlot::class);
    }

     protected $casts = [
        'details' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function getPriceAttribute($value)
    {
        return number_format($value, 2);
    }
}
