<?php
namespace App\Interfaces;
use App\Models\Follower;
use App\Models\User;

interface FollowersInterface
{
    /**
     * Get all followers of a user.
     *
     * @param int $userId
     * @return array
     */
    // public function getAllFollowers(int $userId): array;

    /**
     * Get all users that a user is following.
     *
     * @param int $userId
     * @return array
     */
    // public function getAllFollowing(int $userId): array;



    //follow user
    /**
     * Follow a user.
     * @param User $follower
     * @param User $following
     * @return Follower
     */
    public function follow(User $follower, User $following);

    //unfollow user
    /**
     * Unfollow a user.
     * @param User $follower
     * @param User $following
     * @return bool
     */
    public function unfollow(User $follower, User $following);
}
