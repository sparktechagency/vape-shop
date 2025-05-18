<?php

namespace App\Repositories;

use App\Interfaces\CommentsInterface;
use Illuminate\Support\Facades\Auth;

class CommentsRepository implements CommentsInterface
{
    protected $model;
    public function __construct($model)
    {
        $this->model = $model;
    }

    //get all comments in a post or forum thread
    public function getCommentsByPostId(int $postId, string $modelType): array
    {
        $perPage = request()->query('per_page', 10);
        if ($modelType === 'post') {
            $query = $this->model->where('post_id', $postId);
        } elseif ($modelType === 'forum') {
            $query = $this->model->where('thread_id', $postId);
        } else {
            return []; // Return an empty array if the modelType is invalid
        }

        return $query->whereNull('parent_id')
                    ->with(['user:id,first_name,last_name,role,avatar', 'replies.user:id,first_name,last_name,role,avatar'])
                    ->withCount(['replies' => function ($q) {
                        $q->where('parent_id', '!=', null);
                    }])
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage, ['*'], 'page', request()->query('page', 1))
                    ->toArray();
    }

    //create comment
    public function createComment(array $data)
    {
        return $this->model->create($data);
    }
    public function deleteComment(int $commentId): bool
    {
        $userId = Auth::id();
        $comment = $this->model->where('user_id', $userId)
                                ->find($commentId);

        if (!$comment) {
            return false;
        }

        return $this->model->destroy($commentId);
    }
}
