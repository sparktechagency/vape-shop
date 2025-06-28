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
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('wholesaler_product_id')->nullable()->constrained('wholesaler_products')->onDelete('cascade');
            // Add any other necessary columns or modifications here
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['wholesaler_product_id']);
            $table->dropColumn('wholesaler_product_id');
            // Reverse any other modifications made in the up method
        });
    }
};
