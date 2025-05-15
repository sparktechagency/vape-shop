<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Services\Post\PostLikeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikePostController extends Controller
{
    protected $postLikeService;
    public function __construct(PostLikeService $postLikeService)
    {
        $this->postLikeService = $postLikeService;
    }
    //tigger like post
    public function tiggerLike($postId)
    {
        try {
            $userId = Auth::id();
            $tiggerLike = $this->postLikeService->tiggerLike((int)$postId, $userId);
            // dd($tiggerLike);
            if ($tiggerLike) {
                return response()->successResponse($tiggerLike, 'Post liked successfully');
            } else {
                return response()->successResponse($tiggerLike, 'Post unliked successfully');
            }
        } catch (\Exception $e) {
            return response()->errorResponse('Failed to like post', 500, $e->getMessage());
        }
    }

    //get likes count
    public function getLikesCount($postId)
    {
        try {
            $likesCount = $this->postLikeService->getLikesCount((int)$postId);
            return response()->successResponse($likesCount, 'Likes count retrieved successfully');
        } catch (\Exception $e) {
            return response()->errorResponse('Failed to retrieve likes count', 500, $e->getMessage());
        }
    }
    //get likes by post id
    public function getLikesByPostId($postId)
    {
        try {
            $likes = $this->postLikeService->getLikesByPostId((int)$postId);
            return response()->successResponse($likes, 'Likes retrieved successfully');
        } catch (\Exception $e) {
            return response()->errorResponse('Failed to retrieve likes', 500, $e->getMessage());
        }
    }
}
