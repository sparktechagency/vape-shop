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

    public function tiggerLike(int $postId, int $userId): bool
    {
        return $this->postLikeRepository->tiggerLike($postId, $userId);
    }

    public function getLikesCount(int $postId): int
    {
        return $this->postLikeRepository->getLikesCount($postId);
    }

    public function getLikesByPostId(int $postId): array
    {
        return $this->postLikeRepository->getLikesByPostId($postId);
    }
}
