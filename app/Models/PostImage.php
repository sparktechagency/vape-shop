<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    protected $guarded = [];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Accessor to get full URL of the image
    public function getImagePathAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
}
