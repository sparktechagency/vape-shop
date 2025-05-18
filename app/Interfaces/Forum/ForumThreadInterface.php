<?php

namespace App\Interfaces\Forum;

interface ForumThreadInterface
{

    public function getAllThreads($groupId): array;

    public function getThreadById($threadId): array;

    public function createThread($data) : array;

    public function updateThread($data, $threadId) : array | bool;

    public function deleteThread($threadId): bool;

    public function getThreadComments($threadId);
}
