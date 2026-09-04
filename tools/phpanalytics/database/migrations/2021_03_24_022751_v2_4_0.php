<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class V240 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->increments('id')->change();
            $table->integer('user_id')->unsigned()->change();
            $table->text('exclude_params')->after('exclude_bots')->nullable();
        });

        Schema::table('recents', function (Blueprint $table) {
            $table->increments('id')->change();
            $table->integer('website_id')->unsigned()->change();
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
