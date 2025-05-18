<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumGroup extends Model
{
    protected $guarded = ['id'];
    //table name
    protected $table = 'forum_groups';


    public function threads()
    {
        return $this->hasMany(ForumThread::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
