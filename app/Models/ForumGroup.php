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

    //members of the forum group
    public function members()
    {
        return $this->belongsToMany(User::class, 'forum_group_members')
            ->withPivot('status')
            ->withTimestamps();
    }

    // Approved members
    public function approvedMembers()
    {
        return $this->members()->wherePivot('status', 'approved');
    }

    // Pending requests
    public function pendingRequests()
    {
        return $this->members()->wherePivot('status', 'pending');
    }
}
