<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    /**
     * Scope a query to filter results by a partial match on the name.
     */
    public function scopeSearchName(Builder $query, string $value): Builder
    {
        return $query->where('name', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to filter results by a partial match on the code.
     */
    public function scopeSearchCode(Builder $query, string|int $value): Builder
    {
        return $query->where('code', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to include results of a given type.
     */
    public function scopeOfType(Builder $query, string|int $value): Builder
    {
        return $query->where('type', '=', $value);
    }

    /**
     * Determine if the coupon is redeemable.
     */
    public function isRedeemable(): bool
    {
        return $this->type == 1;
    }
}
