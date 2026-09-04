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
        DB::table('settings')->whereIn('name', ['captcha_contact', 'captcha_registration'])->delete();

        DB::table('settings')->insert(
            [
                ['name' => 'force_https', 'value' => '1'],
                ['name' => 'contact_form', 'value' => '0'],
                ['name' => 'contact_address', 'value' => ''],
                ['name' => 'contact_email_public', 'value' => ''],
                ['name' => 'contact_phone', 'value' => '0'],
            ]
        );

        Schema::table('websites', function ($table) {
            $table->timestamp('favorited_at')->after('exclude_ips')->default(null)->nullable();
        });

        Schema::table('pages', function ($table) {
            $table->string('language', 16)->after('visibility')->nullable();
        });

        DB::statement("UPDATE `pages` SET `language` = :language", ['language' => config('settings.locale')]);
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
