<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogComment extends Model
{
    protected $guarded = [];

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('status', 'approved')
            ->orderBy('created_at');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * Gravatar URL based on email — works without uploading user avatars.
     */
    public function getAvatarUrlAttribute(): string
    {
        $hash = md5(strtolower(trim((string) $this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=80";
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $initials ?: 'A';
    }
}
