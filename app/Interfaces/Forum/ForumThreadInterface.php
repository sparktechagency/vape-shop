<?php

namespace App\Interfaces\Forum;

use App\Models\ForumThread;

interface ForumThreadInterface
{

    public function getAllThreads($groupId): array;

    public function getThreadById($threadId): ?ForumThread;

    public function createThread($data) : array;

    public function updateThread($data, $threadId) : array | bool;

    public function deleteThread($threadId): bool;

    public function getThreadComments($threadId);

    public function incrementViewCount(int $threadId): void;
}
