<?php

namespace App\Repositories\Products;

use App\Enums\UserRole\Role;
use App\Http\Resources\ProductDeteails;
use App\Interfaces\Products\HomeProductInterface;
use App\Models\ManageProduct;
use App\Models\StoreProduct;

class HomeProductRepository implements HomeProductInterface
{
    // Implement the methods defined in the interface
    public function getAllProducts(int $role): array
    {
        $perPage = request()->get('per_page', 10); // Get the number of items per page, default to 10
        switch ($role) {
            case Role::BRAND->value:
               return ManageProduct::with('category' )
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->toArray();
            case Role::STORE->value:
               return StoreProduct::with('category')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->toArray();
            case Role::WHOLESALER->value:
                // Implement logic for wholesaler if needed
                return [];
            default:
                //error handling or default case
                throw new \Exception("Invalid role provided.");
        }
        return [];
    }
    public function getProductById(int $id, int $role)
    {
        switch ($role) {
            case Role::BRAND->value:
                $product = ManageProduct::with(['category', 'user'])->findOrFail($id);
                // related product filter by top rating
                $product->makeVisible(['user', 'category']);
                $relatedProducts = ManageProduct::where('id', '!=', $id)
                    ->inRandomOrder()
                    ->take(4)
                    ->get();
                $product->relatedProducts = $relatedProducts;
                $product = new ProductDeteails($product);
                return $product;
            case Role::STORE->value:
                $product = StoreProduct::with('category','user')
                        ->findOrFail($id)
                        ->makeVisible(['user', 'category']);
                $relatedProducts = StoreProduct::where('id', '!=', $id)
                    ->inRandomOrder()
                    ->take(4)
                    ->get();
                $product->relatedProducts = $relatedProducts;
                $product = new ProductDeteails($product);
                return $product;
            case Role::WHOLESALER->value:
                // Implement logic for wholesaler if needed
                return [];
            default:
                //error handling or default case
                throw new \Exception("Invalid role provided.");
        }
        return [];
    }
}
