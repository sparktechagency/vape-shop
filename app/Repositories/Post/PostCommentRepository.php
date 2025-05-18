<?php

namespace App\Repositories\Post;

use App\Interfaces\Post\PostCommentInterface;
use App\Models\PostComment;
use Illuminate\Support\Facades\Auth;

class PostCommentRepository implements PostCommentInterface
{
    protected $model;
    public function __construct(PostComment $model)
    {
        $this->model = $model;
    }
    public function getCommentsByPostId(int $postId): array
    {
        // Implementation to get comments by post ID
        $perPage = request()->query('per_page', 10);
        return $this->model->where('post_id', $postId)
                    ->whereNull('parent_id')
                    ->with(['user:id,first_name,last_name,role,avatar', 'replies.user:id,first_name,last_name,role,avatar'])
                    ->withCount(['replies' => function ($query) {
                        $query->where('parent_id', '!=', null);
                    }])
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage, ['*'], 'page', request()->query('page', 1))
                    ->toArray();
    }

    public function createComment(array $data): PostComment
    {
        // Implementation to add a comment
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
