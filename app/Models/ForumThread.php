<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumThread extends Model
{
    protected $guarded = ['id'];
    //table name
    protected $table = 'forum_threads';

    public function group()
    {
        return $this->belongsTo(ForumGroup::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(ForumComment::class);
    }
}
