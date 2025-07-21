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
        Schema::table('forum_groups', function (Blueprint $table) {
            $table->enum('type', ['public', 'private'])
              ->default('public')
              ->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forum_groups', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
