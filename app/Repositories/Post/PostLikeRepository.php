<?php
namespace App\Repositories\Post;

use App\Interfaces\Post\PostLikeInterface;
use App\Models\PostLike;

class PostLikeRepository implements PostLikeInterface {

    protected $likeModel;
    public function __construct(PostLike $likeModel)
    {
        $this->likeModel = $likeModel;
    }

    public function tiggerLike(int $postId, int $userId):bool
    {
        $like = $this->likeModel->where('post_id', $postId)
                     ->where('user_id', $userId)
                     ->first();

        if ($like) {
            // If the like exists, delete it (unlike)
            $like->delete();
            return false; // Unliked
        } else {
            // If the like does not exist, create it (like)
            $this->likeModel->create([
                'post_id' => $postId,
                'user_id' => $userId,
            ]);
            return true; // Liked
        }
    }

    public function getLikesCount(int $postId) :int
    {
        return $this->likeModel->where('post_id', $postId)->count();
    }

    //getLikesByPostId
    public function getLikesByPostId(int $postId): array
    {
        return $this->likeModel->with('user:id,first_name,last_name,role,avatar', 'post:id,user_id,title,content')
                    ->where('post_id', $postId)
                    ->get()
                    ->toArray();
    }



}
