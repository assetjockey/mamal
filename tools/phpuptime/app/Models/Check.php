<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Check extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $casts = [
        'checked_at' => 'datetime'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the monitor associated with the check.
     */
    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }

    /**
     * Scope a query to filter results by a partial match on the response status code.
     */
    public function scopeSearchResponseStatusCode(Builder $query, string|int $value): Builder
    {
        return $query->where('response_status_code', 'like', '%' . $value . '%');
    }
}
