<?php

namespace App\Models;

use App\Traits\Webhookable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    use Webhookable;

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
     * Scope a query to include only global results.
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->where('user_id', '=', 0);
    }

    /**
     * Scope a query to include only private results.
     */
    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('user_id', '<>', 0);
    }

    /**
     * Get the domain's total links count.
     */
    public function getTotalLinksCountAttribute(): int
    {
        return $this->hasMany(Link::class)->where('domain_id', '=', $this->id)->count();
    }

    /**
     * Get the links associated with the domain.
     */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    /**
     * Get the user that owns the domain.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the domain's URL.
     */
    public function getUrlAttribute(): string
    {
        return config('settings.domain_protocol') . '://' .$this->name;
    }
}
