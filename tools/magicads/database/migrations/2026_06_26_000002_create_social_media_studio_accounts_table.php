<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Social Media Studio — per-user connected social accounts.
 *
 * One row per authorized destination (a Facebook *page*, an Instagram business
 * account, a YouTube channel, a TikTok creator, etc.). Access/refresh tokens
 * are encrypted at rest via the model's `encrypted` casts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_media_studio_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('platform', 30);        // twitter|linkedin|facebook|instagram|tiktok|youtube|youtube_shorts
            $table->string('platform_id', 191);    // remote account/page/channel id used for posting
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('picture', 1024)->nullable();
            $table->string('type', 30)->nullable(); // user|page|business|creator|channel|shorts

            // Tokens (encrypted via model casts)
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('access_token_expires_at')->nullable();
            $table->timestamp('refresh_token_expires_at')->nullable();

            // Arbitrary provider metadata (page_id, scope, channel_title, …)
            $table->text('metadata')->nullable();

            $table->boolean('status')->default(true); // active / paused
            $table->timestamps();

            $table->unique(['user_id', 'platform', 'platform_id'], 'sms_account_unique');
            $table->index(['user_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_media_studio_accounts');
    }
};
