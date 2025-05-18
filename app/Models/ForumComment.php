<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumComment extends Model
{
    protected $guarded = ['id'];
    //table name
    protected $table = 'forum_comments';

    public function thread()
    {
        return $this->belongsTo(ForumThread::class, 'thread_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function parent()
    // {
    //     return $this->belongsTo(ForumComment::class, 'parent_id');
    // }

    public function replies()
    {
        return $this->hasMany(ForumComment::class, 'parent_id')->with('replies');
    }
}
