<?php

namespace App\Services\Post;

use App\Interfaces\Post\PostCommentInterface;

class PostCommentService
{
    protected $repository;

    public function __construct(PostCommentInterface $repository)
    {
        $this->repository = $repository;
    }

    //get all comments in a post
    public function getAllComments($postId)
    {
        return $this->repository->getCommentsByPostId($postId);
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
