<?php

namespace App\Policies;

use App\Models\ForumGroup;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ForumThreadPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, ForumGroup $group): bool
    {
       //if group is public, then anyone can view threads.
        if ($group->type === 'public') {
            return true;
        }

        // If group is private, then only owner or approved members can view threads.
        return $user->id === $group->user_id || $group->approvedMembers()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can view the model.
     * this method checks if the user can view a specific thread.
     */
    public function view(User $user, ForumThread $thread): bool
    {
        //
        $group = $thread->group;
        return $this->viewAny($user, $group);
    }


    public function create(User $user, ForumGroup $group): bool
    {
        // if group is public, then anyone can create threads.
        if ($group->type === 'public') {
            return true;
        }

        // if group is private, then only owner or approved members can create threads.
        return $user->id === $group->user_id || $group->approvedMembers()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ForumThread $thread): bool
    {
        // only the thread owner can update the thread.
        return $user->id === $thread->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ForumThread $thread): bool
    {
        // only the thread owner or group owner can delete the thread.
        return $user->id === $thread->user_id || $user->id === $thread->group->user_id;
    }
}
