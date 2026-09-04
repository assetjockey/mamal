<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title', 160)->nullable();
            $table->string('platform', 40);           // facebook_feed, tiktok, google_search, etc.
            $table->string('objective', 30)->nullable();
            $table->string('framework', 40)->nullable();
            $table->string('tone', 40)->nullable();
            $table->string('engine', 30)->default('openai');
            $table->string('model_id', 60)->nullable();
            $table->string('language', 20)->default('en');

            $table->text('product_description')->nullable();
            $table->text('target_audience')->nullable();
            $table->text('key_benefits')->nullable();
            $table->text('keywords')->nullable();
            $table->string('cta', 40)->nullable();
            $table->text('extra_instructions')->nullable();

            // Generated output — array of variants
            $table->text('variants')->nullable();  // [{fields: {...}, meta: {...}}, ...]
            $table->text('meta')->nullable();      // tokens used, model, etc.
            $table->enum('status', ['pending','processing','completed','failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->float('credits')->default(0);
            $table->unsignedInteger('words')->default(0);
            $table->boolean('is_favorite')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'platform']);
            $table->index(['user_id', 'is_favorite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_copies');
    }
};
