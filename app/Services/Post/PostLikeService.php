<?php
namespace App\Services\Post;

use App\Interfaces\Post\PostLikeInterface;

class PostLikeService
{
    protected $postLikeRepository;

    public function __construct(PostLikeInterface $postLikeRepository)
    {
        $this->postLikeRepository = $postLikeRepository;
    }

    public function tiggerLike(int $postId, int $userId, string $type): array
    {
        return $this->postLikeRepository->tiggerLike($postId, $userId, $type);
    }

    public function getLikesCount(int $postId, string $type): int
    {
        return $this->postLikeRepository->getLikesCount($postId, $type);
    }

    public function getLikesByPostId(int $postId, string $type): array
    {
        return $this->postLikeRepository->getLikesByPostId($postId, $type);
    }
}
