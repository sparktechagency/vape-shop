<?php

namespace Database\Seeders;

use App\Enums\UserRole\Role;
use Illuminate\Database\Seeder;
use App\Models\Heart;
use App\Models\StoreProduct;
use App\Models\ManageProduct;
use App\Models\User;

class HeartSeeder extends Seeder
{
    public function run(): void
    {
        // Store Product Hearts
        $storeProducts = StoreProduct::inRandomOrder()->take(10)->get();
        $storeUsers = User::where('role', Role::MEMBER)->get();

        foreach ($storeProducts as $product) {
            $randomUsers = $storeUsers->random(min(3, $storeUsers->count()));
            $data = $this->getProductAndRegionId($product->id, Role::STORE);
            foreach ($randomUsers as $user) {
                Heart::create([
                    'user_id' => $user->id,
                    'manage_product_id' => $data['manage_product_id'] ?? null,
                    'store_product_id' => $product->id,
                    'region_id' => $data['region_id'] ?? null,
                ]);
            }
        }

        // Manage Product Hearts
        $manageProducts = ManageProduct::inRandomOrder()->take(10)->get();
        $brandUsers = User::where('role', \App\Enums\UserRole\Role::MEMBER)->get();

        foreach ($manageProducts as $product) {
            $randomUsers = $brandUsers->random(min(3, $brandUsers->count()));
            foreach ($randomUsers as $user) {
                Heart::create([
                    'user_id' => $user->id,
                    'manage_product_id' => $product->id,
                ]);
            }
        }
    }

    private function getProductAndRegionId($productId, $role)
    {
        if ($role === Role::STORE) {
            $data = [];
            $product = StoreProduct::find($productId);
            if($product){
                if ($product->user_id && User::find($product->user_id)->address) {
                    $data['region_id'] = User::find($product->user_id)->address->region_id;
                }
                if($product->product_id && $product->manageProducts) {
                    $data['manage_product_id'] = $product->product_id;
                } else {
                    $data['manage_product_id'] = null;
                }

            }
            return $data;
        }
        return null;
    }
}
