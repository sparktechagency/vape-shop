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
    public function toggleHeartedProduct(int $productId, int $userId, int $role): bool
    {
        switch ($role) {
            case Role::BRAND->value:
                $heart = $this->model->where('manage_product_id', $productId)->where('user_id', $userId)->first();
                break;
            case Role::STORE->value:
                $heart = $this->model->where('store_product_id', $productId)->where('user_id', $userId)->first();
                break;
            case Role::WHOLESALER->value:
                $heart = $this->model->where('wholesaler_product_id', $productId)->where('user_id', $userId)->first();
                break;
            default:
                throw new \InvalidArgumentException('Invalid role provided.');
        }

        if ($heart) {
            $heart->delete();
            return false;
        }


        $likingUser = User::with('address')->find($userId);
        // dd($likingUser);
        $userRegionId = $likingUser?->address?->region_id;
        // dd($userRegionId);
        switch ($role) {
            case Role::BRAND->value:
                $model = $this->model->create([
                    'manage_product_id' => $productId,
                    'user_id' => $userId,
                    'region_id' => $userRegionId,
                ]);
                break;
            case Role::STORE->value:
                $data = $this->getAssociatedProductData($productId, $role);
                $model = $this->model->create([
                    'store_product_id' => $productId,
                    'manage_product_id' => $data['manage_product_id'] ?? null,
                    'user_id' => $userId,
                    'region_id' => $userRegionId,
                ]);
                break;
            case Role::WHOLESALER->value:
                $data = $this->getAssociatedProductData($productId, $role);
                $model = $this->model->create([
                    'wholesaler_product_id' => $productId,
                    'manage_product_id' => $data['manage_product_id'] ?? null,
                    'user_id' => $userId,
                    'region_id' => $userRegionId,
                ]);
                break;
        }

        return (bool) ($model ?? false);
    }

    private function getAssociatedProductData($productId, $role)
    {
        $data = ['manage_product_id' => null];

        if ($role === Role::STORE->value) {
            $product = StoreProduct::find($productId);
        } elseif ($role === Role::WHOLESALER->value) {
            $product = WholesalerProduct::find($productId);
        } else {
            return $data;
        }

        if ($product && isset($product->product_id)) {
            $data['manage_product_id'] = $product->product_id;
        }

        return $data;
    }
}
