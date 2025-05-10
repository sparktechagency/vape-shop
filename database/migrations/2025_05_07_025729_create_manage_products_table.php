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
        Schema::create('manage_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('product_name');
            $table->string('slug')->unique();
            $table->string('product_image')->nullable();
            $table->decimal('product_price', 10, 2)->nullable();
            $table->string('brand_name')->nullable();
            $table->decimal('product_discount', 10, 2)->nullable();
            $table->decimal('product_discount_unit', 10, 2)->nullable();
            $table->integer('product_stock')->nullable();
            $table->string('product_description')->nullable();
            $table->json('product_faqs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manage_products');
    }
};
