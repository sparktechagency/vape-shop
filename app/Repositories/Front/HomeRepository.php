<?php

namespace App\Repositories\Front;

use App\Enums\UserRole\Role;
use App\Interfaces\Front\HomeInterface;
use App\Models\User;

class HomeRepository implements HomeInterface
{
    public function getAllStoreBrandWholesaler($type, $perPage)
    {

        switch ($type) {
            case 'store':
                return $this->getAllStore($perPage);
            case 'brand':
                return $this->getAllBrand($perPage);
            case 'wholesaler':
                return $this->getAllWholesaler($perPage);
            default:
                return [];
        }
    }


    //get products by store or brand or wholesaler id
    public function getProductsByRoleId($type, $userId, $perPage)
    {
        $products = User::where('id', $userId)
            ->where('role', Role::BRAND)
            ->with(['manageProducts' => function ($query) use ($perPage) {
                $query->paginate($perPage);
            }]);
        return $products->first();
        switch ($type) {
            case 'store':
                $products = $products->where('role', Role::STORE);
                break;
            case 'brand':
                $products = $products->where('role', Role::BRAND);
                break;
            case 'wholesaler':
                $products = $products->where('role', Role::WHOLESALER);
                break;
            default:
                return [];

                $products = $products->with(['manageProducts' => function ($query) use ($perPage) {
                    $query->paginate($perPage);
                }])
                    ->first();
                return $products ;
        }
    }
    //get all store
    private function getAllStore($perPage)
    {
        $store = User::where('role', Role::STORE)
            ->paginate($perPage);
        return $store;
    }
    //get all brand
    private function getAllBrand($perPage)
    {
        $brand = User::where('role', Role::BRAND)
            ->paginate($perPage);
        return $brand;
    }
    //get all wholesaler
    private function getAllWholesaler($perPage)
    {
        $wholesaler = User::where('role', Role::WHOLESALER)
            ->paginate($perPage);
        return $wholesaler;
    }
}
