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
    //relationship with manage product
    public function manageProduct()
    {
        return $this->belongsTo(ManageProduct::class);
    }
    //relationship with store product
    public function storeProduct()
    {
        return $this->belongsTo(StoreProduct::class);
    }

    //get is_heart attribute
    public function getIsHeartedAttribute()
    {
        return true;
    }
}
