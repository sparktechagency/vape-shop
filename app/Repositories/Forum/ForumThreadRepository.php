<?php

namespace App\Repositories\Forum;

use App\Interfaces\Forum\ForumThreadInterface;
use App\Models\ForumThread;
use Illuminate\Support\Facades\Auth;

class ForumThreadRepository implements ForumThreadInterface
{
    protected $model;
    public function __construct(ForumThread $model)
    {
        $this->model = $model;
    }

    /**
     * @param $groupId
     * @return array
     */
    public function getAllThreads($groupId): array
    {
        $perPage = request()->query('per_page', 10);
        return $this->model->where('group_id', $groupId)
                           ->with(['user:id,first_name,last_name,role', 'group:id,title'])
                           ->paginate($perPage)
                           ->toArray();
    }

    public function getThreadById($threadId): array
    {
        $thread = $this->model->with(['user:id,first_name,last_name,role', 'group:id,title', 'comments'])
                              ->find($threadId);
        return $thread ? $thread->toArray() : [];
    }

    public function createThread($data) : array
    {
        $userID = Auth::id();
        $data['user_id'] = $userID;
        $thread = $this->model->create($data);
        return $thread->toArray();
    }

    public function updateThread($data, $threadId): array | bool
    {
        $userId = Auth::id();
        $thread = $this->model->where('user_id',$userId)->find($threadId);
        if (!$thread) {
            return false;
        }
        $thread->update($data);
        return $thread->toArray();
    }
    public function deleteThread($threadId) : bool
    {
        $userId = Auth::id();
        $thread = $this->model->where('user_id',$userId)->find($threadId);
        if (!$thread) {
            return false;
        }
        return $thread->delete();
    }
    public function getThreadComments($threadId)
    {
        return $this->model->find($threadId)->comments()->get()->toArray();
    }
}
