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
        Schema::create('b2b_pricings', function (Blueprint $table) {
            $table->id();
            //  Polymorphic columns
            $table->unsignedBigInteger('productable_id');
            $table->string('productable_type');

            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->decimal('wholesale_price', 10, 2);
            $table->integer('moq')->default(1);
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_pricings');
    }
};
