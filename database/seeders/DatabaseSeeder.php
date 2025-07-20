<?php

namespace Database\Seeders;

use App\Enums\UserRole\Role;
use App\Models\Category;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Contracts\Cache\Store;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::create([
        //     'first_name' => 'Admin User',
        //     'email' => 'admin@gmail.com',
        //     'password' => 'Admin@123',
        //     'role' => Role::ADMIN,
        //     'email_verified_at' => now(),
        // ]);

        // User::create([
        //     'first_name' => 'SMOK',
        //     'email' => 'kaziomar001@yopmail.com',
        //     'password' => '11111111',
        //     'role' => Role::BRAND,
        //     'email_verified_at' => now(),
        //     'phone' => fake()->phoneNumber(),
        // ]);


        // User::create([
        //     'first_name' => 'ELUX',
        //     'email' => 'elux@yopmail.com',
        //     'password' => '11111111',
        //     'role' => Role::BRAND,
        //     'email_verified_at' => now(),
        //     'phone' => fake()->phoneNumber(),
        // ]);
        // User::create([
        //     'first_name' => 'Omar',
        //     'last_name' => 'Faruk',
        //     'email' => 'kaziomar@yopmail.com',
        //     'password' => '11111111',
        //     'role' => Role::MEMBER,
        //     'email_verified_at' => now(),
        //     'phone' => fake()->phoneNumber(),
        // ]);
        // User::create([
        //     'first_name' => 'Tahsan',
        //     'last_name' => 'Tanjim',
        //     'email' => 'kaziomar2@yopmail.com',
        //     'password' => '11111111',
        //     'role' => Role::MEMBER,
        //     'email_verified_at' => now(),
        //     'phone' => fake()->phoneNumber(),
        // ]);



        $this->call([
            // Add other seeders here
            // CategorySeeder::class,
            // CountryRegionSeeder::class,
            // UserSeeder::class,
            // ManageProductSeeder::class,
            // StoreProductSeeder::class,
            // HeartSeeder::class,
            PlanSeeder::class,
        ]);

        // $user = User::create([
        //     'first_name' => 'Super Shop',
        //     'email' => 'kaziomar10@yopmail.com',
        //     'password' => '11111111',
        //     'role' => Role::STORE,
        //     'email_verified_at' => now(),
        //     'phone' => fake()->phoneNumber(),
        // ]);
        // $user->address()->create([
        //     'region_id' => 18,
        //     'address' => 'Los Angeles, California',
        //     'zip_code' => '12345',
        // ]);
    }


}
