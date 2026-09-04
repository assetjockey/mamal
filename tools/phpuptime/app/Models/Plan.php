<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'items' => 'object',
        'tax_rates' => 'object',
        'coupons' => 'object',
        'features' => 'object'
    ];

    /**
     * Scope a query to filter results by a partial match on name.
     */
    public function scopeSearchName(Builder $query, string $value): Builder
    {
        return $query->where('name', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to include results of a given visibility.
     */
    public function scopeOfVisibility(Builder $query, string|int $value): Builder
    {
        return $query->where('visibility', '=', $value);
    }

    /**
     * Scope a query to exclude the default plan.
     */
    public function scopeNotDefault(Builder $query): Builder
    {
        return $query->where('id', '>', 1);
    }

    /**
     * Determine if the plan is the default plan.
     */
    public function isDefault(): bool
    {
        return $this->id == 1;
    }

    /**
     * Determine if the plan is priced.
     */
    public function isPriced(): bool
    {
        return $this->amount_month > 0 && $this->amount_year > 0;
    }
}
