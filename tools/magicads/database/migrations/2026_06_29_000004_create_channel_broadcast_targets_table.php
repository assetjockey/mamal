<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel Broadcast — per-destination delivery targets.
 *
 * One row per (broadcast × destination). Drives the immediate-send path, the
 * scheduled worker and the recurring cadence (the worker re-arms next_run_at
 * after each successful recurring send).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('channel_broadcast_targets')) {
            return;
        }

        Schema::create('channel_broadcast_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained('channel_broadcasts')->onDelete('cascade');
            $table->foreignId('destination_id')->constrained('channel_broadcast_destinations')->onDelete('cascade');

            $table->string('channel', 30);

            // pending|scheduled|sending|sent|failed
            $table->string('status', 20)->default('pending');

            $table->string('remote_id')->nullable();        // provider message id
            $table->unsignedInteger('recipients_sent')->default(0);
            $table->text('error')->nullable();

            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('run_count')->default(0);
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'next_run_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_broadcast_targets');
    }
};
