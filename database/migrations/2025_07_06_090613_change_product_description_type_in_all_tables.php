<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeProductDescriptionTypeInAllTables extends Migration
{
    public function up()
    {
        Schema::table('manage_products', function (Blueprint $table) {
            $table->text('product_description')->nullable()->change();
        });

        Schema::table('store_products', function (Blueprint $table) {
            $table->text('product_description')->nullable()->change();
        });

        Schema::table('wholesaler_products', function (Blueprint $table) {
            $table->text('product_description')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('manage_products', function (Blueprint $table) {
            $table->string('product_description')->nullable()->change();
        });

        Schema::table('store_products', function (Blueprint $table) {
            $table->string('product_description')->nullable()->change();
        });

        Schema::table('wholesaler_products', function (Blueprint $table) {
            $table->string('product_description')->nullable()->change();
        });
    }
}

