<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Product Photoshoot extension — schema + settings + install registration.
 *
 * Creates the three feature tables (photoshoots, products, asset library),
 * adds the feature flag + defaults to the shared `extension_settings` row,
 * and registers the marketplace `extensions` row so
 * HelperService::extensionProductPhotoshoot() can gate visibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---- Generated photoshoots / edits -------------------------------
        Schema::create('product_photoshoot_shoots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('type', 40)->default('photoshoot');   // photoshoot|template|background_replace|edit_image
            $table->string('feature', 40)->nullable();           // billing feature key
            $table->string('title')->nullable();
            $table->text('prompt')->nullable();
            $table->string('provider', 40)->default('gemini');   // gemini|openai
            $table->text('source_files')->nullable();
            $table->string('result_path')->nullable();
            $table->string('status', 20)->default('completed');
            $table->boolean('is_favorite')->default(false);
            $table->text('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'is_favorite']);
        });

        // ---- Product library ---------------------------------------------
        Schema::create('product_photoshoot_products', function (Blueprint $table) {
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

        // ---- Asset library (reusable backgrounds / references) -----------
        Schema::create('product_photoshoot_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('kind', 20);                          // background|reference
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->string('source', 20)->default('uploaded');
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'kind']);
        });

        // ---- Settings columns on the shared extension_settings row --------
        Schema::table('extension_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('extension_settings', 'product_photoshoot_feature')) {
                $table->boolean('product_photoshoot_feature')->default(false);
            }
            if (! Schema::hasColumn('extension_settings', 'product_photoshoot_free_tier')) {
                $table->boolean('product_photoshoot_free_tier')->default(false);
            }
            if (! Schema::hasColumn('extension_settings', 'product_photoshoot_provider')) {
                $table->string('product_photoshoot_provider', 40)->default('gemini');
            }
            if (! Schema::hasColumn('extension_settings', 'product_photoshoot_max_upload_mb')) {
                $table->unsignedInteger('product_photoshoot_max_upload_mb')->default(20);
            }
            if (! Schema::hasColumn('extension_settings', 'product_photoshoot_pricing')) {
                $table->text('product_photoshoot_pricing')->nullable();
            }
            if (! Schema::hasColumn('extension_settings', 'product_photoshoot_openai_quality')) {
                $table->string('product_photoshoot_openai_quality', 10)->default('medium');
            }
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('product_photoshoot_shoots');
        Schema::dropIfExists('product_photoshoot_products');
        Schema::dropIfExists('product_photoshoot_assets');

        Schema::table('extension_settings', function (Blueprint $table) {
            foreach ([
                'product_photoshoot_feature',
                'product_photoshoot_free_tier',
                'product_photoshoot_provider',
                'product_photoshoot_max_upload_mb',
                'product_photoshoot_pricing',
                'product_photoshoot_openai_quality',
            ] as $column) {
                if (Schema::hasColumn('extension_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

    }
};
