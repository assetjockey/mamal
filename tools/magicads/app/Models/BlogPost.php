<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'reading_time_minutes' => 'integer',
    ];

    /**
     * Auto-generate slug if blank, append 5-char random suffix to keep it
     * URL-safe and avoid collisions on re-publish/title-change cycles.
     */
    protected static function booted(): void
    {
        static::saving(function (self $post) {
            if (blank($post->slug) && filled($post->title)) {
                $base = Str::slug($post->title);
                $candidate = $base;
                $i = 2;
                while (static::query()
                    ->where('slug', $candidate)
                    ->where('id', '!=', $post->id ?? 0)
                    ->exists()
                ) {
                    $candidate = $base . '-' . $i++;
                }
                $post->slug = $candidate;
            }

            // Auto-compute reading time from content if not set
            if (blank($post->reading_time_minutes) && filled($post->content)) {
                $words = str_word_count(strip_tags((string) $post->content));
                $post->reading_time_minutes = max(1, (int) ceil($words / 220));
            }
        });
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->whereNull('parent_id');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(BlogComment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->hasMany(BlogComment::class)
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc');
    }

    /* ------------------------------------------------------------------
     | Scopes
     * ------------------------------------------------------------------ */

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        // Works on both MySQL JSON columns and SQLite (used in dev/install)
        return $query->where('tags', 'like', '%"' . $tag . '"%');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }
        $like = '%' . trim($term) . '%';
        return $query->where(function ($q) use ($like) {
            $q->where('title', 'like', $like)
              ->orWhere('excerpt', 'like', $like)
              ->orWhere('meta_keywords', 'like', $like)
              ->orWhere('content', 'like', $like);
        });
    }

    /* ------------------------------------------------------------------
     | Accessors
     * ------------------------------------------------------------------ */

    public function getUrlAttribute(): string
    {
        return route('blog.show', ['slug' => $this->slug]);
    }

    public function getResolvedMetaTitleAttribute(): string
    {
        return filled($this->meta_title)
            ? $this->meta_title
            : Str::limit($this->title, 65, '');
    }

    public function getResolvedMetaDescriptionAttribute(): string
    {
        return filled($this->meta_description)
            ? $this->meta_description
            : Str::limit(strip_tags((string) $this->excerpt ?: (string) $this->content), 155, '…');
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (blank($this->featured_image)) {
            return null;
        }
        return str_starts_with((string) $this->featured_image, 'http')
            ? $this->featured_image
            : asset($this->featured_image);
    }

    public function getKeywordsArrayAttribute(): array
    {
        if (blank($this->meta_keywords)) {
            // Fall back to tags when keywords aren't set
            return is_array($this->tags) ? $this->tags : [];
        }
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->meta_keywords))));
    }

    public function getPublishedDateForSchemaAttribute(): ?string
    {
        return $this->published_at?->toIso8601String();
    }

    public function getUpdatedDateForSchemaAttribute(): ?string
    {
        return $this->updated_at?->toIso8601String();
    }

    /**
     * Atomic view count increment without triggering full model save / events.
     */
    public function incrementViews(): void
    {
        $this->newQuery()->whereKey($this->getKey())->increment('view_count');
    }
}
