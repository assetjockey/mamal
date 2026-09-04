<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $guarded = [];

    protected $casts = [
        'featured' => 'boolean',
        'stars' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Only testimonials that should appear on the public landing page.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Up-to-two uppercase initials derived from the name, used as the avatar
     * fallback when no image is uploaded.
     */
    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials !== '' ? $initials : 'A';
    }

    /**
     * Public URL for the uploaded avatar, or null when none is set (so the
     * frontend can fall back to the initials chip). Passes through absolute
     * URLs and resolves stored relative paths via asset().
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (blank($this->avatar)) {
            return null;
        }

        return str_starts_with((string) $this->avatar, 'http')
            ? $this->avatar
            : asset($this->avatar);
    }
}
