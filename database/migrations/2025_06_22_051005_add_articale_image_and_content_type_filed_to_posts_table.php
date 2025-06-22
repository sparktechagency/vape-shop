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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('article_image')->nullable()->after('content');
            $table->enum('content_type', ['post', 'article'])
                ->default('post')
                ->after('article_image')
                ->comment('Type of content: post or article');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('article_image');
            $table->dropColumn('content_type');
        });
    }
};
