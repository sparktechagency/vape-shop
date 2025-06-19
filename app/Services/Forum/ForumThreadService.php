<?php

namespace App\Services\Forum;

use App\Interfaces\Forum\ForumThreadInterface;

class ForumThreadService
{
    protected $repository;

    public function __construct(ForumThreadInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllThreads($groupId)
    {
        return $this->repository->getAllThreads($groupId);
    }
    public function getThreadById($threadId)
    {
        return $this->repository->getThreadById($threadId);
    }
    public function createThread($data)
    {
        return $this->repository->createThread($data);
    }

    public function updateThread($data, $threadId)
    {
        return $this->repository->updateThread($data, $threadId);
    }

    public function deleteThread($threadId)
    {
        return $this->repository->deleteThread($threadId);
    }

    public function getThreadComments($threadId)
    {
        return $this->repository->getThreadComments($threadId);
    }

    public function incrementViewCount(int $threadId): void
    {
        $this->repository->incrementViewCount( $threadId);
    }
}
