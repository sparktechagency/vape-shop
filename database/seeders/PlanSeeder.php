<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::updateOrCreate(['slug' => 'store-monthly'], [
            'name' => 'Store Monthly Subscription',
            'price' => 30.00,
            'type' => 'store',
            'description' => 'Main monthly subscription for store owners.'
        ]);

        Plan::updateOrCreate(['slug' => 'brand-monthly'], [
            'name' => 'Brand Monthly Subscription',
            'price' => 40.00,
            'type' => 'brand',
            'description' => 'Main monthly subscription for brand owners.'
        ]);
        Plan::updateOrCreate(['slug' => 'wholesaler-monthly'], [
            'name' => 'Wholesaler Monthly Subscription',
            'price' => 40.00,
            'type' => 'wholesaler',
            'description' => 'Main monthly subscription for wholesaler owners.'
        ]);

        Plan::updateOrCreate(['slug' => 'advocacy-champion'], [
            'name' => 'Advocacy Champion Add-on',
            'price' => 6.00,
            'type' => 'advocacy',
            'badge' => 'Advocacy Champion',
            'description' => 'Optional add-on to support industry associations.'
        ]);
        Plan::updateOrCreate(['slug' => 'core-club-membership'], [
            'name' => 'Core Club Membership',
            'price' => 6.00,
            'type' => 'member',
            'badge' => 'Core Club',
            'description' => 'Optional add-on to support industry associations.'
        ]);

        Plan::updateOrCreate(['slug' => 'hemp-alley'], [
            'name' => 'Hemp Alley Add-on',
            'price' => 3.00,
            'type' => 'hemp',
            'badge' => 'Hemp Alley',
            'description' => 'Optional add-on to support Hemp Alley association.'
        ]);

        Plan::updateOrCreate(['slug' => 'additional-location'], [
            'name' => 'Additional Location Fee',
            'price' => 30.00,
            'type' => 'location',
            'description' => 'Fee for adding a new business location.'
        ]);
    }
}
