<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class V300 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex('title');
            $table->index('name', 'name');
        });

        $language = DB::table('languages')->select('code')->where('default', '=', 1)->first();

        $settings = array_combine(['custom_js'], ['tracking_code']);

        $sqlQuery = null;
        foreach($settings as $new => $old) {
            $sqlQuery .= "WHEN `name` = '" . $old . "' THEN '" . $new . "' ";
        }

        DB::update("UPDATE `settings` SET `name` = CASE " . $sqlQuery . " END WHERE `name` IN ('" . implode("', '", $settings) . "')");

        DB::table('settings')->insert(
            [
                ['name' => 'locale', 'value' => $language->code],
                ['name' => 'bad_words', 'value' => '']
            ]
        );

        Schema::drop('languages');

        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('position')->after('visibility')->nullable()->default(0);
            $table->dropColumn('decimals');
        });
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
}
