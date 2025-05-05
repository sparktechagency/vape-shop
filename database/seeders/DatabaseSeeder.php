<?php

namespace Database\Seeders;

use App\Enums\UserRole\Role;
use App\Models\User;
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

        User::create([
            'first_name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => 'Admin@123',
            'role' => Role::ADMIN,
            'email_verified_at' => now(),
        ]);
    }
}
