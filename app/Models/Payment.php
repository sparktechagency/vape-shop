<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = ['id'];


    /**
     * Get the parent payable model (TradingProduct,).
     */
    public function payable()
    {
        return $this->morphTo();
    }

    
}
