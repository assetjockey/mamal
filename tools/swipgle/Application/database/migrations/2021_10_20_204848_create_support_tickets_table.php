<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ticket_number', 255);
            $table->bigInteger('user_id')->unsigned();
            $table->string('subject', 255);
            $table->tinyInteger('priority')->default(0)->comment('0: Normal, 1: Low , 2: High, 3: Urgent');
            $table->tinyInteger('status')->default(0)->comment('0: Opened, 1: Answered, 2: Replied, 3: Closed');
            $table->foreign("user_id")->references("id")->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('support__tickets');
    }
}
