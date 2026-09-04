<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxRate extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'regions' => 'object'
    ];

    /**
     * Scope a query to filter results by a partial match on name.
     */
    public function scopeSearchName(Builder $query, string $value): Builder
    {
        return $query->where('name', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to include results of a given type.
     */
    public function scopeOfType(Builder $query, string|int $value): Builder
    {
        return $query->where('type', '=', $value);
    }

    /**
     * Scope a query to include results of a given region.
     */
    public function scopeOfRegion(Builder $query, ?string $value): Builder
    {
        $query->whereNull('regions')
            ->when($value, function ($query) use ($value) {
                $query->orWhere('regions', 'like', '%' . $value . '%');
            });

        return $query;
    }
}
