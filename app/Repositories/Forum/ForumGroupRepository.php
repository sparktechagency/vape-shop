<?php

namespace App\Repositories\Forum;

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
    public function getAllGroups(): array
    {
        $userId = Auth::id();
        $is_trending = request()->get('is_trending', false);
        $perPage = request()->get('per_page', 10);
        $query = $this->model->withCount('threads');
        if ($is_trending) {
            $query->orderBy('threads_count', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
        if ($userId) {
            $query->where('user_id', $userId);
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
        $query = $this->model->where('id', $groupId);
        if ($userId) {
            $query->where('user_id', $userId);
        }
        $group = $query->first();
        return $group ? $group->toArray() : [];
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
        $userId = Auth::id();
        $group = $this->model->where('user_id', $userId)->find($groupId);
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
        $group = $this->model->where('user_id', $userId)
                             ->find($groupId);
        if ($group) {
            return $group->delete();
        }
        return false;
    }

}
