<?php

namespace App\Repositories\Forum;

use App\Interfaces\Forum\ForumThreadInterface;
use App\Models\ForumThread;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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
    // public function getAllThreads($groupId): array
    // {
    //     $perPage = request()->query('per_page', 10);
    //     $is_most_viewed = request()->get('is_most_viewed', false);
    //     // dd($is_most_viewed);
    //     $query = $this->model->where('group_id', $groupId)
    //                        ->with(['user:id,first_name,last_name,role', 'group:id,title']);
    //     if ($is_most_viewed) {
    //         $query->orderBy('views', 'desc');
    //     } else {
    //         $query->orderBy('created_at', 'desc');
    //     }
    //     return $query->paginate($perPage)->toArray();
    // }

    public function getAllThreads($groupId): array
    {
        $perPage = request()->query('per_page', 10);
        $is_most_viewed = request()->get('is_most_viewed', false);

        $query = $this->model->where('group_id', $groupId)
                            ->with(['user:id,first_name,last_name,role', 'group:id,title']);

        if ($is_most_viewed) {
            $query->orderBy('views', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage)->toArray();
    }



   public function getThreadById($threadId): ?ForumThread {

        return $this->model->with(['user:id,first_name,last_name,role', 'group:id,title', 'comments'])
                           ->find($threadId);
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
        $thread = $this->model->find($threadId);
        return $thread->delete();
    }
    public function getThreadComments($threadId)
    {
        return $this->model->find($threadId)->comments()->get()->toArray();
    }

     public function incrementViewCount(int $threadId): void
    {
        $ipAddress = request()->ip();
        // dd($ipAddress);
        $cacheKey = "viewed_thread_{$threadId}_{$ipAddress}";

        if (Cache::has($cacheKey)) {
            return;
        }

        $thread = $this->model->find($threadId);
        if ($thread) {
            $thread->increment('views');
        }

        Cache::put($cacheKey, true, now()->addMinutes(15));
    }
}
