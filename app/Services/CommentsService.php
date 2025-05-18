<?php

namespace App\Services;

use App\Interfaces\CommentsInterface;

class CommentsService
{
    protected $repository;

    public function __construct(CommentsInterface $repository)
    {
        $this->repository = $repository;
    }

    //get all comments in a post
    public function getAllComments($postId, $modelType)
    {
        return $this->repository->getCommentsByPostId($postId, $modelType);
    }

    //add comment
    public function createComment(array $data)
    {
        return $this->repository->createComment($data);
    }

    //delete comment
    public function deleteComment($commentId)
    {
        return $this->repository->deleteComment($commentId);
    }
}
