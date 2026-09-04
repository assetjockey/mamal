<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('blog_post_id')
                ->constrained('blog_posts')
                ->cascadeOnDelete();

            // Threaded replies (one level deep is enforced in the controller)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('blog_comments')
                ->cascadeOnDelete();

            // Author (anonymous-friendly: not tied to users table)
            $table->string('name');
            $table->string('email');
            $table->string('website')->nullable();

            $table->text('content');

            // Moderation
            $table->enum('status', ['pending', 'approved', 'spam', 'rejected'])
                ->default('pending')
                ->index();

            // Spam mitigation context (not displayed)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            $table->index(['blog_post_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_comments');
    }
};
