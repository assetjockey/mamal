<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ip');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->bigInteger('storage_provider_id')->unsigned();
            $table->text('name');
            $table->string('filename');
            $table->string('mime');
            $table->string('extension');
            $table->string('size');
            $table->text('path');
            $table->foreign("user_id")->references("id")->on('users')->onDelete('cascade');
            $table->foreign("storage_provider_id")->references("id")->on('storage_providers')->onDelete('cascade');
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
        Schema::dropIfExists('uploads');
    }
}
