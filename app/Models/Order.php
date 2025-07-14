<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'subtotal' => 'decimal:2',
    ];

    public function checkout()
    {
        return $this->belongsTo(Checkout::class);
    }

    public function store()
    {
        return $this->belongsTo(User::class, 'store_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function OrderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    //b2b order items
    public function b2bOrderItems()
    {
        return $this->hasMany(B2BOrderItem::class);
    }
}
