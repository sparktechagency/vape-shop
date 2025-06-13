<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    // protected $table = 'countries';
    protected $guarded = ['id'];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }
}
