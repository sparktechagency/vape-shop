<?php

namespace App\Policies;

use App\Models\ForumGroup;
use App\Models\User;

class ForumGroupPolicy
{
    // only group owner can manage the group
    public function manage(User $user, ForumGroup $group): bool
    {
        return $user->id === $group->user_id;
    }

    // only group owner or approved members can post the group
    public function post(User $user, ForumGroup $group): bool
    {
        if ($group->type === 'public') {
            return true;
        }
        return $user->id === $group->user_id || $group->approvedMembers()->where('user_id', $user->id)->exists();
    }

    // only approved members (but not the owner) can leave the group
    public function leave(User $user, ForumGroup $group): bool
    {

        if ($user->id === $group->user_id) {
            return false;
        }

        return $group->approvedMembers()->where('user_id', $user->id)->exists();
    }

    public function viewMembers(User $user, ForumGroup $group): bool
    {
        // for public group, anyone can view members
        if ($group->type === 'public') {
            return true;
        }

        // for private group, only owner or approved members can view members
        return $user->id === $group->user_id || $group->approvedMembers()->where('user_id', $user->id)->exists();
    }
}
