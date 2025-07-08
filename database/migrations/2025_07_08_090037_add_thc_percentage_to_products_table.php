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
        Schema::table('manage_products', function (Blueprint $table) {
            $table->decimal('thc_percentage', 5, 2)->nullable()->after('product_discount');
        });

        Schema::table('store_products', function (Blueprint $table) {
            $table->decimal('thc_percentage', 5, 2)->nullable()->after('product_discount');
        });

        Schema::table('wholesaler_products', function (Blueprint $table) {
            $table->decimal('thc_percentage', 5, 2)->nullable()->after('product_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manage_products', function (Blueprint $table) {
            $table->dropColumn('thc_percentage');
        });

        Schema::table('store_products', function (Blueprint $table) {
            $table->dropColumn('thc_percentage');
        });

        Schema::table('wholesaler_products', function (Blueprint $table) {
            $table->dropColumn('thc_percentage');
        });
    }
};
