<?php

namespace App\Models;

use App\Enums\UserRole\Role;
use Illuminate\Database\Eloquent\Model;

class ManageProduct extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'product_faqs' => 'array',
    ];

    protected $appends = [
        'role',
        'role_label',
        'is_hearted',
        'total_heart',
        'average_rating',
    ];

    protected $hidden = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //category relationship
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    //store product relationship
    public function storeProduct()
    {
        return $this->hasMany(StoreProduct::class, 'product_id');
    }

    //heart relationship
    public function hearts()
    {
        return $this->hasMany(Heart::class, 'manage_product_id');
    }

    //reviews relationship
    public function reviews()
    {
        return $this->hasMany(Review::class, 'manage_product_id');
    }


    public function getProductImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    public function getProductDiscountAttribute($value)
    {
        return $value ? $value . '%' : null;
    }

    //role attribute
    public function getRoleAttribute()
    {
        return $this->user->role;
    }
    public function getRoleLabelAttribute()
    {
        return $this->user->role ? Role::from($this->user->role)->label() : null;
    }

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

    //hearted product count
    public function getTotalHeartAttribute()
    {
        return $this->hasMany(Heart::class, 'manage_product_id')->count();
    }

    //is_hearted attribute
    public function getIsHeartedAttribute()
    {
        return $this->hasMany(Heart::class, 'manage_product_id')
            ->where('user_id', auth()->id())
            ->exists();
    }

    //avarage rating
    public function getAverageRatingAttribute()
    {
        $avg = $this->reviews()->avg('rating');
        if ($avg === null) {
            return 0;
        }
        return round($avg, 1);
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
