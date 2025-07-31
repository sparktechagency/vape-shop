<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MostFollowerAd extends Model
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


   //region relationship
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }



    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
