<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /**
     * Scope a query to filter results by a partial match on the name.
     */
    public function scopeSearchName(Builder $query, string $value): Builder
    {
        return $query->where('name', 'like', '%' . $value . '%')->orWhere('content', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to include results of a given visibility.
     */
    public function scopeOfVisibility(Builder $query, string|int $value): Builder
    {
        return $query->where('visibility', '=', $value);
    }

    /**
     * Scope a query to include results of a given language.
     */
    public function scopeOfLanguage(Builder $query, string $value): Builder
    {
        return $query->where('language', '=', $value);
    }
}
