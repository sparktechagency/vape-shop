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
}
