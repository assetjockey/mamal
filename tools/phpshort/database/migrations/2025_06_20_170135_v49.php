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
        DB::table('settings')->insert([
            ['name' => 'mollie', 'value' => '0'],
            ['name' => 'mollie_key', 'value' => ''],
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->text('plan_subscription_information')->nullable()->after('plan_subscription_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
