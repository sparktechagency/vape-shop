<?php

namespace App\Repositories\Products;

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
                           ->with('product:id,user_id,product_name,product_image,product_price')
                           ->orderBy('created_at', 'desc')
                           ->paginate($perPage)
                           ->toArray();
    }
    //toggle hearted product
    public function toggleHeartedProduct(int $productId, int $userId): bool
    {
        $heart = $this->model->where('product_id', $productId)
                             ->where('user_id', $userId)
                             ->first();
        if ($heart) {
             $heart->delete();
             return false;
        }
        $model = $this->model->create([
            'product_id' => $productId,
            'user_id' => $userId
        ]);
        return (bool) $model;
    }

}
