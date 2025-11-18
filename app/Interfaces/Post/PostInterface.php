<?php
namespace App\Interfaces\Post;
use Illuminate\Http\Request;

interface PostInterface
{
    public function createPost(array $data, ?Request $request = null);
    public function updatePost(int $postId, array $data);
    public function deletePost(int $postId);
    public function getPostById(int $postId);
    public function getAllPosts();
    public function getTrendingPosts();
    // public function likePost(int $postId, int $userId);
    // public function unlikePost(int $postId, int $userId);
    // public function commentOnPost(int $postId, int $userId, string $comment);
    // public function deleteComment(int $commentId);
}
