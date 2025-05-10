<?php
namespace App\Interfaces\Products;

interface ManageProductsInterface
{
    public function getAllProducts(): array;
    public function getProductById(int $id): array;
    public function storeProduct(array $data): array;
    public function updateProduct(int $id, array $data): array;
    public function deleteProduct(int $id): bool;
}
