<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2BOrderItem extends Model
{
    protected $guarded = ['id'];

    public function productable()
    {
        return $this->morphTo();
    }
}
