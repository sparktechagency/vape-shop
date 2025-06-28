<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    protected $guarded = ['id'];


    //relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
