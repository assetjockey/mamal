<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('support_reply_id')->unsigned();
            $table->string('attachment', 100);
            $table->foreign("support_reply_id")->references("id")->on('support_replies')->onDelete('cascade');
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
        Schema::dropIfExists('support__attachments');
    }
}
