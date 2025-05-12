<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowingIdRequest;
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
            return response()->successResponse('Followed successfully');
        } catch (\Exception $e) {
            return response()->errorResponse('Error following user', 500, $e->getMessage());
        }
    }

    //unfollow user
    /**
     * Unfollow a user.
     * @param Request $request
     * @return
     */
    public function unfollow(FollowingIdRequest $request){
    try {
            $follower = Auth::user();
            $following = User::findOrFail($request->following_id);
            $this->followerService->unfollow($follower, $following);
            return response()->successResponse('Unfollowed successfully');
        } catch (\Exception $e) {
            return response()->errorResponse('Error unfollowing user', 500, $e->getMessage());
        }
    }
}
