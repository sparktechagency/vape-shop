<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ad_pricings', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('ad_slot_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained();
            $table->foreignId('region_id')->constrained();
            $table->json('details')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            // Unique key (Best Practice)
            $table->unique(
                ['ad_slot_id', 'category_id', 'region_id'],
                'slot_category_region_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_pricings');
    }
};
