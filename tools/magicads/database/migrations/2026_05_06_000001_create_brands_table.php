<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Identity
            $table->string('name', 120);
            $table->string('slug', 140)->nullable();
            $table->string('website_url', 255)->nullable();
            $table->string('industry', 60)->nullable();
            $table->string('tagline', 180)->nullable();
            $table->text('description')->nullable();

            // Assets
            $table->string('logo_path')->nullable();
            $table->string('cover_path')->nullable();

            // Colors
            $table->string('primary_color', 7)->nullable();
            $table->string('secondary_color', 7)->nullable();
            $table->string('accent_color', 7)->nullable();

            // Typography / voice
            $table->string('font_family', 60)->nullable();
            $table->string('tone_of_voice', 40)->nullable();

            // Marketing context
            $table->text('target_audience')->nullable();
            $table->text('brand_values')->nullable();       // ["trust","innovation"]
            $table->text('social_handles')->nullable();     // {"instagram":"@x","tiktok":"@x"}
            $table->text('ad_platforms')->nullable();       // ["meta","google","tiktok"]
            $table->text('custom_fields')->nullable();      // free-form extras

            // Status
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
