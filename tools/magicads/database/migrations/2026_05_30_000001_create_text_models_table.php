<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * text_models — the ad-copy engine/model registry.
 *
 * Replaces the static config('ad-copy.engines') array. Each row is one
 * selectable copy model belonging to a vendor (engine), carrying its driver
 * class, the admin_keys column that holds its API key, pricing tier, credit
 * cost, and the UI presentation fields.
 *
 * A "vendor" (e.g. 'openai', 'gemini', 'anthropic', 'xai') groups several
 * models. Vendor-level fields (label, driver, key_column, icon) are
 * denormalised onto every row so a single query rebuilds the whole picker
 * without a separate vendors table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('text_models', function (Blueprint $table) {
            $table->id();

            // Engine/vendor slug — formerly the config array key. Groups the
            // models shown under one engine card (e.g. 'openai').
            $table->string('vendor')->index();

            // The provider's model identifier sent to the API
            // (e.g. 'gpt-5.5', 'gemini-3.5-flash'). Unique per vendor.
            $table->string('model_id');

            // Presentation
            $table->string('label');
            $table->text('description')->nullable();

            // Vendor-level wiring (denormalised onto each model row).
            // Fully-qualified copy-driver class, e.g.
            // App\Services\AdCopy\Drivers\OpenAiCopyDriver
            $table->string('driver');

            // Vendor display label shown on the engine card (e.g. 'OpenAI GPT').
            $table->string('vendor_label')->nullable();

            // admin_keys column that stores this vendor's API key.
            $table->string('key_column');

            // Heroicon fallback name + brand tint for the engine card.
            $table->string('icon')->nullable();
            $table->string('tint')->nullable();

            // 'premium' | 'standard' | 'fast'
            $table->string('tier')->nullable();

            // Per-generation credit cost.
            $table->unsignedInteger('credit_cost')->default(1);

            // Admin enable/disable + stable ordering in the picker.
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            // One row per (vendor, model_id).
            $table->unique(['vendor', 'model_id']);
            $table->index(['enabled', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('text_models');
    }
};
