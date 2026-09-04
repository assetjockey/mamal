<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Social Media Studio — per-network delivery targets.
 *
 * One row per (post × connected account). Each target carries its own status,
 * remote post id/url, error and retry bookkeeping, so the publishing worker
 * can advance, retry or repost each network independently.
 *
 *   next_run_at — when the worker should (re)attempt this target. Drives both
 *                 one-shot scheduling and the auto-repost cadence.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_media_studio_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('social_media_studio_posts')->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('social_media_studio_accounts')->nullOnDelete();

            $table->string('platform', 30);
            $table->string('platform_id', 191)->nullable();  // snapshot of the account's remote id

            // pending|scheduled|publishing|posted|failed|skipped
            $table->enum('status', ['pending', 'scheduled', 'publishing', 'posted', 'failed', 'skipped'])->default('pending');

            $table->string('remote_id', 191)->nullable();     // tweet id / post id / video id
            $table->string('remote_url', 1024)->nullable();
            $table->text('error')->nullable();

            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedInteger('run_count')->default(0);  // successful publishes (repost counter)
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'next_run_at']);
            $table->index(['post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_media_studio_targets');
    }
};
