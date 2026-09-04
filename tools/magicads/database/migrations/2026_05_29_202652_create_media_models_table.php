<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * media_models — the AI provider/engine registry.
 *
 * Replaces the static config('ai-studio.providers') array. Each row is one
 * generation engine (image or video) with its driver class, key column,
 * pricing tier, capability metadata, and UI presentation fields.
 *
 * The `vendor` column is the stable slug (formerly the array key, e.g.
 * 'gemini', 'veo', 'veo-lite') used everywhere a provider is referenced:
 * AdCreative.provider, ProviderManager::driver(), the studio model pickers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_models', function (Blueprint $table) {
            $table->id();

            // Stable identifier — formerly the config array key. Used as
            // AdCreative.provider and in every provider lookup.
            $table->string('vendor')->unique();

            // Presentation
            $table->string('label');
            $table->string('sub_label')->nullable();
            $table->string('model_id')->nullable();

            // 'image' | 'video'
            $table->string('type')->index();

            // Fully-qualified driver class, e.g.
            // App\Services\AiStudio\Drivers\GeminiDriver
            $table->string('driver');

            // admin_keys column that stores this provider's API key.
            $table->string('key_field')->nullable();

            $table->text('description')->nullable();

            // Capability chips shown on the engine card. JSON array of strings.
            $table->text('tags')->nullable();

            // 'premium' | 'mid' | 'budget'
            $table->string('tier')->nullable();

            // Video: produces synchronised audio in a single pass.
            $table->boolean('audio')->default(false);

            // Video: exact clip lengths the model accepts. JSON array of ints.
            $table->text('durations')->nullable();

            // Video: largest single-call clip length (seconds).
            $table->unsignedInteger('max_duration')->nullable();

            // Per-generation credit cost.
            $table->unsignedInteger('credit_cost')->default(1);

            // Image: 'best' | 'good' | 'weak'.  Video: 'native' | 'weak'.
            // Drives the headline-warning (image) and the overlay default (video).
            $table->string('text_rendering')->nullable();

            // Image: largest side the model produces natively. Caps the
            // custom-size slider. Null for video providers.
            $table->unsignedInteger('max_resolution')->nullable();

            // Lucide-style inner SVG path markup for the engine card icon.
            $table->text('icon_svg')->nullable();

            // Marks the "Top pick" / default-selection engine for its type.
            $table->boolean('recommended')->default(false);

            // Admin enable/disable + stable ordering in the picker.
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            // 'auto' | 'low' | 'medium' | 'high' (provider-dependent).
            $table->string('image_quality')->nullable()->default('medium');

            $table->timestamps();

            $table->index(['type', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_models');
    }
};
