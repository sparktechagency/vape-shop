<?php

namespace App\Services;

use App\Interfaces\FollowersInterface;
use App\Models\User;
use App\Notifications\FollowerNotification;
use Illuminate\Database\Eloquent\Collection;

class FollowerService
{
    protected $followerRepository;

    public function __construct(FollowersInterface $followerRepository)
    {
        $this->followerRepository = $followerRepository;
    }


    //get all followers of a user
    /**
     * Get all followers of a user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getAllFollowers(int $userId): Collection
    {
        return $this->followerRepository->getAllFollowers($userId);

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
        return $this->followerRepository->getAllFollowing($userId);
    }



    //follow user
    /**
     * Follow a user.
     * @param User $follower
     * @param User $following
     * @return Follower
     */
    public function follow(User $follower, User $following)
    {
        $followerData = $this->followerRepository->follow($follower, $following);

        //send notification to the user
        $following->notify( new FollowerNotification($follower));
        
        return $followerData;
    }

    //unfollow user
    /**
     * Unfollow a user.
     * @param User $follower
     * @param User $following
     */
    public function unfollow(User $follower, User $following)
    {
        return $this->followerRepository->unfollow($follower, $following);
    }

}
