<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
       protected $guarded=['id'];

    public function sender(){
        return $this->belongsTo(User::class);
    }
    public function receiver(){
        return $this->belongsTo(User::class);
    }
}
