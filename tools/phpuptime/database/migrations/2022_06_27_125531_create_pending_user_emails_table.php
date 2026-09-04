<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingUserEmailsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pending_user_emails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_type');
            $table->unsignedBigInteger('user_id');
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();

            $table->index(['user_type', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_user_emails');
    }
}
