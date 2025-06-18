<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = ['image'];

    /**
     * Get the URL of the slider image.
     *
     * @return string
     */
    public function getImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
}
