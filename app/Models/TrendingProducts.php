<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrendingProducts extends Model
{
    protected $guarded = ['id'];

    /**
     * Get all of the trading request's payments.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function product()
    {
        return $this->belongsTo(ManageProduct::class, 'product_id');
    }

    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
