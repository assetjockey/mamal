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
        Schema::drop('stats');
        Schema::drop('recents');

        Schema::create('stats', function (Blueprint $table) {
            $table->integer('website_id')->index('website_id')->index('website_id');
            $table->boolean('unique')->nullable()->index('unique');
            $table->string('referrer', 255)->nullable();
            $table->string('page', 255)->nullable();
            $table->string('browser', 64)->nullable();
            $table->string('operating_system', 64)->nullable();
            $table->string('device', 64)->nullable();
            $table->string('continent', 16)->nullable();
            $table->string('country', 64)->nullable();
            $table->string('city', 128)->nullable();
            $table->string('screen_resolution', 16)->nullable();
            $table->string('theme', 8)->nullable();
            $table->string('campaign', 64)->nullable();
            $table->char('language', 2)->nullable();
            $table->timestamp('created_at')->useCurrent()->index('created_at');

            $table->index(['website_id', 'unique', 'created_at']);
            $table->index(['website_id', 'unique', 'created_at', 'referrer']);
            $table->index(['website_id',           'created_at', 'page']);
            $table->index(['website_id', 'unique', 'created_at', 'page']);
            $table->index(['website_id', 'unique', 'created_at', 'browser']);
            $table->index(['website_id', 'unique', 'created_at', 'operating_system']);
            $table->index(['website_id', 'unique', 'created_at', 'device']);
            $table->index(['website_id', 'unique', 'created_at', 'continent']);
            $table->index(['website_id', 'unique', 'created_at', 'country']);
            $table->index(['website_id', 'unique', 'created_at', 'city']);
            $table->index(['website_id', 'unique', 'created_at', 'screen_resolution']);
            $table->index(['website_id', 'unique', 'created_at', 'theme']);
            $table->index(['website_id', 'unique', 'created_at', 'campaign']);
            $table->index(['website_id', 'unique', 'created_at', 'language']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->integer('website_id')->index('website_id')->index('website_id');
            $table->string('value', 128)->nullable();
            $table->timestamp('created_at')->useCurrent()->index('created_at');

            $table->index(['website_id', 'created_at', 'value']);
        });

        Schema::table('users', function($table) {
            $table->timestamp('authed_at')->nullable()->index('authed_at')->after('tfa_code_created_at');
            $table->unsignedBigInteger('website_pageviews_count')->after('can_track')->default(0);
            $table->renameColumn('can_track', 'can_track_websites');
        });

        DB::table('settings')->insert(
            [
                ['name' => 'auth_remember_me_duration', 'value' => 129600],
                ['name' => 'image_driver', 'value' => 'gd'],
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
