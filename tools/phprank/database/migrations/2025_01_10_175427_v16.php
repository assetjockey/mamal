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
        Schema::table('reports', function (Blueprint $table) {
            $table->renameColumn('result', 'score');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('currency', 12)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('authed_at')->nullable()->index('authed_at')->after('tfa_code_created_at');
            $table->renameColumn('brand', 'brand_information');
            $table->string('brand_logo', 48)->nullable()->after('brand_information');
        });

        DB::table('settings')->where('name', '=', 'request_user_agent')->update(['value' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36']);

        DB::table('settings')->insert(
            [
                ['name' => 'auth_remember_me_duration', 'value' => 129600],
                ['name' => 'image_driver', 'value' => 'gd'],
                ['name' => 'paddle', 'value' => '0'],
                ['name' => 'paddle_mode', 'value' => 'live'],
                ['name' => 'paddle_api_key', 'value' => ''],
                ['name' => 'paddle_client_token', 'value' => ''],
                ['name' => 'paddle_wh_secret', 'value' => ''],
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
