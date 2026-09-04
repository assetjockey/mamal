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
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->text('languages')->nullable();
            $table->string('default_language')->nullable();
            $table->string('logo_frontend')->default('uploads/logo/logo_frontend.png');
            $table->string('logo_frontend_collapsed')->default('uploads/logo/logo_frontend_collapsed.png');
            $table->string('logo_favicon')->default('uploads/logo/favicon.ico');
            $table->string('logo_dashboard_light')->default('uploads/logo/logo_dashboard_light.png');
            $table->string('logo_dashboard_dark')->default('uploads/logo/logo_dashboard_dark.png');
            $table->string('logo_dashboard_collapsed_light')->default('uploads/logo/logo_dashboard_collapsed_light.png');
            $table->string('logo_dashboard_collapsed_dark')->default('uploads/logo/logo_dashboard_collapsed_dark.png');
            $table->string('frontend_theme')->default('default');
            $table->string('dashboard_theme')->default('default');
            $table->boolean('user_registration')->default(true);
            $table->boolean('user_registration_subscription')->default(false);
            $table->boolean('email_verification')->default(false);     
            $table->string('default_theme')->nullable()->default('light');
            $table->boolean('google_recaptcha')->default(false); 
            $table->boolean('google_analytics_homepage')->default(false); 
            $table->boolean('google_analytics_dashboard')->default(false); 
            $table->boolean('google_maps')->default(false); 
            $table->boolean('live_chat_tawk')->default(false); 
            $table->string('license_type')->nullable();
            $table->string('license')->nullable();
            $table->string('username')->nullable();
            $table->integer('default_credits')->nullable()->default(0);
            $table->string('default_storage', 40)->default('local');
            $table->unsignedSmallInteger('free_tier_project_limit')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
