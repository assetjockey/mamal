<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * UGC Factory plugin — schema + install registration.
 *
 * Ships with the "magicads-ugc-factory" plugin. Mirrors the self-contained
 * Avatar Studio install pattern: a single migration that
 *
 *   1. Creates the feature tables (creations + saved actors).
 *   2. Adds the plugin's settings columns to the shared single-row
 *      extension_settings table (master switch, free tier, the admin's
 *      encrypted fal.ai key, render mode and the per-feature pricing matrix).
 *   3. Adds the `ugc_factory_feature` per-plan toggle to the plans table so it
 *      can be granted exactly like avatar_studio_feature.
 *   4. Registers the marketplace `extensions` row as installed so
 *      HelperService::extensionUgcFactory() can gate visibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---- Generated UGC videos ----
        if (! Schema::hasTable('ugc_factory_creations')) {
            Schema::create('ugc_factory_creations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->index();
                // ugc_video (talking video) — kept generic for future tools.
                $table->string('type', 30)->default('ugc_video');
                $table->string('title')->nullable();
                $table->text('script')->nullable();

                // Uploaded/derived source paths (actor image, audio) and the
                // full request snapshot so the stateless poller can resume.
                $table->text('source_files')->nullable();   // JSON
                $table->text('meta')->nullable();            // JSON

                // Async lifecycle — mirrors avatar_studio_creations.
                $table->string('status', 20)->default('pending'); // pending|processing|completed|failed
                $table->string('provider_job_id')->nullable();    // fal.ai status URL
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

        // ---- Saved actors (uploaded or AI-generated) ----
        if (! Schema::hasTable('ugc_factory_actors')) {
            Schema::create('ugc_factory_actors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->index();
                $table->string('name')->nullable();
                // 'upload' | 'generated'
                $table->string('source', 20)->default('upload');
                $table->string('image_path');                 // results-disk relative path
                $table->text('prompt')->nullable();            // for generated actors
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
            });
        }

        // ---- extension_settings columns ----
        Schema::table('extension_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('extension_settings', 'ugc_factory_feature')) {
                $table->boolean('ugc_factory_feature')->default(false);
            }
            if (! Schema::hasColumn('extension_settings', 'ugc_factory_free_tier')) {
                $table->boolean('ugc_factory_free_tier')->default(false);
            }
            // The admin's fal.ai API key — encrypted at the model layer. Optional:
            // when blank the client falls back to admin_keys.fal_key.
            if (! Schema::hasColumn('extension_settings', 'fal_api_key')) {
                $table->text('fal_api_key')->nullable();
            }
            // Render fidelity: 'quality' | 'fast'.
            if (! Schema::hasColumn('extension_settings', 'ugc_factory_render_mode')) {
                $table->string('ugc_factory_render_mode', 12)->default('quality');
            }
            if (! Schema::hasColumn('extension_settings', 'ugc_factory_max_upload_mb')) {
                $table->unsignedInteger('ugc_factory_max_upload_mb')->default(10);
            }
            // Per-feature pricing matrix override (JSON).
            if (! Schema::hasColumn('extension_settings', 'ugc_factory_pricing')) {
                $table->text('ugc_factory_pricing')->nullable();
            }
        });


        // ---- plans column (per-plan grant, like avatar_studio_feature) ----
        if (Schema::hasTable('plans') && ! Schema::hasColumn('plans', 'ugc_factory_feature')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->boolean('ugc_factory_feature')->nullable()->default(0);
            });
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('ugc_factory_creations');
        Schema::dropIfExists('ugc_factory_actors');

        Schema::table('extension_settings', function (Blueprint $table) {
            foreach ([
                'ugc_factory_feature',
                'ugc_factory_free_tier',
                'fal_api_key',
                'ugc_factory_render_mode',
                'ugc_factory_max_upload_mb',
                'ugc_factory_pricing',
            ] as $column) {
                if (Schema::hasColumn('extension_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasTable('plans') && Schema::hasColumn('plans', 'ugc_factory_feature')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('ugc_factory_feature');
            });
        }

    }
};
