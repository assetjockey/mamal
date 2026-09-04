<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pixel extends Model
{
    /**
     * Scope a query to filter results by a partial match on the name.
     */
    public function scopeSearchName(Builder $query, string $value): Builder
    {
        return $query->where('name', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to include results of a given type.
     */
    public function scopeOfType(Builder $query, string $value): Builder
    {
        return $query->where('type', '=', $value);
    }

    /**
     * Scope a query to include results of a given user.
     */
    public function scopeOfUser(Builder $query, string|int $value): Builder
    {
        return $query->where('user_id', '=', $value);
    }

    /**
     * Get the user that owns the pixel.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the links associated with the pixel.
     */
    public function links(): BelongsToMany
    {
        return $this->belongsToMany(Link::class);
    }

    /**
     * Get the pixel's total links count.
     */
    public function getTotalLinksCountAttribute(): int
    {
        return $this->belongsToMany(Link::class)->count();
    }
}
