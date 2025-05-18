<?php

namespace App\Interfaces\Forum;

interface ForumGroupInterface
{
    public function getAllGroups(): array;

    public function getGroupById(int $groupId): array;

    public function createGroup(array $data): array;

    public function updateGroup(int $groupId, array $data): bool;

    public function deleteGroup(int $groupId): bool;


}
