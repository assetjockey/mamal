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
        Schema::create('extension_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('saas_feature')->default(false);
            $table->boolean('fashion_studio_feature')->default(false);
            $table->boolean('fashion_studio_free_tier')->default(false);
            $table->string('fashion_studio_provider', 40)->default('gemini');
            $table->unsignedInteger('fashion_studio_max_upload_mb')->default(15);
            $table->text('fashion_studio_pricing')->nullable();
            $table->string('fashion_studio_openai_quality', 10)->default('medium'); 
            $table->boolean('product_photoshoot_feature')->default(false);
            $table->boolean('product_photoshoot_free_tier')->default(false);
            $table->string('product_photoshoot_provider', 40)->default('gemini');
            $table->unsignedInteger('product_photoshoot_max_upload_mb')->default(20);
            $table->text('product_photoshoot_pricing')->nullable();
            $table->string('product_photoshoot_openai_quality', 10)->default('medium');      
            $table->boolean('gift_cards_feature')->default(false);   
            $table->boolean('coupons_feature')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extension_settings');
    }
};
