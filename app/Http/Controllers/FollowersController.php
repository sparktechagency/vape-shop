<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowingIdRequest;
use App\Http\Resources\Follower\FollowerResource;
use App\Models\User;
use App\Services\FollowerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class FollowersController extends Controller
{
    protected $followerService;

    // Cache configuration
    private const CACHE_TTL = 1800; // 30 minutes
    private const FOLLOWERS_CACHE_PREFIX = 'user_followers';
    private const FOLLOWING_CACHE_PREFIX = 'user_following';

    public function __construct(FollowerService $followerService)
    {
        $this->followerService = $followerService;
    }

    /**
     * Generate cache key for followers/following
     */
    private function generateCacheKey(string $prefix, array $params = []): string
    {
        $key = $prefix;
        if (!empty($params)) {
            $key .= '_' . md5(json_encode($params));
        }
        return $key;
    }

    //get all followers of a user
    /**
     * Get all followers of a user.
     * @param int $userId
     * @return array
     */

    public function getAllFollowers(Request $request)
    {
        try {
            $userId = $request->user_id ?? Auth::id();
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 15);

            // Generate cache key based on user ID and pagination
            $cacheKey = $this->generateCacheKey(self::FOLLOWERS_CACHE_PREFIX, [
                'user_id' => $userId,
                'page' => $page,
                'per_page' => $perPage
            ]);

            // Use cache for followers with tags
            $followers = Cache::tags(['users', 'followers'])->remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
                return $this->followerService->getAllFollowers($userId);
            });

            if ($followers->isEmpty()) {
                return response()->error('No followers found', 404);
            }
            $followers = FollowerResource::collection($followers);
            return response()->success(
                $followers,
                'Followers fetched successfully',
                200
            );
        } catch (\Exception $e) {
            return response()->error('Error fetching followers', 500, $e->getMessage());
        }
    }

    //get all users that a user is following
    /**
     * Get all users that a user is following.
     * @param int $userId
     * @return array
     */

    public function getAllFollowing(Request $request)
    {
        try {
            $userId = $request->user_id ?? Auth::id();
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 15);

            // Generate cache key based on user ID and pagination
            $cacheKey = $this->generateCacheKey(self::FOLLOWING_CACHE_PREFIX, [
                'user_id' => $userId,
                'page' => $page,
                'per_page' => $perPage
            ]);

            // Use cache for following with tags
            $following = Cache::tags(['users', 'followers'])->remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
                return $this->followerService->getAllFollowing($userId);
            });

            if ($following->isEmpty()) {
                return response()->error('No following found', 404);
            }
            $following = FollowerResource::collection($following);
            return response()->success(
                $following,
                'Following fetched successfully',
                200
            );
        } catch (\Exception $e) {
            return response()->error('Error fetching following', 500, $e->getMessage());
        }
    }



    //follow user
    /**
     * Follow a user.
     * @param Request $request
     * @return Follower
     */
    public function follow(FollowingIdRequest $request)
    {
        try {
            $follower = Auth::user();
            $following = User::findOrFail($request->following_id);
            $this->followerService->follow($follower, $following);
            return response()->success(null,'Followed successfully');
        } catch (\Exception $e) {
            return response()->error('Error following user', 500, $e->getMessage());
        }
    }

    //unfollow user
    /**
     * Unfollow a user.
     * @param Request $request
     * @return
     */
    public function unfollow(FollowingIdRequest $request)
    {
        try {
            $follower = Auth::user();
            $following = User::findOrFail($request->following_id);
            $this->followerService->unfollow($follower, $following);
            return response()->success(null,'Unfollowed successfully');
        } catch (\Exception $e) {
            return response()->error('Error unfollowing user', 500, $e->getMessage());
        }
    }

}
