<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ad Performance Analytics — dedicated settings table.
 *
 * Single-row, mirrors the Social Media Studio settings pattern. Holds:
 *   - feature flags (master switch + free-tier access)
 *   - the AI-insight pricing override (JSON)
 *   - per-provider OAuth app credentials (encrypted via the model)
 *
 * Also adds the `ad_analytics_feature` per-plan toggle to the plans table so
 * access can be granted exactly like social_media_studio_feature.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ad_analytics_settings')) {
            Schema::create('ad_analytics_settings', function (Blueprint $table) {
                $table->id();

                // Feature gating
                $table->boolean('ad_analytics_feature')->default(false);
                $table->boolean('ad_analytics_free_tier')->default(false);

                // AI insight pricing override — JSON for forward-compat
                $table->text('ai_pricing')->nullable();

                // Per-provider enable switches
                $table->boolean('meta_enabled')->default(false);
                $table->boolean('google_enabled')->default(false);
                $table->boolean('tiktok_enabled')->default(false);

                // Meta (Facebook/Instagram) Marketing API
                $table->text('meta_client_id')->nullable();
                $table->text('meta_client_secret')->nullable();

                // Google Ads API (OAuth client + developer token + optional MCC)
                $table->text('google_client_id')->nullable();
                $table->text('google_client_secret')->nullable();
                $table->text('google_developer_token')->nullable();
                $table->text('google_login_customer_id')->nullable();

                // TikTok Marketing API
                $table->text('tiktok_app_id')->nullable();
                $table->text('tiktok_client_secret')->nullable();

                $table->timestamps();
            });

            // Seed the single settings row so ::first() is always present.
            DB::table('ad_analytics_settings')->insert([
                'ad_analytics_feature'   => false,
                'ad_analytics_free_tier' => false,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }

        // Per-plan grant column (like social_media_studio_feature).
        if (Schema::hasTable('plans') && ! Schema::hasColumn('plans', 'ad_analytics_feature')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->boolean('ad_analytics_feature')->nullable()->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_analytics_settings');

        if (Schema::hasTable('plans') && Schema::hasColumn('plans', 'ad_analytics_feature')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('ad_analytics_feature');
            });
        }
    }
};
