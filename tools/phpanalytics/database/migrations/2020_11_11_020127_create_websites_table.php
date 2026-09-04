<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebsitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('url', 255)->index('url');
            $table->integer('user_id')->index('user_id');
            $table->tinyInteger('privacy')->nullable()->default(0);
            $table->string('password')->nullable();
            $table->tinyInteger('email')->nullable()->index('email_reports');
            $table->tinyInteger('exclude_bots')->nullable();
            $table->text('exclude_ips')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('websites');
    }
}
