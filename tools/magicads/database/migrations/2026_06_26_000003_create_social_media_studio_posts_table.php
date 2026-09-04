<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Social Media Studio — composed posts.
 *
 * One row per "compose" action. The actual per-network delivery lives in
 * social_media_studio_targets (one target per selected account) so a single
 * post can succeed on some networks and fail on others independently.
 *
 * Content is sourced from the user's existing MagicAds library:
 *   - caption text  ← an AdCopy variant (or AI-generated / hand-written)
 *   - media         ← an AdCreative (image or video)
 * A denormalized media snapshot is kept so the post still publishes even if
 * the underlying creative is later deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_media_studio_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Source references (nullable — caption may be free-typed, media optional)
            $table->foreignId('ad_copy_id')->nullable()->constrained('ad_copies')->nullOnDelete();
            $table->unsignedSmallInteger('ad_copy_variant')->nullable();
            $table->foreignId('ad_creative_id')->nullable()->constrained('ad_creatives')->nullOnDelete();

            // Resolved content
            $table->text('caption')->nullable();

            // Denormalized media snapshot (survives creative deletion)
            $table->string('media_type', 10)->nullable();   // image | video
            $table->string('media_path')->nullable();        // file_path on its disk
            $table->string('media_disk', 20)->nullable();    // storage_disk (local|s3|wasabi)
            $table->string('media_mime', 80)->nullable();

            // Scheduling
            $table->enum('schedule_type', ['immediately', 'scheduled', 'repost'])->default('immediately');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('repost_start_at')->nullable();
            $table->timestamp('repost_end_at')->nullable();
            $table->unsignedInteger('repost_interval_minutes')->nullable();
            $table->string('repost_days', 20)->nullable();   // all|weekdays|weekends|even|odd|monday…

            // Rollup status across targets: draft|scheduled|publishing|completed|partial|failed
            $table->enum('status', ['draft', 'scheduled', 'publishing', 'completed', 'partial', 'failed'])->default('draft');

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_media_studio_posts');
    }
};
