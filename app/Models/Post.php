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
        'content_type', // 'post' or 'article'
        // 'image',
    ];

    protected $appends = [
        'role',
        'like_count',
        'is_post_liked',
    ];
    protected $hidden = ['user'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
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
    //role attribute
    public function getRoleAttribute()
    {
        return $this->user->role ? Role::from($this->user->role) : null;
    }

    //image attribute
    public function getArticleImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    //get attribute for postlikecoutn
    public function getLikeCountAttribute()
    {
        return $this->likes()->count();
    }

    //is post liked by user
    public function getIsPostLikedAttribute()
    {
        $userId = auth()->id();
        return $userId ? $this->likes()->where('user_id', $userId)->exists() : false;
    }

    public function postImages()
    {
        return $this->hasMany(PostImage::class);
    }
}
