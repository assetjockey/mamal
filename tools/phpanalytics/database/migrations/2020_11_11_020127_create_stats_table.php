<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stats', function (Blueprint $table) {
            $table->integer('website_id');
            $table->enum('name', ['browser', 'os', 'device', 'visitors', 'pageviews', 'country', 'city', 'page', 'referrer', 'resolution', 'language', 'landing_page', 'event', 'campaign', 'continent', 'visitors_hour', 'pageviews_hours']);
            $table->string('value', 255);
            $table->bigInteger('count')->default(1);
            $table->date('date');
            $table->primary(['website_id', 'name', 'value', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stats');
    }
}
