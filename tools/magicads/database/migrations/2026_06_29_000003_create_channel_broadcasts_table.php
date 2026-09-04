<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel Broadcast — composed broadcasts.
 *
 * The message the user composed once, plus its media, CTA link and schedule.
 * Per-channel delivery is tracked on channel_broadcast_targets.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('channel_broadcasts')) {
            return;
        }

        Schema::create('channel_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Source references (nullable — message can be free-typed)
            $table->unsignedBigInteger('ad_copy_id')->nullable();
            $table->unsignedInteger('ad_copy_variant')->nullable();
            $table->unsignedBigInteger('ad_creative_id')->nullable();

            // Content
            $table->string('subject')->nullable();    // used by Email
            $table->longText('message')->nullable();
            $table->string('link_url', 1024)->nullable();
            $table->string('link_label')->nullable();

            // Media snapshot (resolved from the creative at compose time)
            $table->string('media_type', 20)->nullable();   // image|video
            $table->string('media_path', 1024)->nullable();
            $table->string('media_disk', 50)->nullable();
            $table->string('media_mime', 120)->nullable();

            // Scheduling
            $table->string('schedule_type', 20)->default('immediately'); // immediately|scheduled|recurring
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('recurrence_start_at')->nullable();
            $table->timestamp('recurrence_end_at')->nullable();
            $table->unsignedInteger('recurrence_interval_minutes')->nullable();
            $table->string('recurrence_days', 20)->nullable();

            // draft|scheduled|sending|completed|partial|failed
            $table->string('status', 20)->default('draft');

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_broadcasts');
    }
};
