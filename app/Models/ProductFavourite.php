<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductFavourite extends Model
{
    protected $guarded = [];

    public function favouritable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
