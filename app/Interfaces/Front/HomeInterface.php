<?php

namespace App\Interfaces\Front;

interface HomeInterface
{
   //get all store or brand or wholesaler
    public function getAllStoreBrandWholesaler($type, $perPage);

    //get products by store or brand or wholesaler id
    public function getProductsByRoleId(string $type,int $userId,int $perPage);
}
