<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedAd extends Model
{
    protected $guarded = ['id'];

    //relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    //relationships article
    public function FeaturedArticle(){
        return $this->belongsTo(Post::class, 'featured_article_id');
    }
}
