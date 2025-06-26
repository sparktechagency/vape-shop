<?php

namespace App\Services\Front;

use App\Interfaces\Front\HomeInterface;

class HomeService
{
    protected $repository;

    public function __construct(HomeInterface $repository)
    {
        $this->repository = $repository;
    }


    //search brand, shops, products, all account except admin,  and everything
    public function search(?string $searchTerm, ?string $type, int $perPage, int $regionId)
    {
        // Validate the type
        if (!in_array($type, ['products', 'store', 'brand', 'wholesaler', 'accounts'])) {
            throw new \InvalidArgumentException('Invalid type provided. Allowed types are: products, store, brand, wholesaler and accounts.');
        }
        // Validate the search term
        return $this->repository->search($searchTerm, $type, $perPage, $regionId);
    }

    public function getAllStoreBrandWholesaler($type, $perPage = 12)
    {
        // Validate the type
        if (!in_array($type, ['store', 'brand', 'wholesaler'])) {
            throw new \InvalidArgumentException('Invalid type provided. Allowed types are: store, brand, wholesaler.');
        }

        // Call the repository method to get the data
        return $this->repository->getAllStoreBrandWholesaler($type, $perPage);
    }

    //get products by store or brand or wholesaler id
    public function getProductsByRoleId($type, $userId, $perPage = 12)
    {
        // Validate the type
        if (!in_array($type, ['store', 'brand', 'wholesaler'])) {
            throw new \InvalidArgumentException('Invalid type provided. Allowed types are: store, brand, wholesaler.');
        }
        // Validate the store ID
        if (empty($userId)) {
            throw new \InvalidArgumentException('User ID cannot be empty.');
        }
        if (!is_numeric($userId)) {
            throw new \InvalidArgumentException('User ID must be a number.');
        }

        // Call the repository method to get the data
        return $this->repository->getProductsByRoleId($type, $userId, $perPage);
    }

}
