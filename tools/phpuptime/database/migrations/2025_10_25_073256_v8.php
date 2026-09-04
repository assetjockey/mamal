<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->where('name', '=', 'custom_server_addr')->update(['value' => getHostIp()]);
        DB::table('settings')->where('name', '=', 'index')->update(['name' => 'homepage_redirect_url']);
        DB::table('settings')->where('name', '=', 'registration_verification')->update(['name' => 'registration_require_email_verification']);
        DB::table('settings')->where('name', '=', 'bad_words')->update(['name' => 'banned_words']);
        DB::table('settings')->where('name', '=', 'announcement_guest')->update(['name' => 'announcement_guest_content']);
        DB::table('settings')->where('name', '=', 'announcement_user')->update(['name' => 'announcement_user_content']);
        DB::table('settings')->where('name', '=', 'billing_invoice_prefix')->update(['name' => 'invoice_prefix']);

        DB::table('settings')->insert([
            ['name' => 'announcement_guest', 'value' => (config('settings.announcement_guest_content') ? 1 : 0)],
            ['name' => 'announcement_user', 'value' => (config('settings.announcement_user_content') ? 1 : 0)],
            ['name' => 'webhook_payment_created', 'value' => ''],
            ['name' => 'webhook_payment_updated', 'value' => ''],
            ['name' => 'webhook_secret_key', 'value' => Str::random(32)],
            ['name' => 'invoicing', 'value' => '1'],
            ['name' => 'monitors_double_check', 'value' => '0'],
            ['name' => 'monitors_double_check_delay_seconds', 'value' => '5'],
            ['name' => 'pwa', 'value' => '1'],
            ['name' => 'pwa_background_color', 'value' => '#000000'],
            ['name' => 'pwa_display', 'value' => 'standalone'],
            ['name' => 'pwa_logo', 'value' => 'pwa-logo.png'],
            ['name' => 'pwa_logo_maskable', 'value' => 'pwa-logo-maskable.png'],
            ['name' => 'pwa_logo_monochrome', 'value' => 'pwa-logo-monochrome.png'],
            ['name' => 'pwa_orientation', 'value' => 'any'],
            ['name' => 'pwa_theme_color', 'value' => '#000000'],
            ['name' => 'stripe_ideal', 'value' => '0'],
            ['name' => 'stripe_sepa_direct_debit', 'value' => '0'],
            ['name' => 'stripe_klarna', 'value' => '0'],
            ['name' => 'data_retention_grace_days', 'value' => '7'],
            ['name' => 'auth_apple_team_id', 'value' => ''],
            ['name' => 'auth_apple_key_id', 'value' => ''],
            ['name' => 'auth_apple_private_key', 'value' => ''],
        ]);

        foreach (DB::table('plans')->select('*')->cursor() as $row) {
            $features = json_decode($row->features, true);

            $features['data_retention'] = -1;

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
