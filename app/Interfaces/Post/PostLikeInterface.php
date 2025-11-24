<?php

namespace App\Interfaces\Post;

interface PostLikeInterface
{
    //tiggerLike
    public function tiggerLike(int $postId, int $userId, string $type = 'like'); 
    public function getLikesCount(int $postId, string $type = 'like'): int;
    public function getLikesByPostId(int $postId, string $type = 'like'): array;
}
