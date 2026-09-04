<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('ip');
            $table->bigInteger('storage_provider_id')->unsigned();

            $table->string('unique_id')->unique();
            $table->string('link')->unique();

            $table->string('sender_email');
            $table->string('sender_name')->nullable();
            $table->longText('emails')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();

            $table->string('password')->nullable();
            $table->boolean('download_notify')->default(false);
            $table->boolean('expiry_notify')->default(false);
            $table->timestamp('expiry_at')->nullable();

            $table->tinyInteger('type');
            $table->boolean('status')->default(true);
            $table->string('cancellation_reason')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamp('files_deleted_at')->nullable();
            $table->boolean('read_status')->default(false);

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
        Schema::dropIfExists('transfers');
    }
}
