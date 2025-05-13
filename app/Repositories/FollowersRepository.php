<?php
namespace App\Repositories;
use App\Interfaces\FollowersInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class FollowersRepository implements FollowersInterface
{
    /**
     * Get all followers of a user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getAllFollowers(int $userId): Collection
    {
        $user = User::find($userId);
        return $user->followers()
                    ->select('users.id','first_name','last_name','email','role')
                    ->get();
    }

    //get all following of a user
    /**
     * Get all users that a user is following.
     *
     * @param int $userId
     * @return Collection
     */
    public function getAllFollowing(int $userId): Collection
    {
        $user = User::find($userId);
        return $user->following()
                    ->select('users.id','first_name','last_name','email','role')
                    ->get();
    }



    //follow user
    public function follow(User $follower, User $following)
    {
        return $follower->following()->attach($following->id);
    }

    //unfollow user
    public function unfollow(User $follower, User $following)
    {
        return $follower->following()->detach($following->id);
    }
}
