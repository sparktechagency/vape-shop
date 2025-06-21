<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $categories = [
            'VAPE KITS',
            'DISPOSABLES',
            'E-LIQUIDS',
            'BRANDS',
            'NIC POUCHES',
            'ACCESSORIES'
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create(['name' => $category]);
        }
    }

    //run category seeder
    // php artisan db:seed --class=CategorySeeder

}
