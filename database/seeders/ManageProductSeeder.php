<?php

namespace Database\Seeders;

use App\Enums\UserRole\Role;
use App\Models\Category;
use App\Models\ManageProduct;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ManageProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        $brandUsers = User::where('role',Role::BRAND->value)->get();
        $categories = Category::pluck('id');

        if ($brandUsers->isEmpty()) {
            $this->command->warn('No brand users found. Please seed users with BRAND role first.');
            return;
        }

        $productNames = [
            'Cloud Blaster',
            'Nicotine Pro',
            'Vape Elite',
            'Flavor Burst',
            'Storm Tank',
        ];

        foreach ($brandUsers as $brandUser) {
            foreach (array_rand($productNames, 3) as $key) {
                $name = $productNames[$key] . ' by ' . $brandUser->brand_name;

                ManageProduct::create([
                    'category_id' => $categories->random(),
                    'product_image' => 'products/default.jpg',
                    'user_id' => $brandUser->id,
                    'product_name' => $name,
                    'slug' => Str::slug($name) . '-' . uniqid(),
                    'product_price' => rand(1000, 5000) / 100,
                    'brand_name' => $brandUser->brand_name,
                    'product_discount' => rand(0, 30),
                    'product_discount_unit' => rand(0, 100),
                    'product_stock' => rand(10, 100),
                    'product_description' => fake()->sentence(10),
                    'product_faqs' => json_encode([
                        [
                            'question' => 'How to use this product?',
                            'answer' => 'Please read the manual carefully before use.',
                        ],
                        [
                            'question' => 'Is it rechargeable?',
                            'answer' => 'Yes, it comes with a USB-C charger.',
                        ],
                    ]),
                ]);
            }
        }
    }
}
