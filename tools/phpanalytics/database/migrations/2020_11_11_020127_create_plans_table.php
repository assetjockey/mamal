<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('product', 255);
            $table->string('name', 255);
            $table->text('description');
            $table->integer('trial_days')->nullable();
            $table->string('currency', 12);
            $table->tinyInteger('decimals')->nullable();
            $table->string('plan_month', 255)->nullable();
            $table->string('plan_year', 255)->nullable();
            $table->text('coupons')->nullable();
            $table->integer('amount_month')->nullable();
            $table->integer('amount_year')->nullable();
            $table->tinyInteger('visibility')->nullable();
            $table->bigInteger('option_pageviews')->nullable();
            $table->softDeletes();
        });

        DB::table('plans')->insert([
            'product' => '',
            'name' => 'Default',
            'description' => 'The plan\'s awesome description.',
            'trial_days' => NULL,
            'currency' => '',
            'decimals' => NULL,
            'plan_month' => '',
            'plan_year' => '',
            'amount_month' => 0,
            'amount_year' => 0,
            'visibility' => 1,
            'option_pageviews' => 1000
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans');
    }
}
