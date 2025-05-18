<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumGroup extends Model
{
    protected $guarded = ['id'];
    //table name
    protected $table = 'forum_groups';
    protected $appends = ['total_threads', 'total_comments'];


    public function threads()
    {
        return $this->hasMany(ForumThread::class, 'group_id')->with(['user:id,first_name,last_name,role', 'comments']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //total threads
    public function getTotalThreadsAttribute()
    {
        return $this->threads()->count();
    }
    //total comments
    public function getTotalCommentsAttribute()
    {
        return $this->threads()->withCount('comments')->get()->sum('comments_count');
    }
}
