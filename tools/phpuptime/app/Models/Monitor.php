<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Monitor extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'request_headers' => 'object',
        'alerts' => 'object',
        'ssl_information' => 'object',
        'ssl_checked_at' => 'datetime',
        'ssl_alerted_at' => 'datetime',
        'ssl_created_at' => 'datetime',
        'ssl_ends_at' => 'datetime',
        'domain_information' => 'object',
        'domain_checked_at' => 'datetime',
        'domain_alerted_at' => 'datetime',
        'domain_created_at' => 'datetime',
        'domain_ends_at' => 'datetime',
        'maintenance_start_at' => 'datetime',
        'maintenance_end_at' => 'datetime',
        'started_at' => 'datetime',
        'checked_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user that owns the monitor.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the monitor's checks.
     */
    public function checks(): HasMany
    {
        return $this->hasMany(Check::class);
    }

    /**
     * Get the status pages associated with the monitor.
     */
    public function statusPages(): BelongsToMany
    {
        return $this->belongsToMany(StatusPage::class);
    }

    /**
     * Get the monitor's incidents.
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Scope a query to filter results by a partial match on the name.
     */
    public function scopeSearchName(Builder $query, string $value): Builder
    {
        return $query->where('name', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to filter results by a partial match on the URL.
     */
    public function scopeSearchUrl(Builder $query, string $value): Builder
    {
        return $query->where('url', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to include results of a given user.
     */
    public function scopeOfUser(Builder $query, string|int $value): Builder
    {
        return $query->where('user_id', '=', $value);
    }

    /**
     * Get the monitor's display URL.
     */
    public function getDisplayUrlAttribute(): string
    {
        $url = $this->url;
        if (parse_url($url, PHP_URL_PATH) == '/') {
            $url = rtrim($url, '/');
        }

        return preg_replace('/^https?:\/\/(www\.)?/i', '', $url);
    }

    /**
     * Get the monitor's status.
     */
    public function getStatusAttribute(): string
    {
        if ($this->attributes['status'] !== 'paused') {
            if ($this->maintenance_start_at && $this->maintenance_end_at) {
                if (Carbon::now()->isBetween($this->maintenance_start_at, $this->maintenance_end_at)) {
                    return 'maintenance';
                }
            }

            return $this->attributes['status'];
        }

        return $this->attributes['status'];
    }

    /**
     * Set and encrypt monitor's password.
     */
    public function setRequestAuthPasswordAttribute(?string $value): void
    {
        $this->attributes['request_auth_password'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get the decrypted monitor's password.
     */
    public function getRequestAuthPasswordAttribute(?string $value): ?string
    {
        try {
            return Crypt::decryptString($value);
        } catch (Exception) {}

        return null;
    }
}
