<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recents', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('website_id')->index('website_id');
            $table->string('page', 255)->nullable();
            $table->string('referrer', 255)->nullable()->index('referrer');
            $table->string('os', 64)->nullable();
            $table->string('browser', 64)->nullable();
            $table->string('device', 64)->nullable();
            $table->string('country', 64)->nullable();
            $table->string('city', 128)->nullable();
            $table->char('language', 2)->nullable();
            $table->timestamp('created_at')->useCurrent()->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recents');
    }
}
