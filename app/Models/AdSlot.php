<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSlot extends Model
{
    protected $guarded = ['id'];

    public function adPricings()
    {
        return $this->hasMany(AdPricing::class);
    }

    
}
