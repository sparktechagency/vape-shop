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
        Schema::table('most_follower_ads', function (Blueprint $table) {
            $table->foreignId('region_id')
                ->constrained('regions')
                ->onDelete('cascade')
                ->after('user_id');
            $table->decimal('amount', 10, 2)
                ->nullable()
                ->after('region_id');
            $table->integer('slot')
                ->nullable()
                ->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('most_follower_ads', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');
            $table->dropColumn('slot');
            $table->dropColumn('amount');
        });
    }
};
