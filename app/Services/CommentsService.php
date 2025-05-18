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

    // Define service methods that use the repository
}
