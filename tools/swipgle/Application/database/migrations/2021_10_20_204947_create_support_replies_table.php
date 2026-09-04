<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_replies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('admin_id')->unsigned()->nullable();
            $table->bigInteger('support_ticket_id')->unsigned();
            $table->text('message');
            $table->foreign("support_ticket_id")->references("id")->on('support_tickets')->onDelete('cascade');
            $table->foreign("admin_id")->references("id")->on('admins')->onDelete('cascade');
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
        Schema::dropIfExists('support__replies');
    }
}
