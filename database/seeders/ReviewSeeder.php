<?php

namespace Database\Seeders;

use App\Enums\UserRole\Role;
use App\Models\ManageProduct;
use App\Models\Review;
use App\Models\StoreProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Store Product Hearts
        $storeProducts = StoreProduct::inRandomOrder()->take(10)->get();
        $storeUsers = User::where('role', Role::MEMBER->value)->get();

        foreach ($storeProducts as $product) {
            $randomUsers = $storeUsers->random(min(3, $storeUsers->count()));
            $data = $this->getProductAndRegionId($product->id, Role::STORE->value);
            foreach ($randomUsers as $user) {
                Review::create([
                    'user_id' => $user->id,
                    'manage_product_id' => $data['manage_product_id'] ?? null,
                    'store_product_id' => $product->id,
                    'region_id' => $data['region_id'] ?? null,
                    'rating' => rand(1, 5), // Random rating between 1 and 5
                    'comment' => fake()->sentence(10), // Random comment
                ]);
            }
        }

        // Manage Product Hearts
        $manageProducts = ManageProduct::inRandomOrder()->take(10)->get();
        $brandUsers = User::where('role', \App\Enums\UserRole\Role::MEMBER->value)->get();

        foreach ($manageProducts as $product) {
            $randomUsers = $brandUsers->random(min(3, $brandUsers->count()));
            foreach ($randomUsers as $user) {
                Review::create([
                    'user_id' => $user->id,
                    'manage_product_id' => $product->id,
                    'rating' => rand(1, 5), // Random rating between 1 and 5
                    'comment' => fake()->sentence(10), // Random comment
                ]);
            }
        }
    }

    private function getProductAndRegionId($productId, $role)
    {
        if ($role === Role::STORE->value) {
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
