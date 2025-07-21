<?php

namespace App\Repositories\Products;

use App\Enums\UserRole\Role;
use App\Interfaces\Products\HeartedProductsInterface;
use App\Models\Heart;
use App\Models\StoreProduct;
use App\Models\User;
use App\Models\WholesalerProduct;

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
                           ->with(['manageProduct:id,user_id,product_name,product_image,product_price', 'storeProduct:id,user_id,product_name,product_image,product_price', 'wholesalerProduct:id,user_id,product_name,product_image,product_price'])
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
            case Role::WHOLESALER->value:
                $heart = $this->model->where('wholesaler_product_id', $productId)
                                     ->where('user_id', $userId)
                                     ->whereHas('wholesalerProduct', function ($query) use ($productId) {
                                         $query->where('id', $productId);
                                     })->first();
                break;
            default:
                throw new \InvalidArgumentException('Invalid role provided.');
        }
        // If heart exists, delete it; otherwise, create a new heart
        // dd($this->getRegionId($productId, $role));
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
                $data = $this->getProductAndRegionId($productId, $role);
                $model = $this->model->create([
                    'store_product_id' => $productId,
                    'manage_product_id' => $data['manage_product_id'] ?? null, // Optional, if manage_product_id is needed
                    'user_id' => $userId,
                    'region_id' => $data['region_id'] ?? null, // Assuming region_id is needed for store products
                ]);
                break;
            case Role::WHOLESALER->value:
                $data = $this->getProductAndRegionId($productId, $role);
                $model = $this->model->create([
                    'wholesaler_product_id' => $productId,
                    'manage_product_id' => $data['manage_product_id'] ?? null, // Optional, if manage_product_id is needed
                    'region_id' => $data['region_id'] ?? null, // Assuming region_id
                    'user_id' => $userId,
                ]);
                break;
            default:
                throw new \InvalidArgumentException('Invalid role provided.');
        }


        return (bool) $model;
    }

    private function getProductAndRegionId($productId, $role)
    {
        $data = [];
        if ($role === Role::STORE->value) {
            $product = StoreProduct::find($productId);
        } elseif ($role === Role::WHOLESALER->value) {
            $product = WholesalerProduct::find($productId);
        }else {
            $product = null;
        }


            if($product){
                if ($product->user_id && User::find($product->user_id)->address) {
                    $data['region_id'] = User::find($product->user_id)->address->region_id;
                }
                // dd($product->manageProducts);
                if($product->product_id && $product->manageProducts) {
                    $data['manage_product_id'] = $product->product_id;
                } else {
                    $data['manage_product_id'] = null;
                }

                return $data;
            }

        return null;
    }

}
