<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('addressable_type')->nullable()->after('id');
            $table->unsignedBigInteger('addressable_id')->nullable()->after('id');
            $table->index(['addressable_id', 'addressable_type']);
        });

        $addresses = DB::table('addresses')->whereNotNull('user_id')->get();
        foreach ($addresses as $address) {
            DB::table('addresses')
                ->where('id', $address->id)
                ->update([
                    'addressable_id' => $address->user_id,
                    'addressable_type' => 'App\\Models\\User',
                ]);
        }

        // Step 3: Purono foreign key column remove korun
        Schema::table('addresses', function (Blueprint $table) {

            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {

            $table->foreignId('user_id')->nullable()->constrained('users');
        });

        //
        $userAddresses = DB::table('addresses')->where('addressable_type', 'App\\Models\\User')->get();
        foreach ($userAddresses as $address) {
            DB::table('addresses')
                ->where('id', $address->id)
                ->update(['user_id' => $address->addressable_id]);
        }

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['addressable_id', 'addressable_type']);
        });
    }
};
