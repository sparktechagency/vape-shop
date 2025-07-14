<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumComment extends Model
{
    protected $guarded = ['id'];
    //table name
    protected $table = 'forum_comments';
    protected $appends = ['total_likes','is_liked'];

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

    // Polymorphic relationship for likes
    public function likes()
    {
        return $this->morphMany(FourmLike::class, 'likeable');
    }

    //total likes count
    public function getTotalLikesAttribute()
    {
        return $this->likes()->count();
    }
    // is liked by user
    public function getIsLikedAttribute()
    {
        return $this->likes()->where('user_id', auth()->id())->exists();
    }
}
