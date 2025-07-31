<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowingIdRequest;
use App\Http\Resources\Follower\FollowerResource;
use App\Models\User;
use App\Services\FollowerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowersController extends Controller
{
    protected $followerService;
    public function __construct(FollowerService $followerService)
    {
        $this->followerService = $followerService;
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
            $followers = $this->followerService->getAllFollowers($userId);
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
            $following = $this->followerService->getAllFollowing($userId);
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
