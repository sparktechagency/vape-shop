<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FourmLike extends Model
{
    protected $table = 'forum_likes';
    protected $guarded = ['id'];

    // Polymorphic relationship
    public function likeable()
    {
        return $this->morphTo();
    }

    // User who liked the item
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
