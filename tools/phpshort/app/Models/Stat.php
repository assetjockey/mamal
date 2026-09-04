<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stat extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the link associated with the stat.
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }

    /**
     * Scope a query to filter results by a partial match on the column.
     */
    public function scopeSearch(Builder $query, string $value, string $column): Builder
    {
        return $query->where($column, 'like', '%' . $value . '%');
    }
}
