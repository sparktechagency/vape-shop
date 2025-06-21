<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    protected $guarded = ['id'];

    //relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //relationship with favourite
    public function favourite()
    {
        return $this->belongsTo(User::class, 'favourite_id');
    }
}
