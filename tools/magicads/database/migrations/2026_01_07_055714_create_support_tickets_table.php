<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('priority', 20);
            $table->string('category', 20)->comment('technical|billing|account|general|request')->index();
            $table->string('status', 20)->comment('open|in_progress|resolved|closed')->index();
            $table->string('subject', 200);
            $table->timestamp('resolved_on')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
