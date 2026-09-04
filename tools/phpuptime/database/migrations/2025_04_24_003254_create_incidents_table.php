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
        Schema::create('incidents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id')->index('user_id');
            $table->unsignedInteger('monitor_id')->index('monitor_id');
            $table->string('url', 2048)->nullable();
            $table->unsignedSmallInteger('response_status_code')->default(0);
            $table->string('cause', 255)->index('cause')->nullable()->index('cause');
            $table->string('comment', 1024)->nullable();
            $table->text('alerted')->nullable();
            $table->string('token', 16)->nullable();
            $table->string('monitor_token', 16)->nullable();
            $table->timestamp('started_at')->nullable()->index('started_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('ended_at')->nullable()->index('ended_at');

            $table->index(['monitor_id', 'started_at']);
            $table->index(['monitor_id', 'ended_at']);
            $table->index(['started_at', 'ended_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
