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
        Schema::create('page_settings', function (Blueprint $table) {
            $table->id();

            // Privacy Policy page
            $table->longText('privacy_content')->nullable();
            $table->timestamp('privacy_updated_at')->nullable();

            // Terms of Service page
            $table->longText('terms_content')->nullable();
            $table->timestamp('terms_updated_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_settings');
    }
};
