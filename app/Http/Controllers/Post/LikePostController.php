<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Services\Post\PostLikeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LikePostController extends Controller
{
    protected $postLikeService;
    public function __construct(PostLikeService $postLikeService)
    {
        $this->postLikeService = $postLikeService;
    }
    //tigger like post
    public function tiggerLike(Request $request, $postId)
    {
        try {
            $userId = Auth::id();
            $type = $request->input('type', 'like');
            $tiggerLike = $this->postLikeService->tiggerLike((int)$postId, $userId, $type);
            if ($tiggerLike['is_active']) {
                return response()->success($tiggerLike, "Post " . $type . "d successfully");
            } else {
                return response()->success($tiggerLike, "Post " . $type . " removed successfully");
            }
        } catch (\Exception $e) {
            return response()->error('Failed to like post', 500, $e->getMessage());
        }
    }

    //get likes count
    public function getLikesCount(Request $request, $postId)
    {
        try {
            $type = $request->input('type', 'like');
            $likesCount = $this->postLikeService->getLikesCount((int)$postId, $type);
            return response()->success($likesCount, "{$type}s count retrieved successfully");
        } catch (\Exception $e) {
            return response()->error("Failed to retrieve {$type}s count", 500, $e->getMessage());
        }
    }
    //get likes by post id
    public function getLikesByPostId(Request $request, $postId)
    {
        try {
            $type = $request->input('type', 'like');
            $likes = $this->postLikeService->getLikesByPostId((int)$postId, $type);
            if (empty($likes)) {
                return response()->error("No ". $type."s found for this post", 404);
            }
            return response()->success($likes, Str::ucfirst($type) . "s retrieved successfully");
        } catch (\Exception $e) {
            return response()->error("Failed to retrieve " . $type. "s", 500, $e->getMessage());
        }
    }
}
