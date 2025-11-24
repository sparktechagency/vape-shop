<?php

namespace App\Models;

use App\Enums\UserRole\Role;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'is_in_gallery',
        'title',
        'content',
        'article_image',
        'content_type',
    ];

    protected $appends = [
        'role',
        'like_count',
        'hearts_count',  // <--- ঠিক আছে
        'is_post_liked',
        'is_hearted',    // <--- (NEW) এইটা মিসিং ছিল
    ];

    protected $hidden = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hearts()
    {
        return $this->hasMany(PostLike::class)->where('type', 'heart');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class)->where('type', 'like');
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }

    public function likeCount()
    {
        return $this->likes()->count();
    }

    public function commentCount()
    {
        return $this->comments()->count();
    }

    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function isCommentedBy($userId)
    {
        return $this->comments()->where('user_id', $userId)->exists();
    }

    //attributes
    public function getRoleAttribute()
    {
        return $this->user && $this->user->role ? Role::from($this->user->role) : null;
    }

    public function getArticleImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    // --- (FIXED) Like Count Attribute ---
    public function getLikeCountAttribute()
    {
        // আগে চেক করবে withCount করা আছে কিনা, না থাকলে কুয়েরি করবে
        return $this->attributes['likes_count'] ?? $this->likes()->count();
    }

    // --- (NEW) Heart Count Attribute ---
    public function getHeartsCountAttribute()
    {
        // (NEW) এই ফাংশনটা মিসিং ছিল যার কারণে এরর আসতো
        return $this->attributes['hearts_count'] ?? $this->hearts()->count();
    }

    // --- (FIXED) Is Post Liked Attribute ---
    public function getIsPostLikedAttribute()
    {
        // আগে চেক করবে withExists করা আছে কিনা
        if (array_key_exists('is_post_liked', $this->attributes)) {
            return (bool) $this->attributes['is_post_liked'];
        }
        $userId = auth()->id();
        return $userId ? $this->likes()->where('user_id', $userId)->exists() : false;
    }

    // --- (NEW) Is Hearted Attribute ---
    public function getIsHeartedAttribute()
    {
        if (array_key_exists('is_hearted', $this->attributes)) {
            return (bool) $this->attributes['is_hearted'];
        }
        $userId = auth()->id();
        return $userId ? $this->hearts()->where('user_id', $userId)->exists() : false;
    }

    public function postImages()
    {
        return $this->hasMany(PostImage::class);
    }
}
