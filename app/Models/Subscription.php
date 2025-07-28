<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Subscription extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'plan_details' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

     protected $appends = ['user'];

    protected $hidden = ['subscribable'];

    /**
     * Get the user that owns the subscription.
     */
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    //polymorphic relationship
    public function subscribable()
    {
        return $this->morphTo();
    }

    /**
     * Get the plan associated with the subscription.
     */
    //pending subscription count helper
    public static function pendingSubscriptionCount()
    {
        return static::where('invoice_status', 'pending_invoice')->count();
    }

    protected function user(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->subscribable instanceof User) {
                    return $this->subscribable;
                }
                if ($this->subscribable instanceof Branch) {
                    return $this->subscribable->owner;
                }
                return null;
            }
        );
    }

}
