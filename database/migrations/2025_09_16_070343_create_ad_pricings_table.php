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
            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('region_id')->constrained();
            $table->enum('type', ['product', 'follower', 'featured']);
            $table->json('details')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Unique key (Updated)
            $table->unique(
                ['ad_slot_id', 'category_id', 'region_id', 'type'],
                'slot_category_region_type_unique'
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
