<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrendingProducts extends Model
{
    protected $guarded = ['id'];

     protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

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

    //category relationship
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    //region relationship
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    //relationship approveby
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    //relationship rejectedby
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

}
