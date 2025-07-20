<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ManageProduct;
use App\Models\StoreProduct;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StoreProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $storeUsers = User::where('role', \App\Enums\UserRole\Role::STORE)->get();
        $manageProducts = ManageProduct::all();
        $categories = Category::pluck('id');

        if ($storeUsers->isEmpty()) {
            $this->command->warn('No STORE users found.');
            return;
        }

        foreach ($storeUsers as $storeUser) {

            // 3 ta manage product
            foreach ($manageProducts->random(3) as $product) {
                StoreProduct::create([
                    'user_id' => $storeUser->id,
                    'product_id' => $product->id,
                    'category_id' => $product->category_id,
                    'product_name' => $product->product_name,
                    'slug' => Str::slug($product->product_name) . '-' . uniqid(),
                    'product_image' => $product->product_image ? preg_replace('#^https?://[^/]+/storage/#', 'products/', $product->product_image) : null,
                    'product_price' => $product->product_price,
                    'brand_id' => $product->user_id,
                    'brand_name' => $product->brand_name,
                    'product_discount' => rtrim($product->product_discount, '%') ? (int) rtrim($product->product_discount, '%') : 0,
                    'product_discount_unit' => $product->product_discount_unit,
                    'product_stock' => rand(5, 50),
                    'product_description' => $product->product_description,
                    'product_faqs' => $product->product_faqs,
                ]);
            }

            //2 ta completely new product create
            for ($i = 0; $i < 2; $i++) {
                $name = fake()->words(3, true);
                StoreProduct::create([
                    'user_id' => $storeUser->id,
                    'product_id' => null,
                    'category_id' => $categories->random(),
                    'product_name' => ucfirst($name),
                    'slug' => Str::slug($name) . '-' . uniqid(),
                    'product_image' => 'products/default.jpg',
                    'product_price' => rand(1000, 5000) / 100,
                    'brand_id' => null,
                    'brand_name' => fake()->company(),
                    'product_discount' => rand(0, 25),
                    'product_discount_unit' => rand(0, 100),
                    'product_stock' => rand(10, 80),
                    'product_description' => fake()->sentence(10),
                    'product_faqs' => json_encode([
                        [
                            'question' => 'What is included?',
                            'answer' => 'Everything you need to get started.',
                        ],
                    ]),
                ]);
            }
        }
    }
}
