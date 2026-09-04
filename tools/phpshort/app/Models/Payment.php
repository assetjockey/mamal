<?php

namespace App\Models;

use App\Traits\Webhookable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use Webhookable;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'product' => 'object',
        'tax_rates' => 'object',
        'coupon' => 'object',
        'customer' => 'object',
        'seller' => 'object'
    ];

    /**
     * Get the user that owns the payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the plan associated with the payment.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class)->withTrashed();
    }

    /**
     * Scope a query to filter results by a partial match on the payment ID.
     */
    public function scopeSearchPayment(Builder $query, string|int $value): Builder
    {
        return $query->where('payment_id', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to filter results by a partial match on the invoice ID.
     */
    public function scopeSearchInvoice(Builder $query, string|int $value): Builder
    {
        return $query->where([['invoice_id', 'like', '%' . $value . '%'], ['status', '<>', 'pending']]);
    }

    /**
     * Scope a query to include results of a given plan.
     */
    public function scopeOfPlan(Builder $query, string|int $value): Builder
    {
        return $query->where('plan_id', '=', $value);
    }

    /**
     * Scope a query to include results of a given user.
     */
    public function scopeOfUser(Builder $query, string|int $value): Builder
    {
        return $query->where('user_id', '=', $value);
    }

    /**
     * Scope a query to include results of a given interval.
     */
    public function scopeOfInterval(Builder $query, string $value): Builder
    {
        return $query->where('interval', '=', $value);
    }

    /**
     * Scope a query to include results of a given processor.
     */
    public function scopeOfProcessor(Builder $query, string $value): Builder
    {
        return $query->where('processor', '=', $value);
    }

    /**
     * Scope a query to include results of a given status.
     */
    public function scopeOfStatus(Builder $query, string $value): Builder
    {
        return $query->where('status', '=', $value);
    }
}
