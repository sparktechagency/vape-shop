<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $guarded = ['id'];

    //table name
    protected $table = 'reviews';
    protected $appends = [
        'is_liked',
    ];


    //relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //relationship with manage product
    public function manageProducts()
    {
        return $this->belongsTo(ManageProduct::class, 'manage_product_id');
    }
    //relationship with store product
    public function storeProducts()
    {
        return $this->belongsTo(StoreProduct::class, 'store_product_id');
    }

    //relationship with wholesaler product
    public function wholesalerProducts()
    {
        return $this->belongsTo(WholesalerProduct::class, 'wholesaler_product_id');
    }
    //relationship with region
    public function region()
    {
        return $this->belongsTo(Region::class);
    }



    //likeable relationship
    public function likedByUsers(){
        return $this->belongsToMany(
            User::class,
            'reviews_likeables',
            'review_id',
            'user_id'
        );
    }

    //replies relationship
    public function replies()
    {
        return $this->hasMany(Review::class, 'parent_id')->with('user:id,first_name,last_name,role,avatar');
    }

    //is liked attribute
    public function getIsLikedAttribute()
    {
        return $this->likedByUsers()->exists();
    }
}
