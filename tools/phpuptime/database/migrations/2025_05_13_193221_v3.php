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
            ['name' => 'custom_server_addr', 'value' => ''],
            ['name' => 'paddle', 'value' => '0'],
            ['name' => 'paddle_mode', 'value' => 'live'],
            ['name' => 'paddle_api_key', 'value' => ''],
            ['name' => 'paddle_client_token', 'value' => ''],
            ['name' => 'paddle_wh_secret', 'value' => '']
        ]);

        Schema::table('monitors', function (Blueprint $table) {
            $table->string('alert_text_lookup', 255)->nullable()->after('url');
            $table->string('alert_condition', 32)->default('url_unavailable')->after('url');

            $table->tinyInteger('cache_buster')->default(0)->nullable()->after('url');
            $table->string('response_keyword', 255)->nullable()->after('url');
            $table->text('request_auth_password')->nullable()->after('url');
            $table->string('request_auth_username', 255)->nullable()->after('url');
            $table->text('request_headers')->nullable()->after('url');
            $table->string('request_method', 7)->default('GET')->after('url');

            $table->timestamp('domain_ends_at')->nullable()->index('domain_ends_at')->after('ssl_ends_at');
            $table->timestamp('domain_created_at')->nullable()->index('domain_created_at')->after('ssl_ends_at');
            $table->timestamp('domain_alerted_at')->nullable()->index('domain_alerted_at')->after('ssl_ends_at');
            $table->timestamp('domain_checked_at')->nullable()->after('ssl_ends_at');
            $table->text('domain_information')->nullable()->after('ssl_ends_at');
            $table->unsignedSmallInteger('domain_alert_days')->nullable()->default(0)->index('domain_alert_days')->after('ssl_ends_at');
        });

        Schema::table('checks', function (Blueprint $table) {
            $table->string('request_method', 7)->default('GET')->after('monitor_id');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('currency', 12)->nullable()->change();
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('response_status_code');
        });

        foreach (DB::table('plans')->select('*')->cursor() as $row) {
            $features = json_decode($row->features, true);

            $features['domain_monitoring'] = 1;

            DB::statement("UPDATE `plans` SET `features` = :features WHERE `id` = :id", ['features' => json_encode($features), 'id' => $row->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
