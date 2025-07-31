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
        Schema::table('trending_products', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('cascade')
                ->after('user_id');
            $table->foreignId('region_id')
                ->constrained('regions')
                ->onDelete('cascade')
                ->after('category_id');
            $table->index(['category_id', 'region_id'], 'trending_products_category_region_index');
            $table->index('product_id', 'trending_products_product_index');
            $table->index('user_id', 'trending_products_user_index');
            $table->index('preferred_duration', 'trending_products_duration_index');
            $table->index('status', 'trending_products_status_index');
            $table->index('amount', 'trending_products_amount_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trending_products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['region_id']);
            $table->dropIndex('trending_products_category_region_index');
            $table->dropIndex('trending_products_product_index');
            $table->dropIndex('trending_products_user_index');
            $table->dropIndex('trending_products_duration_index');
            $table->dropIndex('trending_products_status_index');
            $table->dropIndex('trending_products_amount_index');
            $table->dropColumn(['category_id', 'region_id']);
        });
    }
};
