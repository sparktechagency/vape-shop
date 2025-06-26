<?php

namespace App\Interfaces\Front;

interface HomeInterface
{
    //search brand, shops, products, all account except admin,  and everything
    public function search(?string $searchTerm, ?string $type = 'products', int $perPage = 10, int $regionId);
   //get all store or brand or wholesaler
    public function getAllStoreBrandWholesaler($type, $perPage);

    //get products by store or brand or wholesaler id
    public function getProductsByRoleId(string $type,int $userId,int $perPage);
}
