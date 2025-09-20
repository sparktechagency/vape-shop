<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

class Address extends Model
{
    use HasSpatial;
    protected $guarded = ['id'];

     protected $casts = [
        'location' => Point::class,
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

     public function addressable()
    {
        return $this->morphTo();
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
