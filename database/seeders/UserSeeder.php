<?php

namespace Database\Seeders;

use App\Enums\UserRole\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Member
        User::factory(10)->create([
            'password' => Hash::make('11111111'),
        ]);

        // Wholesaler
        User::factory(10)->create([
            'role' => Role::WHOLESALER,
            'password' => Hash::make('11111111'),
        ]);

        // Association
        User::factory(10)->create([
            'role' => Role::ASSOCIATION,
            'password' => Hash::make('11111111'),
        ]);

        // 🏬 Store Users
        $storeNames = ['Vape Heaven', 'Cloud Nine Vapes', 'Puff Paradise', 'Smoke House', 'Vapezilla'];
        foreach ($storeNames as $store) {
           $user = User::create([
                'first_name' => $store,
                'email' => strtolower(str_replace(' ', '', $store)) . '@store.com',
                'password' => Hash::make('11111111'),
                'role' => Role::STORE,
                'email_verified_at' => now(),
            ]);

            $user->address()->create([
                'region_id' => \App\Models\Region::inRandomOrder()->value('id'),
                'address' => 'Some Street',
                'zip_code' => '12345',
            ]);
        }

        // 🏷️ Brand Users
        $brands = ['Elf Bar', 'Juul', 'Oxva', 'Voopoo', 'Vaporesso'];
        foreach ($brands as $brand) {
            User::create([
                'first_name' => $brand,
                'email' => strtolower(str_replace(' ', '', $brand)) . '@brand.com',
                'password' => Hash::make('11111111'),
                'role' => Role::BRAND,
                'email_verified_at' => now(),
            ]);
        }
    }
}
