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

    //search
    public function search(?string $searchTerm, ?string $type = 'products', int $perPage = 10, $regionId = null)

    {
        $searchTerm = trim($searchTerm);
        if (empty($searchTerm)) {
            return [];
        }

        $searchTerm = match ($type) {
            'store' => $this->getAllStore($perPage, $searchTerm, $regionId),
            'brand' => $this->getAllBrand($perPage, $searchTerm, $regionId),
            'wholesaler' => $this->getAllWholesaler($perPage, $searchTerm, $regionId),
            'products' => $this->searchProducts($searchTerm, $perPage),
            'accounts' => $this->getAllAccounts($perPage, $searchTerm, $regionId),
            default => $this->searchProducts($searchTerm, $perPage),
        };
        return $searchTerm;
    }


    //get all store, brand or wholesaler
    public function getAllStoreBrandWholesaler($type, $perPage)
    {

        return match ($type) {
            'store' => $this->getAllStore($perPage),
            'brand' => $this->getAllBrand($perPage),
            'wholesaler' => $this->getAllWholesaler($perPage),
            default => [],
        };
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
    private function getAllStore($perPage, $searchTerm = null, $regionId = null)
    {
        $query = User::where('role', Role::STORE); // অথবা Store::query()

        $query->when($searchTerm, function ($query, $searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%');
            });
        });
        $query->when($regionId, function ($query, $regionId) {
            $query->whereHas('address', function ($q) use ($regionId) {
                $q->where('region_id', $regionId);
            });
        });


        $stores = $query->with('address')->paginate($perPage);

        return $stores;
    }
    //get all brand
    private function getAllBrand($perPage, $searchTerm = null, $regionId = null)
    {
        $query = User::where('role', Role::BRAND);

        $query->when($searchTerm, function ($query, $searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%');
            });
        });
        $query->when($regionId, function ($query, $regionId) {
            $query->whereHas('address', function ($q) use ($regionId) {
                $q->where('region_id', $regionId);
            });
        });

        $brand = $query->paginate($perPage);
        return $brand;
    }
    //get all wholesaler
    private function getAllWholesaler($perPage, $searchTerm = null, $regionId = null)
    {
        $query = User::where('role', Role::WHOLESALER);

        $query->when($searchTerm, function ($query, $searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%');
            });
        });
        $query->when($regionId, function ($query, $regionId) {
            $query->whereHas('address', function ($q) use ($regionId) {
                $q->where('region_id', $regionId);
            });
        });

        $wholesaler = $query->paginate($perPage);
        return $wholesaler;
    }

    //search brands products by name
    private function searchProducts($searchTerm, $perPage)
    {
        return ManageProduct::where('product_name', 'like', '%' . $searchTerm . '%')
            // ->orWhere('description', 'like', '%' . $searchTerm . '%')
            ->paginate($perPage);
    }

    //all accounts except admin
    private function getAllAccounts($perPage, $searchTerm = null, $regionId = null)
    {
        $query = User::where('role', '!=', Role::ADMIN->value);
        $query->when($searchTerm, function ($query, $searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('phone', 'like', '%' . $searchTerm . '%');
            });
        });
        $query->when($regionId, function ($query, $regionId) {
            $query->whereHas('address', function ($q) use ($regionId) {
                $q->where('region_id', $regionId);
            });
        });
        $accounts = $query->with('address')->orderBy('created_at', 'desc');
        return $accounts->paginate($perPage);
    }
}
