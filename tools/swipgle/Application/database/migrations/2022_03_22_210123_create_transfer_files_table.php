<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ip');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->bigInteger('transfer_id')->unsigned();
            $table->bigInteger('storage_provider_id')->unsigned();
            $table->text('name');
            $table->string('filename');
            $table->string('mime');
            $table->string('extension');
            $table->string('size');
            $table->text('path');
            $table->bigInteger('downloads')->default(0);
            $table->foreign("user_id")->references("id")->on('users')->onDelete('cascade');
            $table->foreign("transfer_id")->references("id")->on('transfers')->onDelete('cascade');
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
        Schema::dropIfExists('transfer_files');
    }
}
