<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feature_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('image_studio_feature')->nullable()->default(1);
            $table->boolean('video_studio_feature')->nullable()->default(1);
            $table->boolean('copy_studio_feature')->nullable()->default(1);
            $table->boolean('image_studio_free_tier')->nullable()->default(1);
            $table->boolean('video_studio_free_tier')->nullable()->default(1);
            $table->boolean('copy_studio_free_tier')->nullable()->default(1);            
            $table->string('default_image_model')->nullable();
            $table->string('default_video_model')->nullable();
            $table->string('default_copy_engine')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_settings');
    }
};
