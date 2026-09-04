<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Avatar Studio plugin — schema + install registration.
 *
 * Ships with the "magicads-avatar-studio" plugin. Mirrors the self-contained
 * Wasabi/Fashion Studio install pattern: a single migration that
 *
 *   1. Creates the feature tables (creations + saved voices).
 *   2. Adds the plugin's settings columns to the shared single-row
 *      extension_settings table (master switch, free tier, the admin's
 *      encrypted HeyGen key, defaults and the per-feature pricing matrix).
 *   3. Adds the `avatar_studio_feature` per-plan toggle to the plans table so
 *      it can be granted exactly like fashion_studio_feature.
 *   4. Registers the marketplace `extensions` row as installed so
 *      HelperService::extensionAvatarStudio() can gate visibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---- Generated avatar videos (spokesperson / talking photo / localized) ----
        if (! Schema::hasTable('avatar_studio_creations')) {
            Schema::create('avatar_studio_creations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->index();
                // spokesperson | talking_photo | localizer
                $table->string('type', 30)->default('spokesperson');
                $table->string('title')->nullable();
                $table->text('script')->nullable();

                // HeyGen selections (avatar/voice ids, language list, etc.) and
                // the full request snapshot so the stateless poller can resume.
                $table->text('source_files')->nullable();   // JSON: uploaded photo/video/audio paths
                $table->text('meta')->nullable();            // JSON: request snapshot + options

                // Async lifecycle — mirrors ad_creatives.
                $table->string('status', 20)->default('pending'); // pending|processing|completed|failed
                $table->string('provider_job_id')->nullable();    // HeyGen video_id / translation_id
                $table->string('result_path')->nullable();        // results-disk relative path
                $table->string('mime_type', 50)->nullable();
                $table->unsignedInteger('credits')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('last_polled_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedSmallInteger('poll_count')->default(0);
                $table->boolean('is_favorite')->default(false);
                $table->timestamps();

                $table->index(['user_id', 'type']);
                $table->index(['status', 'provider_job_id']);
            });
        }

        // ---- Saved voices (cloned / recorded for "bring your own voice") ----
        if (! Schema::hasTable('avatar_studio_voices')) {
            Schema::create('avatar_studio_voices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->index();
                $table->string('name')->nullable();
                // HeyGen voice id once cloned, or null while only an audio asset exists.
                $table->string('heygen_voice_id')->nullable();
                // HeyGen audio asset id (uploaded sample) — used as audio source directly.
                $table->string('audio_asset_id')->nullable();
                $table->string('sample_path')->nullable();   // results-disk copy of the sample
                $table->string('status', 20)->default('ready'); // ready|processing|failed
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }

        // ---- extension_settings columns ----
        Schema::table('extension_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('extension_settings', 'avatar_studio_feature')) {
                $table->boolean('avatar_studio_feature')->default(false);
            }
            if (! Schema::hasColumn('extension_settings', 'avatar_studio_free_tier')) {
                $table->boolean('avatar_studio_free_tier')->default(false);
            }
            // The admin's HeyGen API key — encrypted at the model layer.
            if (! Schema::hasColumn('extension_settings', 'heygen_api_key')) {
                $table->text('heygen_api_key')->nullable();
            }
            // Translation fidelity: 'speed' | 'precision'.
            if (! Schema::hasColumn('extension_settings', 'avatar_studio_translate_mode')) {
                $table->string('avatar_studio_translate_mode', 12)->default('speed');
            }
            if (! Schema::hasColumn('extension_settings', 'avatar_studio_max_upload_mb')) {
                $table->unsignedInteger('avatar_studio_max_upload_mb')->default(50);
            }
            // Per-feature pricing matrix override (JSON).
            if (! Schema::hasColumn('extension_settings', 'avatar_studio_pricing')) {
                $table->text('avatar_studio_pricing')->nullable();
            }
        });

        // ---- plans column (per-plan grant, like fashion_studio_feature) ----
        if (Schema::hasTable('plans') && ! Schema::hasColumn('plans', 'avatar_studio_feature')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->boolean('avatar_studio_feature')->nullable()->default(0);
            });
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('avatar_studio_creations');
        Schema::dropIfExists('avatar_studio_voices');

        Schema::table('extension_settings', function (Blueprint $table) {
            $table->dropColumn([
                'avatar_studio_feature',
                'avatar_studio_free_tier',
                'heygen_api_key',
                'avatar_studio_translate_mode',
                'avatar_studio_max_upload_mb',
                'avatar_studio_pricing',
            ]);
        });

        if (Schema::hasTable('plans') && Schema::hasColumn('plans', 'avatar_studio_feature')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('avatar_studio_feature');
            });
        }

    }
};
