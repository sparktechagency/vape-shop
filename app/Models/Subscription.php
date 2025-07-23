<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'plan_details' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan associated with the subscription.
     */
    //pending subscription count helper
    public static function pendingSubscriptionCount()
    {
        return static::where('invoice_status', 'pending_invoice')->count();
    }

}
