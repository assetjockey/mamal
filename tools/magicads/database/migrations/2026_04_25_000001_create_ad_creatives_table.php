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
        Schema::create('ad_creatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['image', 'video']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('provider', 30);
            $table->string('model_id', 100)->nullable();
            $table->text('prompt');
            $table->string('preset_slug', 30);
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');
            $table->unsignedTinyInteger('duration')->nullable();
            $table->string('file_path')->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->unsignedInteger('credits')->default(0);
            $table->text('brand_kit_snapshot')->nullable();
            $table->text('generation_meta')->nullable();
            $table->text('error_message')->nullable();
            $table->string('provider_job_id')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('last_polled_at')->nullable();
            $table->unsignedSmallInteger('poll_count')->default(0);
            $table->string('storage_disk', 20)->nullable()->default('local');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_creatives');
    }
};
