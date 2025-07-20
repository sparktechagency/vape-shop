<?php

namespace App\Models;

use App\Enums\UserRole\Role;
use Illuminate\Database\Eloquent\Model;

class StoreProduct extends Model
{
    protected $table = 'store_products';
    protected $guarded = ['id'];
    protected $casts = [
        'product_faqs' => 'array',
    ];
    protected $appends = [
        'role',
        'role_label',
        'is_hearted',
        'total_heart',
    ];

    protected $hidden = ['user'];

    //product_faqs attribute
    public function setProductFaqsAttribute($value)
    {
        $this->attributes['product_faqs'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getProductFaqsAttribute($value)
    {
        if (!$value) return null;

        $decoded = json_decode($value, true);

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return $decoded;
    }

    public function getProductImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    //role attribute
    public function getRoleAttribute()
    {
        return $this->user->role;
    }
    public function getRoleLabelAttribute()
    {
        return $this->user->role ? $this->user->role->label() : null;
    }

    //hearted product count
    public function getTotalHeartAttribute()
    {
        return $this->hasMany(Heart::class, 'store_product_id')->count();
    }

    //is_hearted attribute
    public function getIsHeartedAttribute()
    {
        return $this->hasMany(Heart::class, 'store_product_id')
            ->where('user_id', auth()->id())
            ->exists();
    }

    //Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    //manage products
    public function manageProducts()
    {
        return $this->belongsTo(ManageProduct::class, 'product_id');
    }

    //hearts
    public function hearts()
    {
        return $this->hasMany(Heart::class, 'store_product_id');
    }

    //reviews
    public function reviews()
    {
        return $this->hasMany(Review::class, 'store_product_id');
    }

    public function b2bPricing()
    {
        return $this->morphOne(B2bPricing::class, 'productable');
    }
    public function orderItems()
    {
        return $this->morphMany(B2BOrderItem::class, 'productable');
    }
}
