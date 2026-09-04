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
        Schema::create('social_media_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('social_media')->default(false);
            $table->boolean('facebook')->default(false);
            $table->string('facebook_api_key')->nullable();
            $table->string('facebook_api_secret')->nullable();
            $table->string('facebook_url')->nullable();
            $table->boolean('twitter')->default(false);
            $table->string('twitter_api_key')->nullable();
            $table->string('twitter_api_secret')->nullable();
            $table->string('twitter_url')->nullable();
            $table->boolean('google')->default(false);
            $table->string('google_api_key')->nullable();
            $table->string('google_api_secret')->nullable();
            $table->string('google_url')->nullable();
            $table->boolean('linkedin')->default(false);
            $table->string('linkedin_api_key')->nullable();
            $table->string('linkedin_api_secret')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_settings');
    }
};
