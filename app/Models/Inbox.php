<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inbox extends Model
{
    protected $guarded = ['id'];
    protected $table = 'inboxes';

    // Define the relationship with the sender
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Define the relationship with the receiver
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Define the relationship for replies
    public function replies()
    {
        return $this->hasMany(Inbox::class, 'parent_id')->with('sender:id,first_name,last_name,role,avatar');
    }
}
