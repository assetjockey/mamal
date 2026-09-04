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
        Schema::create('admin_keys', function (Blueprint $table) {
            $table->id();
            $table->text('openai_key')->nullable();
            $table->text('gemini_key')->nullable();
            $table->text('xai_key')->nullable();
            $table->text('nova_key')->nullable();
            $table->text('anthropic_key')->nullable();
            $table->text('runway_key')->nullable();
            $table->text('kling_key')->nullable();
            $table->text('seedance_key')->nullable();
            $table->text('fal_key')->nullable();
            $table->text('flux_key')->nullable();
            $table->text('ideogram_key')->nullable();
            $table->text('recraft_key')->nullable();
            $table->text('kie_key')->nullable();
            $table->string('google_maps_api_key')->nullable();
            $table->string('google_analytics_property_id')->nullable();
            $table->string('google_analytics_service_credentials')->nullable();
            $table->string('google_analytics_tracking_id')->nullable();
            $table->string('google_recaptcha_site_key')->nullable();
            $table->string('google_recaptcha_secret_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_keys');
    }
};
