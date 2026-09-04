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
        Schema::create('cookie_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enable_cookies')->default(false);
            $table->boolean('enable_dark_mode')->default(false);
            $table->boolean('disable_page_interaction')->default(false);
            $table->boolean('hide_from_bots')->default(true);
            $table->string('consent_modal_layouts')->nullable()->default('box');
            $table->string('consent_modal_position')->nullable()->default('bottom right');
            $table->string('preferences_modal_layout')->nullable()->default('box');
            $table->string('preferences_modal_position')->nullable()->default('right');
            $table->integer('cookie_valid_days')->default(7);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cookie_settings');
    }
};
