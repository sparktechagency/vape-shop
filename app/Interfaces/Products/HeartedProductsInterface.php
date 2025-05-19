<?php

namespace App\Interfaces\Products;

interface HeartedProductsInterface
{
    public function getHeartedProductsByUserId(int $userId): array;

    public function toggleHeartedProduct(int $productId, int $userId): bool;
}
