<?php

namespace App\Repositories\Forum;

use App\Enums\UserRole\Role;
use App\Interfaces\Forum\ForumGroupInterface;
use App\Models\ForumGroup;
use Illuminate\Support\Facades\Auth;

class ForumGroupRepository implements ForumGroupInterface
{
    protected $model;

    public function __construct(ForumGroup $model)
    {
        $this->model = $model;
    }
    //get all groups
    /**
     * @return array
     */
    // public function getAllGroups(): array
    // {
    //     $userId = Auth::id();
    //     $isTrending = request()->boolean('is_trending');
    //     $isGlobal = request()->boolean('show_front');
    //     $perPage = (int) request()->get('per_page', 10);
    //     $user_id = request()->get('user_id');
    //     $isLatest = request()->boolean('is_latest', false);

    //     $query = $this->model->withCount('threads');

    //     if ($isTrending) {
    //         $query->orderByDesc('threads_count');
    //     } elseif ($isLatest) {
    //         $query->orderByDesc('created_at');
    //     }
    //     else {
    //         $query->inRandomOrder();
    //     }


    //     if (!$isGlobal && $userId) {
    //         $query->where('user_id', $userId);
    //     }

    //     if ($user_id) {
    //         $query->where('user_id', $user_id);
    //     }

    //     return $query->paginate($perPage)->toArray();
    // }

    public function getAllGroups(): array
    {
        $userId = Auth::id();
        $isTrending = request()->boolean('is_trending');
        $isGlobal = request()->boolean('show_front');
        $perPage = (int) request()->get('per_page', 10);
        $user_id_filter = request()->get('user_id');
        $isLatest = request()->boolean('is_latest', false);

        $query = $this->model->withCount('threads');


        $query->where(function ($q) use ($userId) {

            $q->where('type', 'public');
            if ($userId) {
                $q->orWhere('user_id', $userId);
                $q->orWhereHas('approvedMembers', function ($memberQuery) use ($userId) {
                    $memberQuery->where('user_id', $userId);
                });
            }
        });
        if ($isTrending) {
            $query->orderByDesc('threads_count');
        } elseif ($isLatest) {
            $query->orderByDesc('created_at');
        } else {
            $query->inRandomOrder();
        }

        if (!$isGlobal && $userId) {
            $query->where('user_id', $userId);
        }

        if ($user_id_filter) {
            $query->where('user_id', $user_id_filter);
        }

        return $query->paginate($perPage)->toArray();
    }

    //get group by id
    /**
     * @param int $groupId
     * @return array
     */
    public function getGroupById(int $groupId): array
    {
        $userId = Auth::id();
        $group = $this->model->find($groupId);

        if (!$group) {
            return [];
        }
        if ($group->type === 'public') {
            return $group->toArray();
        }

        if ($group->type === 'private') {
            if ($userId && ($group->user_id === $userId || $group->approvedMembers()->where('user_id', $userId)->exists())) {
                return $group->toArray();
            }
        }
        return [];
    }

    //create group
    /**
     * @param array $data
     * @return array
     */
    public function createGroup(array $data): array
    {
        $userId = Auth::id();
        $data['user_id'] = $userId;
        return $this->model->create($data)->toArray();
    }

    //update group
    /**
     * @param int $groupId
     * @param array $data
     * @return bool
     */
    public function updateGroup(int $groupId, array $data): bool
    {
        $group = $this->model->find($groupId);

        if ($group) {
            return $group->update($data);
        }

        return false;
    }

    //delete group
    /**
     * @param int $groupId
     * @return bool
     */
    public function deleteGroup(int $groupId): bool
    {
        $userId = Auth::id();
        $group = $this->model->find($groupId);

        // Allow delete if user is admin or group owner
        if ($group) {
            if (Auth::user() && (Auth::user()->role === Role::ADMIN->value || $group->user_id == $userId)) {
            return $group->delete();
            }
        }
        return false;
    }
}
