<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class FeedController extends Controller
{
    // Cache configuration
    private const CACHE_TTL = 900; // 15 minutes (feed data changes frequently)
    private const FEED_CACHE_PREFIX = 'user_feed';

    /**
     * Generate cache key for feed
     */
    private function generateCacheKey(string $prefix, array $params = []): string
    {
        $key = $prefix;
        if (!empty($params)) {
            $key .= '_' . md5(json_encode($params));
        }
        return $key;
    }

    public function feed(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $userID = $request->input('user_id', Auth::id());
        $page = $request->input('page', 1);
        
        $user = User::find($userID);

        if (!$user) {
            return response()->error('User not found', 404);
        }

        $followingsIds = $user->following()->pluck('users.id');

        if($followingsIds->isEmpty()) {
            return response()->error(
                'You are not following anyone yet.',
                404
            );
        }

        // Generate cache key based on user ID, page, and per_page
        $cacheKey = $this->generateCacheKey(self::FEED_CACHE_PREFIX, [
            'user_id' => $userID,
            'page' => $page,
            'per_page' => $perPage,
            'following_ids' => $followingsIds->toArray()
        ]);

        // Use cache for user feed with tags
        $post = Cache::tags(['posts', 'feed', 'users'])->remember($cacheKey, self::CACHE_TTL, function () use ($followingsIds, $perPage) {
            return Post::whereIn('user_id', $followingsIds)
                ->where('content_type', 'post')
                ->with(['user:id,first_name,last_name,avatar,role', 'comments' => function ($query) {
                    $query->select('id', 'post_id', 'user_id', 'comment', 'created_at')
                        ->with(['user:id,first_name,last_name,avatar,role','replies']);
                }])
                ->withCount(['likes', 'comments'])
                ->latest()
                ->paginate($perPage);
        });

        if ($post->isEmpty()) {
            return response()->error(
                'No posts found in your feed.',
                404
            );
        }
        $post->getCollection()->makeVisible(['user']);
        return response()->success(
            $post,
            'Feed retrieved successfully.'
        );
    }
}
