<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeSubscriptionsTablePolymorphic extends Migration
{
    public function up(): void
    {
        // add polymorphic columns to subscriptions table
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('subscribable_type')->nullable()->after('id');
            $table->unsignedBigInteger('subscribable_id')->nullable()->after('id');
            $table->index(['subscribable_id', 'subscribable_type']);
        });

        // migrate existing data to new polymorphic columns
        $subscriptions = DB::table('subscriptions')->whereNotNull('user_id')->get();
        foreach ($subscriptions as $subscription) {
            DB::table('subscriptions')
                ->where('id', $subscription->id)
                ->update([
                    'subscribable_id' => $subscription->user_id,
                    'subscribable_type' => 'App\\Models\\User',
                ]);
        }

        // remove old foreign key column
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // re-add the user_id column
            $table->foreignId('user_id')->nullable()->constrained('users');
        });

        // migrate data back to user_id column
        $userSubscriptions = DB::table('subscriptions')->where('subscribable_type', 'App\\Models\\User')->get();
        foreach ($userSubscriptions as $subscription) {
            DB::table('subscriptions')
                ->where('id', $subscription->id)
                ->update(['user_id' => $subscription->subscribable_id]);
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['subscribable_id', 'subscribable_type']);
        });
    }
}
