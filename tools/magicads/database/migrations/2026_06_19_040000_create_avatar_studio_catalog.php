<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Avatar Studio catalog cache — persists the HeyGen avatar + voice lists to
 * the database so the user-facing tools never depend on a live (and slow)
 * HeyGen API call at page-load time.
 *
 * The tables are (re)populated by the admin "Test / Sync" action on the plugin
 * settings page and by the avatar-studio:process cron warm-up, so the pickers
 * are always instant and resilient to API timeouts.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('avatar_studio_avatars')) {
            Schema::create('avatar_studio_avatars', function (Blueprint $table) {
                $table->id();
                $table->string('avatar_id')->unique();   // HeyGen look/avatar id
                $table->string('name')->nullable();
                $table->string('gender', 20)->nullable();
                $table->text('preview_image')->nullable();
                $table->text('preview_video')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index('sort_order');
            });
        }

        if (! Schema::hasTable('avatar_studio_stock_voices')) {
            Schema::create('avatar_studio_stock_voices', function (Blueprint $table) {
                $table->id();
                $table->string('voice_id')->unique();     // HeyGen voice id
                $table->string('name')->nullable();
                $table->string('language')->nullable();
                $table->string('gender', 20)->nullable();
                $table->text('preview')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index('sort_order');
                $table->index('language');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('avatar_studio_avatars');
        Schema::dropIfExists('avatar_studio_stock_voices');
    }
};
