<?php

namespace App\Repositories\Products;

use App\Enums\UserRole\Role;
use App\Interfaces\Products\HeartedProductsInterface;
use App\Models\Heart;

class HeartedProductsRepository implements HeartedProductsInterface
{
    protected $model;

    public function __construct(Heart $model)
    {
        $this->model = $model;
    }
    //get all hearted products by user id
    public function getHeartedProductsByUserId(int $userId): array
    {
        $perPage = request()->query('per_page', 10);
        return $this->model->where('user_id', $userId)
                           ->with(['manageProduct:id,user_id,product_name,product_image,product_price', 'storeProduct:id,user_id,product_name,product_image,product_price'])
                           ->orderBy('created_at', 'desc')
                           ->paginate($perPage)
                           ->toArray();
    }
    //toggle hearted product
    public function toggleHeartedProduct(int $productId, int $userId, int $role) :bool
    {
        switch ($role) {
            case Role::BRAND->value:
                $heart = $this->model->where('manage_product_id', $productId)
                                     ->where('user_id', $userId)
                                     ->whereHas('manageProduct', function ($query) use ($productId) {
                                         $query->where('id', $productId);
                                     })->first();
                break;
            case Role::STORE->value:
                $heart = $this->model->where('store_product_id', $productId)
                                     ->where('user_id', $userId)
                                     ->whereHas('storeProduct', function ($query) use ($productId) {
                                         $query->where('id', $productId);
                                     })->first();
                break;
            default:
                throw new \InvalidArgumentException('Invalid role provided.');
        }
        // If heart exists, delete it; otherwise, create a new heart
        // return $heart;
        if ($heart) {
             $heart->delete();
             return false;
        }
        switch ($role) {
            case Role::BRAND->value:
                $model = $this->model->create([
                    'manage_product_id' => $productId,
                    'user_id' => $userId,
                ]);
                break;
            case Role::STORE->value:
                $model = $this->model->create([
                    'store_product_id' => $productId,
                    'user_id' => $userId,
                ]);
                break;
            default:
                throw new \InvalidArgumentException('Invalid role provided.');
        }
        return (bool) $model;
    }

}
