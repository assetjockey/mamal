<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->bigIncrements('id');
            $table->string('name', 150);
            $table->string('short_description');
            $table->string('color', 10);
            $table->tinyInteger('interval')->comment('0:Monthly 1:Yearly 2:Lifetime');
            $table->float('price', 10, 2);
            $table->boolean('auth')->comment('0:Optional 1:Required');
            $table->bigInteger('storage_space')->nullable();
            $table->bigInteger('transfer_size')->nullable();
            $table->bigInteger('transfer_interval')->nullable();
            $table->boolean('transfer_password')->comment('0:No 1:Yes');
            $table->boolean('transfer_notify')->comment('0:No 1:Yes');
            $table->boolean('transfer_expiry')->comment('0:No 1:Yes');
            $table->boolean('transfer_link')->comment('0:No 1:Yes');
            $table->boolean('advertisements')->comment('0:No 1:Yes');
            $table->longText('custom_features')->nullable();
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
        Schema::dropIfExists('plans');
    }
}
