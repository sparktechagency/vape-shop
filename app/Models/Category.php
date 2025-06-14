<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = ['id'];

    public function manage_products()
    {
        return $this->hasMany(ManageProduct::class, 'category_id');
    }

    public function store_products()
    {
        return $this->hasMany(StoreProduct::class, 'category_id');
    }

}
