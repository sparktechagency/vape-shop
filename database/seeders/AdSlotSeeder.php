<?php

namespace Database\Seeders;

use App\Models\AdSlot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $slotName = "Slot " . $i;

            AdSlot::updateOrCreate(
                ['slug' => Str::slug($slotName)],
                ['name' => $slotName]             
            );
        }
    }
}
