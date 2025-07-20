<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = ['id'];

    //relationship with Subscription
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    //relationship with Payment
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
