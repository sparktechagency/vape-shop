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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payable_id')->unsigned();
            $table->string('payable_type');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->unique();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])
                ->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
