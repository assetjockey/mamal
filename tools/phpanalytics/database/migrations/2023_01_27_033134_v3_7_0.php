<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `stats` CHANGE `name` `name` ENUM('browser','os','device','visitors','pageviews','country','city','page','referrer','resolution','language','landing_page','event','campaign','continent','visitors_hours','pageviews_hours','visitors_hour','pageviews_hour')");
        DB::update("UPDATE `stats` SET `name` = 'visitors_hour' WHERE `name` = 'visitors_hours';");
        DB::update("UPDATE `stats` SET `name` = 'pageviews_hour' WHERE `name` = 'pageviews_hours';");
        DB::statement("ALTER TABLE `stats` CHANGE `name` `name` ENUM('browser','os','device','visitors','pageviews','country','city','page','referrer','resolution','language','landing_page','event','campaign','continent','visitors_hour','pageviews_hour');");

        DB::update("UPDATE `settings` SET `name` = 'social_x' WHERE `name` = 'social_twitter';");

        DB::table('settings')->insert(
            [
                ['name' => 'captcha_driver', 'value' => (config('settings.captcha_site_key') && config('settings.captcha_secret_key') ? 'recaptcha' : '')],
                ['name' => 'auth_google', 'value' => ''],
                ['name' => 'auth_google_client_id', 'value' => ''],
                ['name' => 'auth_google_client_secret', 'value' => ''],
                ['name' => 'auth_microsoft', 'value' => ''],
                ['name' => 'auth_microsoft_client_id', 'value' => ''],
                ['name' => 'auth_microsoft_client_secret', 'value' => ''],
                ['name' => 'auth_apple', 'value' => ''],
                ['name' => 'auth_apple_client_id', 'value' => ''],
                ['name' => 'auth_apple_client_secret', 'value' => ''],
                ['name' => 'auth_apple_team_id', 'value' => ''],
                ['name' => 'auth_apple_key_id', 'value' => ''],
                ['name' => 'auth_apple_private_key', 'value' => ''],
                ['name' => 'social_discord', 'value' => ''],
                ['name' => 'social_github', 'value' => ''],
                ['name' => 'social_linkedin', 'value' => ''],
                ['name' => 'social_pinterest', 'value' => ''],
                ['name' => 'social_reddit', 'value' => ''],
                ['name' => 'social_threads', 'value' => ''],
                ['name' => 'social_tiktok', 'value' => ''],
                ['name' => 'social_tumblr', 'value' => ''],
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
