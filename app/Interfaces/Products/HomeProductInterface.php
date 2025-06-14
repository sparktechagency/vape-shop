<?php

namespace App\Interfaces\Products;

interface HomeProductInterface
{
   public function getAllProducts(int $role): array;
   public function getProductById(int $id, int $role): array;
}
