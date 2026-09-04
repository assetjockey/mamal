<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per successful coupon use. Created inside the same transaction that
 * records the paid Order, so a coupon is only ever marked used when the
 * checkout actually completed.
 */
class CouponRedemption extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'percentage'      => 'integer',
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'redeemed_at'     => 'datetime',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
