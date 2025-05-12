<?php
namespace App\Repositories;
use App\Interfaces\FollowersInterface;
use App\Models\User;

class FollowersRepository implements FollowersInterface
{
    public function follow(User $follower, User $following)
    {
        return $follower->following()->attach($following->id);
    }

    public function unfollow(User $follower, User $following)
    {
        return $follower->following()->detach($following->id);
    }
}
