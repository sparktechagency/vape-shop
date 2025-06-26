<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewsLikeable extends Model
{
    protected $fillable = [
        'review_id',
        'user_id',
    ];

    protected $table = 'reviews_likeables';

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //is the review liked by the user?
    public function isLikedByUser($userId)
    {
        return $this->where('review_id', $this->review_id)
            ->where('user_id', $userId)
            ->exists();
    }
}
