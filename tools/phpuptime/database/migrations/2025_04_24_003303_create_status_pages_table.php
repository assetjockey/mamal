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
        Schema::create('status_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id')->index('user_id');
            $table->string('name', 255)->index('name');
            $table->string('slug', 255)->index('slug');
            $table->string('logo', 48)->nullable();
            $table->string('favicon', 48)->nullable();
            $table->string('website_url', 255)->nullable();
            $table->string('contact_url', 255)->nullable();
            $table->tinyInteger('privacy')->nullable()->default(0);
            $table->text('password')->nullable();
            $table->string('domain', 255)->nullable()->index('domain');
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->string('meta_title', 128)->nullable();
            $table->string('meta_description', 512)->nullable();
            $table->boolean('noindex')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_pages');
    }
};
