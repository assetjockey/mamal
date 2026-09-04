<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();

            // Owner. Admin-authored global prompts keep the admin's id but are
            // flagged is_global so every user can read them.
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

            $table->string('type', 10)->default('image'); // image | video
            $table->string('title', 160);
            $table->text('body');

            // true  → authored by an admin, visible to everyone
            // false → private to the owning user
            $table->boolean('is_global')->default(false);

            $table->timestamps();

            $table->index(['type', 'is_global']);
            $table->index(['user_id', 'type']);
        });

        Schema::create('prompt_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('prompt_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'prompt_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prompt_favorites');
        Schema::dropIfExists('prompts');
    }
};
