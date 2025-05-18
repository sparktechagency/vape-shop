<?php

namespace App\Services\Forum;

use App\Interfaces\Forum\ForumGroupInterface;

class ForumGroupService
{
    protected $repository;

    public function __construct(ForumGroupInterface $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Get all forum groups.
     *
     * @return array
     */
    public function getAllGroups(): array
    {
        return $this->repository->getAllGroups();
    }

    /**
     * Get a forum group by its ID.
     *
     * @param int $groupId
     * @return array
     */
    public function getGroupById(int $groupId): array
    {
        return $this->repository->getGroupById($groupId);
    }

    /**
     * Create a new forum group.
     *
     * @param array $data
     * @return array
     */
    public function createGroup(array $data): array
    {
        return $this->repository->createGroup($data);
    }

    /**
     * Update an existing forum group.
     *
     * @param int $groupId
     * @param array $data
     * @return bool
     */
    public function updateGroup(int $groupId, array $data): bool
    {
        return $this->repository->updateGroup($groupId, $data);
    }

    /**
     * Delete a forum group by its ID.
     *
     * @param int $groupId
     * @return bool
     */
    public function deleteGroup(int $groupId): bool
    {
        return $this->repository->deleteGroup($groupId);
    }
}
