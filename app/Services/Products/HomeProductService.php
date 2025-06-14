<?php

namespace App\Services\Products;

use App\Interfaces\Products\HomeProductInterface;

class HomeProductService
{
    protected $repository;

    public function __construct(HomeProductInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all products.
     *
     * @return array
     */
    public function getAllProducts(int $role): array
    {
        return $this->repository->getAllProducts($role);
    }
    /**
     * Get product by ID.
     *
     * @param int $id
     * @return array
     */
    public function getProductById(int $id, int $role): array
    {
        return $this->repository->getProductById($id, $role);
    }
}
