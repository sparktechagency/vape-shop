<?php

namespace App\Services;

use App\Interfaces\FollowersInterface;
use App\Models\User;

class FollowerService
{
    protected $followerRepository;

    public function __construct(FollowersInterface $followerRepository)
    {
        $this->followerRepository = $followerRepository;
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
        return $this->followerRepository->follow($follower, $following);
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

    /**
     * Get all followers of a user.
     *
     * @param int $userId
     * @return array
     */
    public function getAllFollowers(int $userId): array
    {
        return $this->followerRepository->getAllFollowers($userId);
    }
}
