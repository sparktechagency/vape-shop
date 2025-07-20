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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Plan-er naam, jemon: "Store Monthly"
            $table->string('slug')->unique(); // Unik naam, jemon: "store-monthly"
            $table->decimal('price', 8, 2);
            $table->enum('type', ['main', 'add_on', 'location']); // Plan-er dhoron
            $table->string('badge')->nullable(); // Optional badge for the plan
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
