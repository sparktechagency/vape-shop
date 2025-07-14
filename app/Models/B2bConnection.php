<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2bConnection extends Model
{
    protected $table = 'b2b_connections';
    protected $guarded = ['id'];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
