<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2BOrderItem extends Model
{
    protected $guarded = ['id'];
    protected $table = 'b2b_order_items';


    public function productable()
    {
        return $this->morphTo();
    }
}
