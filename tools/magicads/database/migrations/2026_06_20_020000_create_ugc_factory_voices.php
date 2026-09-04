<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * UGC Factory — voice catalog.
 *
 * The "Text" mode reads its voice list from this DB table (instant, no live API
 * dependency), mirroring the Avatar Studio catalog pattern. It is seeded with
 * ElevenLabs' premade voices and can be expanded to hundreds of multilingual
 * voices by an admin "Sync" against the ElevenLabs shared voice library.
 *
 * Also adds an OPTIONAL encrypted ElevenLabs API key on extension_settings —
 * only needed to sync the large shared library; generation itself still runs on
 * the fal.ai key.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ugc_factory_voices')) {
            Schema::create('ugc_factory_voices', function (Blueprint $table) {
                $table->id();
                // The value passed to fal's `voice` field — a premade NAME or a
                // shared-library voice_id. Unique so syncs upsert cleanly.
                $table->string('voice_ref')->unique();
                $table->string('name');
                $table->string('gender', 20)->nullable();
                $table->string('accent', 40)->nullable();
                // ISO 639-1 language code (e.g. en, es, fr) — drives filtering.
                $table->string('language', 10)->nullable();
                $table->string('tone', 60)->nullable();      // short UI descriptor
                $table->string('preview_url', 512)->nullable();
                $table->string('source', 20)->default('premade'); // premade | library
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['is_active', 'language']);
                $table->index(['source']);
            });
        }

        Schema::table('extension_settings', function (Blueprint $table) {
            // Optional ElevenLabs key — ONLY used to sync the shared voice
            // library. Encrypted at the model layer. Generation uses fal.
            if (! Schema::hasColumn('extension_settings', 'elevenlabs_api_key')) {
                $table->text('elevenlabs_api_key')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ugc_factory_voices');

        Schema::table('extension_settings', function (Blueprint $table) {
            if (Schema::hasColumn('extension_settings', 'elevenlabs_api_key')) {
                $table->dropColumn('elevenlabs_api_key');
            }
        });
    }
};
