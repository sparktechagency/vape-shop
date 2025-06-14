<?php

namespace App\Services\Products;

use App\Interfaces\Products\HeartedProductsInterface;

class HeartedProductsService
{
    protected $repository;

    public function __construct(HeartedProductsInterface $repository)
    {
        $this->repository = $repository;
    }

    //get all hearted products by user id
    public function getHeartedProductsByUserId(int $userId): array
    {
        return $this->repository->getHeartedProductsByUserId($userId);
    }

    //toggle hearted product
    public function toggleHeartedProduct(int $productId, int $userId, int $role): bool
    {
        return $this->repository->toggleHeartedProduct($productId, $userId, $role);
    }
}
