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
        Schema::create('metric_adjustments', function (Blueprint $table) {
            $table->id();
            $table->morphs('adjustable');
            $table->string('metric_type')->index();
            $table->unsignedBigInteger('adjustment_count')->default(0);

            $table->timestamps();

            // Prevent duplicate rows for the same metric on the same object
            $table->unique(['adjustable_id', 'adjustable_type', 'metric_type'], 'unique_metric_adjustment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metric_adjustments');
    }
};
