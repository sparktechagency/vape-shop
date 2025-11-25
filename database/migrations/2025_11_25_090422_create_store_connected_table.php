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
        Schema::create('connected_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('connected_store_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted','rejected'])->default('pending');
            $table->timestamps();
            $table->unique(['store_id', 'connected_store_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('connected_locations', function (Blueprint $table) {
            //
        });
    }
};
