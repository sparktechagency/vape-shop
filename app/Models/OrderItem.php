<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(StoreProduct::class, 'product_id');
    }
}
