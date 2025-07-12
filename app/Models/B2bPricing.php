<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2bPricing extends Model
{
    protected $guarded = ['id'];

    /**
     * Get the user that owns the B2B pricing.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product associated with the B2B pricing.
     */
    public function b2bPricing()
    {
        return $this->morphOne(B2bPricing::class, 'productable');
    }

    
}
