<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB; // DB facade import korun
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN invoice_status ENUM('pending_invoice', 'invoice_sent', 'paid', 'cancelled', 'expired') NOT NULL DEFAULT 'pending_invoice'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN invoice_status ENUM('pending_invoice', 'invoice_sent', 'paid', 'cancelled') NOT NULL DEFAULT 'pending_invoice'");
    }
};
