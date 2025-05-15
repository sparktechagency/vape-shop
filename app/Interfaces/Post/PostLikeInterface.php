<?php
namespace App\Interfaces\Post;

interface PostLikeInterface
{
    //tiggerLike
    public function tiggerLike(int $postId, int $userId): bool;
    //likeCount
    public function getLikesCount(int $postId) : int;
    //getLikesByPostId
    public function getLikesByPostId(int $postId): array;
}
