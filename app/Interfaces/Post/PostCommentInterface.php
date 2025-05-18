<?php

namespace App\Interfaces\Post;
use App\Models\PostComment;

interface PostCommentInterface
{
    public function getCommentsByPostId(int $postId): array;

    public function createComment(array $data): PostComment;

    public function deleteComment(int $commentId): bool;

    // public function updateComment(int $commentId, string $comment): bool;
    // public function getCommentById(int $commentId): array;
}
