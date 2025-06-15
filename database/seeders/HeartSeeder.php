<?php

namespace Database\Seeders;

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
        $storeUsers = User::where('role', \App\Enums\UserRole\Role::MEMBER->value)->get();

        foreach ($storeProducts as $product) {
            $randomUsers = $storeUsers->random(min(3, $storeUsers->count()));
            foreach ($randomUsers as $user) {
                Heart::create([
                    'user_id' => $user->id,
                    'store_product_id' => $product->id,
                    'region_id' => User::find($product->user_id)->address->region_id,
                ]);
            }
        }

        // Manage Product Hearts
        $manageProducts = ManageProduct::inRandomOrder()->take(10)->get();
        $brandUsers = User::where('role', \App\Enums\UserRole\Role::MEMBER->value)->get();

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
}
