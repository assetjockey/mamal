<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();

            // Core
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('content');

            // Visuals
            // featured_image is TEXT to allow either a path/URL or an inline
            // SVG data-URI (used by the demo seeder for self-contained covers).
            $table->text('featured_image')->nullable();
            $table->string('featured_image_alt')->nullable();

            // Author
            $table->string('author_name')->default('AI Ad Studio Team');
            $table->string('author_avatar')->nullable();
            $table->string('author_role')->nullable();

            // Taxonomy
            $table->string('category')->nullable()->index();
            $table->json('tags')->nullable();

            // Publication
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_featured')->default(false)->index();

            // SEO
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();

            // Stats
            $table->unsignedInteger('reading_time_minutes')->default(5);
            $table->unsignedBigInteger('view_count')->default(0);

            $table->timestamps();

            // Composite index for the most common public query
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
