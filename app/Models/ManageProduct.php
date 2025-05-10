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
    ];

    protected $hidden = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
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

}
