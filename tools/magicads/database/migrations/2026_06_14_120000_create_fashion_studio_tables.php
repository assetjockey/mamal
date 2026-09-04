<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fashion Studio extension — schema + install registration.
 *
 * Creates the three feature tables (photoshoots, wardrobe items, asset
 * library), adds the feature flag + defaults to the shared
 * `extension_settings` row, and registers the marketplace `extensions`
 * row so HelperService::extensionFashionStudio() can gate visibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---- Generated photoshoots / videos / edits ----------------------
        Schema::create('fashion_studio_photoshoots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('type', 40)->default('photoshoot');   // photoshoot|virtual_try_on|change_model|change_style|edit_image|create_video
            $table->string('title')->nullable();
            $table->text('prompt')->nullable();
            $table->string('provider', 40)->default('gemini');   // gemini|openai
            $table->text('source_files')->nullable();
            $table->string('result_path')->nullable();
            $table->string('status', 20)->default('completed');
            $table->boolean('is_video')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->text('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'is_favorite']);
        });

        // ---- Wardrobe (products / garments) ------------------------------
        Schema::create('fashion_studio_wardrobe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('name')->nullable();
            $table->string('category', 60)->nullable();
            $table->string('image_path');
            $table->string('source', 20)->default('uploaded');   // uploaded|created
            $table->text('description')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'category']);
        });

        // ---- Asset library (reusable models / poses / backgrounds) -------
        Schema::create('fashion_studio_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('kind', 20);                          // model|pose|background
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->string('source', 20)->default('uploaded');
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'kind']);
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('fashion_studio_photoshoots');
        Schema::dropIfExists('fashion_studio_wardrobe_items');
        Schema::dropIfExists('fashion_studio_assets');
    }
};
