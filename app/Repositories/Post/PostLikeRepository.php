<?php

namespace App\Repositories\Post;

use App\Interfaces\Post\PostLikeInterface;
use App\Models\PostLike;

class PostLikeRepository implements PostLikeInterface
{

    protected $likeModel;
    public function __construct(PostLike $likeModel)
    {
        $this->likeModel = $likeModel;
    }

    public function tiggerLike(int $postId, int $userId, string $type = 'like'): array
    {
        $like = $this->likeModel->where('post_id', $postId)
            ->where('user_id', $userId)
            ->where('type', $type)
            ->first();

        $is_active = false;

        if ($like) {
            $like->delete();
            $is_active = false;
        } else {
            $this->likeModel->create([
                'post_id' => $postId,
                'user_id' => $userId,
                'type'    => $type,
            ]);
            $is_active = true;
        }
        $count = $this->getLikesCount($postId, $type);

        return [
            'is_active' => $is_active,
            'count' => $count,
            'type' => $type
        ];
    }

    public function getLikesCount(int $postId, string $type = 'like'): int
    {
        return $this->likeModel->where('post_id', $postId)
            ->where('type', $type)
            ->count();
    }

    //getLikesByPostId
    public function getLikesByPostId(int $postId, string $type = 'like'): array
    {
        return $this->likeModel->with('user:id,first_name,last_name,role,avatar')
            ->where('post_id', $postId)
            ->where('type', $type) 
            ->get()
            ->toArray();
    }
}
