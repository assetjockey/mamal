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
        Schema::create('monitors', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index('user_id');
            $table->string('name', 255)->index('name');
            $table->string('url', 2048)->nullable();
            $table->unsignedInteger('interval');
            $table->text('alerts')->nullable();
            $table->string('status', 16)->nullable()->default('pending');
            $table->string('token', 16)->nullable();
            $table->unsignedSmallInteger('ssl_alert_days')->nullable()->default(0)->index('ssl_alert_days');
            $table->text('ssl_information')->nullable();
            $table->timestamp('ssl_checked_at')->nullable();
            $table->timestamp('ssl_alerted_at')->nullable()->index('ssl_alerted_at');
            $table->timestamp('ssl_created_at')->nullable()->index('ssl_created_at');
            $table->timestamp('ssl_ends_at')->nullable()->index('ssl_ends_at');
            $table->timestamp('maintenance_start_at')->nullable()->index('maintenance_start_at');
            $table->timestamp('maintenance_end_at')->nullable()->index('maintenance_end_at');
            $table->timestamp('started_at')->nullable()->index('started_at');
            $table->timestamp('checked_at')->nullable()->index('checked_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitors');
    }
};
