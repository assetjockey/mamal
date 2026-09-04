<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A coupon is a PERCENTAGE-ONLY discount applied at checkout to one-time plans
 * (prepaid / lifetime). It is never available for monthly/yearly subscriptions.
 *
 * `owner_id` controls sharing:
 *  - NULL  → open/public code: any eligible user may use it, up to
 *            max_redemptions (and per_user_limit per person, which the business
 *            rule pins to 1 — a user may use a given coupon only once).
 *  - set   → a coupon shared with one specific user by the admin: it appears on
 *            that user's profile and only they can use it.
 */
class Coupon extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'percentage'      => 'integer',
        'max_redemptions' => 'integer',
        'redeemed_count'  => 'integer',
        'per_user_limit'  => 'integer',
        'valid_from'      => 'datetime',
        'valid_until'     => 'datetime',
    ];

    /* -----------------------------------------------------------------
     | Relationships
     | ----------------------------------------------------------------- */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    /* -----------------------------------------------------------------
     | State helpers
     | ----------------------------------------------------------------- */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExhausted(): bool
    {
        return $this->redeemed_count >= $this->max_redemptions;
    }

    public function isWithinWindow(): bool
    {
        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Whether the coupon is broadly usable (ignores the specific user, which the
     * service checks separately for owner/per-user limits).
     */
    public function isRedeemable(): bool
    {
        return $this->isActive() && ! $this->isExhausted() && $this->isWithinWindow();
    }

    /* -----------------------------------------------------------------
     | Code generation
     | ----------------------------------------------------------------- */

    /**
     * Generate a formatted, uppercase code, e.g. "SAVE-4F9K2-QW7P1".
     * Ambiguous characters are omitted so codes are easy to read aloud.
     */
    public static function generateCode(
        ?string $prefix = null,
        int $segments = 3,
        int $segmentLength = 5,
        string $separator = '-'
    ): string {
        $charset = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0/O/1/I
        $segments = max(1, min($segments, 8));
        $segmentLength = max(2, min($segmentLength, 10));

        $parts = [];
        for ($s = 0; $s < $segments; $s++) {
            $part = '';
            for ($i = 0; $i < $segmentLength; $i++) {
                $part .= $charset[random_int(0, strlen($charset) - 1)];
            }
            $parts[] = $part;
        }

        $code = implode($separator, $parts);

        $prefix = trim((string) $prefix);
        if ($prefix !== '') {
            $code = Str::upper($prefix) . $separator . $code;
        }

        return $code;
    }

    /** Generate a code guaranteed not to collide with an existing row. */
    public static function generateUniqueCode(
        ?string $prefix = null,
        int $segments = 3,
        int $segmentLength = 5,
        string $separator = '-'
    ): string {
        do {
            $code = static::generateCode($prefix, $segments, $segmentLength, $separator);
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
