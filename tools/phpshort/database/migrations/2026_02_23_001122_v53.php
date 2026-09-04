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
            ['name' => 'ad_stats_header', 'value' => ''],
            ['name' => 'ad_stats_footer', 'value' => ''],
            ['name' => 'ad_splash_header', 'value' => ''],
            ['name' => 'ad_splash_footer', 'value' => ''],
            ['name' => 'nowpayments', 'value' => '0'],
            ['name' => 'nowpayments_key', 'value' => ''],
            ['name' => 'nowpayments_wh_secret', 'value' => ''],
            ['name' => 'mercadopago', 'value' => '0'],
            ['name' => 'mercadopago_access_token', 'value' => ''],
            ['name' => 'short_alias_length', 'value' => 6],
            ['name' => 'short_link_metadata_fetch', 'value' => 1],
            ['name' => 'short_splash_redirect', 'value' => 0],
            ['name' => 'short_splash_redirect_seconds', 'value' => 15],
            ['name' => 'short_splash_redirect_skipping', 'value' => 1],
            ['name' => 'xendit', 'value' => '0'],
            ['name' => 'xendit_secret_key', 'value' => ''],
            ['name' => 'xendit_wh_secret', 'value' => ''],
            ['name' => 'yoomoney', 'value' => '0'],
            ['name' => 'yoomoney_shop_id', 'value' => ''],
            ['name' => 'yoomoney_secret_key', 'value' => ''],
            ['name' => 'yoomoney_receipts', 'value' => '0'],
            ['name' => 'yoomoney_vat_code', 'value' => ''],
        ]);

        DB::table('settings')->where('name', '=', 'short_domain')->update(['name' => 'short_domain_id']);

        DB::table('settings')->whereIn('name', ['coinbase', 'coinbase_key', 'coinbase_wh_secret'])->delete();

        DB::table('settings')->where('name', '=', 'user_avatar_format')->update(['name' => 'user_avatar_extensions']);

        Schema::table('links', function (Blueprint $table) {
            $table->dropIndex('clicks');
            $table->renameColumn('clicks', 'clicks_count');
            $table->index('clicks_count');
            $table->unsignedBigInteger('user_id')->change();
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
            $table->renameColumn('index_page', 'homepage_url');
            $table->renameColumn('not_found_page', 'not_found_url');
        });

        Schema::table('spaces', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
        });

        Schema::table('pixels', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });

        foreach (DB::table('plans')->select('*')->cursor() as $row) {
            $features = json_decode($row->features, true);

            $features['additional_domains'] = $features['global_domains'];
            $features['no_ads'] = 1;
            unset($features['global_domains']);

            DB::statement("UPDATE `plans` SET `features` = :features WHERE `id` = :id", ['features' => json_encode($features), 'id' => $row->id]);
        }

        DB::table('settings')->where('name', '=', 'request_user_agent')->update(['value' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
