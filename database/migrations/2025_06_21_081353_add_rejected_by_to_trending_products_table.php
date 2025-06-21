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
            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User ID of the admin who rejected the ad request')->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trending_products', function (Blueprint $table) {
            $table->dropForeign(['rejected_by']);
            $table->dropColumn('rejected_by');
        });
    }
};
