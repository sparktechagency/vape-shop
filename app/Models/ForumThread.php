<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumThread extends Model
{
    protected $guarded = ['id'];
    //table name
    protected $table = 'forum_threads';
    protected $appends = ['total_replies'];

    public function group()
    {
        return $this->belongsTo(ForumGroup::class, 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(ForumComment::class, 'thread_id')->with('user:id,first_name,last_name,role');
    }

    //attribute to get the count of comments
    public function getTotalRepliesAttribute()
    {
        return $this->comments()->whereNull('parent_id',)->count();
    }
}
