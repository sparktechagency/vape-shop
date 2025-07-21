<?php

namespace App\Repositories\Products;

use App\Enums\UserRole\Role;
use App\Http\Resources\ProductDeteails;
use App\Interfaces\Products\HomeProductInterface;
use App\Models\ManageProduct;
use App\Models\StoreProduct;
use App\Models\WholesalerProduct;

class HomeProductRepository implements HomeProductInterface
{
    // Implement the methods defined in the interface
    public function getAllProducts(int $role): array
    {
        $perPage = request()->get('per_page', 10); // Get the number of items per page, default to 10
        switch ($role) {
            case Role::BRAND->value:
               $products = ManageProduct::with('category', 'user:id,first_name,last_name,avatar' )
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

                $products->getCollection()->makeVisible(['user']);
                return $products->toArray();
            case Role::STORE->value:
                $products = StoreProduct::with('category', 'user:id,first_name,last_name,avatar')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
                $products->getCollection()->makeVisible(['user']);
                return $products->toArray();
            case Role::WHOLESALER->value:
                $products = WholesalerProduct::with('category', 'user:id,first_name,last_name,avatar')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
                $products->getCollection()->makeVisible(['user']);
                return $products->toArray();
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
                $product = WholesalerProduct::with('category', 'user')
                        ->findOrFail($id)
                        ->makeVisible(['user', 'category']);
                $relatedProducts = WholesalerProduct::where('id', '!=', $id)
                    ->inRandomOrder()
                    ->take(4)
                    ->get();
                $product->relatedProducts = $relatedProducts;
                $product = new ProductDeteails($product);
                return $product;
            default:
                //error handling or default case
                throw new \Exception("Invalid role provided.");
        }
        return [];
    }
}
