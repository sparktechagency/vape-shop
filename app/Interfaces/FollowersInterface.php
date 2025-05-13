<?php
namespace App\Interfaces;
use App\Models\Follower;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface FollowersInterface
{
    /**
     * Get all followers of a user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getAllFollowers(int $userId): Collection;

    /**
     * Get all users that a user is following.
     *
     * @param int $userId
     * @return Collection
     */
    public function getAllFollowing(int $userId): Collection;

    //follow user
    public function follow(User $follower, User $following);

    //unfollow user
    public function unfollow(User $follower, User $following);
}
