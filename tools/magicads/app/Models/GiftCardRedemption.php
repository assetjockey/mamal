<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Authoritative log of a single gift-card redemption: which user redeemed
 * which card, and how many credits were granted at that moment.
 */
class GiftCardRedemption extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'credits'     => 'integer',
        'redeemed_at' => 'datetime',
    ];

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
