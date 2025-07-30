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
            $table->integer('slot')->nullable()->after('preferred_duration');
            $table->decimal('amount', 10, 2)->nullable()->after('slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trending_products', function (Blueprint $table) {
            $table->dropColumn(['slot', 'amount']);
        });
    }
};
