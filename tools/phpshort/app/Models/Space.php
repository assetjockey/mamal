<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Space extends Model
{
    /**
     * Scope a query to filter results by a partial match on the name.
     */
    public function scopeSearchName(Builder $query, string $value): Builder
    {
        return $query->where('name', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to include results of a given user.
     */
    public function scopeOfUser(Builder $query, string|int $value): Builder
    {
        return $query->where('user_id', '=', $value);
    }

    /**
     * Get the links under the space.
     */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    /**
     * Get the user that owns the space.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the space's total links count.
     */
    public function getTotalLinksCountAttribute(): int
    {
        return $this->hasMany(Link::class)->where('space_id', '=', $this->id)->count();
    }
}
