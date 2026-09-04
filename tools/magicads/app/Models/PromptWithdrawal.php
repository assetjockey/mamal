<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A seller's request to cash out part of their marketplace wallet balance.
 *
 * Funds are held the moment the request is created (the user's wallet is
 * debited and the amount parked here), so the balance can't be double-spent
 * while an admin settles the payout manually.
 */
class PromptWithdrawal extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'request_id';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /** Statuses where the held funds are still committed to this request. */
    public function isHeld(): bool
    {
        return in_array($this->status, ['pending', 'approved'], true);
    }
}
