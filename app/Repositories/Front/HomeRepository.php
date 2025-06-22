<?php

namespace App\Repositories\Front;

use App\Enums\UserRole\Role;
use App\Interfaces\Front\HomeInterface;
use App\Models\ManageProduct;
use App\Models\StoreProduct;
use App\Models\User;
use Illuminate\Contracts\Cache\Store;

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
        $user = User::where('id', $userId)->first();
        if (!$user) return [];
        $is_most_hearted = request('is_most_hearted', false);
        switch ($type) {
            case 'store':
                if ($user->role !== Role::STORE->value) return [];
                $query = StoreProduct::where('user_id', $userId)->withCount('hearts');

                if ($is_most_hearted) {
                    $query->orderByDesc('hearts_count');
                } else {
                    $query->orderByDesc('created_at');
                }

                return [
                    'user' => $user,
                    'products' => $query->paginate($perPage),
                ];

            case 'brand':
                if ($user->role !== Role::BRAND->value) return [];
                $query = ManageProduct::where('user_id', $userId)->withCount('hearts');
                if ($is_most_hearted) {
                    $query->orderByDesc('hearts_count');
                } else {
                    $query->orderByDesc('created_at');
                }
                return [
                    'user' => $user,
                    'products' => $query->paginate($perPage),
                ];

            case 'wholesaler':
                // Future logic can go here
                return [];

            default:
                return [];
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
