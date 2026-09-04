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
        Schema::create('frontend_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('frontend_page')->default(true);
            $table->boolean('custom_url_enabled')->default(false);
            $table->string('custom_url')->nullable();
            $table->string('custom_css_url')->nullable();
            $table->string('custom_js_url')->nullable();
            $table->text('custom_header_code')->nullable();
            $table->text('custom_body_code')->nullable();
            $table->string('twitter', 100)->nullable();
            $table->string('facebook', 100)->nullable();
            $table->string('linkedin', 100)->nullable();
            $table->string('instagram', 100)->nullable();
            $table->string('youtube', 100)->nullable();
            $table->string('tiktok', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frontend_settings');
    }
};
