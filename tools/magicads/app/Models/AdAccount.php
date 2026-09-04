<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A user's authorized ad account on an external network (Meta / Google / TikTok).
 *
 * Tokens are encrypted at rest. `external_id` is the remote ad-account
 * identifier used when pulling reports (act_<id> for Meta, customer id for
 * Google, advertiser_id for TikTok).
 */
class AdAccount extends Model
{
    protected $table = 'ad_accounts';

    protected $guarded = [];

    protected $casts = [
        'access_token'             => 'encrypted',
        'refresh_token'            => 'encrypted',
        'access_token_expires_at'  => 'datetime',
        'refresh_token_expires_at' => 'datetime',
        'last_synced_at'           => 'datetime',
        'metadata'                 => 'array',
        'status'                   => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(AdMetric::class);
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function scopeActive($q)
    {
        return $q->where('status', true);
    }

    public function scopeProvider($q, string $provider)
    {
        return $q->where('provider', $provider);
    }

    /** Provider definition from config/ad-analytics.php. */
    public function definition(): array
    {
        return config("ad-analytics.providers.{$this->provider}", []);
    }

    public function label(): string
    {
        return $this->definition()['label'] ?? ucfirst((string) $this->provider);
    }

    /** True when the access token is known to be expired. */
    public function accessTokenExpired(): bool
    {
        return $this->access_token_expires_at !== null
            && $this->access_token_expires_at->isPast();
    }

    /** Whether an automatic sync is due, honoring the min-interval throttle. */
    public function syncDue(): bool
    {
        if ($this->last_synced_at === null) {
            return true;
        }

        $interval = (int) config('ad-analytics.sync.min_interval_minutes', 180);

        return $this->last_synced_at->addMinutes($interval)->isPast();
    }
}
