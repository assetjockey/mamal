<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Social Media Studio — dedicated settings table.
 *
 * Single-row, mirrors the AdminKey/ExtensionSetting pattern. Holds:
 *   - feature flags (master switch + free-tier access)
 *   - the AI caption pricing override (JSON)
 *   - per-provider publishing OAuth app credentials (encrypted via the model)
 *
 * Deliberately separate from the legacy `social_media_settings` (social login)
 * table to avoid any confusion between login OAuth and publishing OAuth.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_media_studio_settings', function (Blueprint $table) {
            $table->id();

            // Feature gating
            $table->boolean('social_media_studio_feature')->default(false);
            $table->boolean('social_media_studio_free_tier')->default(false);

            // AI caption pricing override (credits per 1000 words) — JSON for forward-compat
            $table->text('ai_pricing')->nullable();

            // Per-platform enable switches
            $table->boolean('twitter_enabled')->default(false);
            $table->boolean('linkedin_enabled')->default(false);
            $table->boolean('facebook_enabled')->default(false);
            $table->boolean('instagram_enabled')->default(false);
            $table->boolean('tiktok_enabled')->default(false);
            $table->boolean('youtube_enabled')->default(false);

            // Twitter / X (OAuth2 + PKCE)
            $table->text('twitter_client_id')->nullable();
            $table->text('twitter_client_secret')->nullable();

            // LinkedIn
            $table->text('linkedin_client_id')->nullable();
            $table->text('linkedin_client_secret')->nullable();

            // Facebook (also powers Instagram via the Graph API)
            $table->text('facebook_client_id')->nullable();
            $table->text('facebook_client_secret')->nullable();

            // Instagram (separate Meta app credentials are allowed)
            $table->text('instagram_client_id')->nullable();
            $table->text('instagram_client_secret')->nullable();

            // TikTok
            $table->text('tiktok_client_key')->nullable();
            $table->text('tiktok_client_secret')->nullable();

            // YouTube + YouTube Shorts (shared Google OAuth client)
            $table->text('youtube_client_id')->nullable();
            $table->text('youtube_client_secret')->nullable();

            $table->timestamps();
        });

        // Seed the single settings row so ::first() is always present.
        DB::table('social_media_studio_settings')->insert([
            'social_media_studio_feature'   => false,
            'social_media_studio_free_tier' => false,
            'created_at'                    => now(),
            'updated_at'                    => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('social_media_studio_settings');
    }
};
