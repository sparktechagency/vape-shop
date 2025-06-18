<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $guarded = ['id'];

    //table name
    protected $table = 'reviews';


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
    //relationship with region
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
