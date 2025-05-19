<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Heart extends Model
{
    protected $guarded = ['id'];
    //table name
    protected $table = 'hearts';
    protected $appends = ['is_hearted'];

    //relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    //relationship with product
    public function product()
    {
        return $this->belongsTo(ManageProduct::class, 'product_id');
    }

    //get is_heart attribute
    public function getIsHeartedAttribute()
    {
        return true;
    }
}
