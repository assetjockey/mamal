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

class Link extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'active_period_start_at' => 'datetime',
        'active_period_end_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'targets' => 'object',
    ];

    /**
     * Get the user that owns the link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the space associated with the link.
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * Get the domain associated with the link.
     */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * Get the pixels associated with the link.
     */
    public function pixels(): BelongsToMany
    {
        return $this->belongsToMany(Pixel::class);
    }

    /**
     * Get the link's stats.
     */
    public function stats(): HasMany
    {
        return $this->hasMany(Stat::class);
    }

    /**
     * Scope a query to filter results by a partial match on the title.
     */
    public function scopeSearchTitle(Builder $query, string $value): Builder
    {
        return $query->where('title', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to filter results by a partial match on the URL.
     */
    public function scopeSearchUrl(Builder $query, string $value): Builder
    {
        return $query->where('url', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to filter results by a partial match on the alias.
     */
    public function scopeSearchAlias(Builder $query, string $value): Builder
    {
        return $query->where('alias', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to include results of a given user.
     */
    public function scopeOfUser(Builder $query, string|int $value): Builder
    {
        return $query->where('user_id', '=', $value);
    }

    /**
     * Scope a query to include results of a given space.
     */
    public function scopeOfSpace(Builder $query, string|int $value): Builder
    {
        return $query->where('space_id', '=', $value)
            ->when(!$value, function ($query) use ($value) {
                return $query->orWhereNull('space_id');
            });
    }

    /**
     * Scope a query to include results of a given domain.
     */
    public function scopeOfDomain(Builder $query, string|int $value): Builder
    {
        return $query->where('domain_id', '=', $value);
    }

    /**
     * Scope a query to include only expired results.
     */
    public function scopeExpired(Builder $query)
    {
        return $query->where(function ($query) {
            $query->whereNotNull(['active_period_start_at', 'active_period_end_at'])
                ->where([['active_period_start_at', '>=', Carbon::now()->startOfMinute()], ['active_period_end_at', '<=', now()]]);
            })->orWhere(function ($query) {
                $query->where('clicks_limit', '>', 0)
                    ->whereColumn('clicks_count', '>=', 'clicks_limit');
            });
    }

    /**
     * Scope a query to include only active results.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->where(function ($nestedQuery) {
                $nestedQuery->whereNull(['active_period_start_at', 'active_period_end_at']);
            })
            ->orWhere(function ($query) {
                $query->where([['active_period_start_at', '>', now()], ['active_period_end_at', '>', Carbon::now()->startOfMinute()]])
                    ->orWhere([['active_period_start_at', '<', now()], ['active_period_end_at', '<', Carbon::now()->startOfMinute()]]);
            });
        })
        ->where(function ($query) {
            $query->where(function ($query) {
                $query->where('clicks_limit', '=', null)
                    ->orWhere('clicks_limit', '=', 0);
            })
            ->orWhere(function ($query) {
                $query->where('clicks_limit', '>', 0)
                    ->whereColumn('clicks_count', '<', 'clicks_limit');
            });
        });
    }

    /**
     * Set and encrypt the link's redirect password.
     */
    public function setRedirectPasswordAttribute(?string $value): void
    {
        $this->attributes['redirect_password'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get the decrypted link's redirect password.
     */
    public function getRedirectPasswordAttribute(?string $value): ?string
    {
        try {
            return Crypt::decryptString($value);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Set and encrypt link's stats page password.
     */
    public function setPasswordAttribute(?string $value): void
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get the decrypted link's stats page password.
     */
    public function getPasswordAttribute(?string $value): ?string
    {
        try {
            return Crypt::decryptString($value);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Determine if the link's privacy is public.
     */
    public function isPublic(): bool
    {
        return $this->privacy == 0;
    }

    /**
     * Determine if the link's privacy is private.
     */
    public function isPrivate(): bool
    {
        return $this->privacy == 1;
    }

    /**
     * Determine if the link's privacy is password protected.
     */
    public function isPasswordProtected(): bool
    {
        return $this->privacy == 2;
    }

    /**
     * Define the link's password session key.
     */
    public function passwordSessionKey(): string
    {
        return 'link_' . $this->id;
    }

    /**
     * Get the link's display URL.
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
     * Get the link's display short URL.
     */
    public function getDisplayShortUrlAttribute(): string
    {
        return preg_replace('/^https?:\/\/(www\.)?/i', '', $this->shortUrl);
    }

    /**
     * Get the link's short URL.
     */
    public function getShortUrlAttribute(): string
    {
        return (isset($this->domain) ? (parse_url($this->domain->url, PHP_URL_HOST) == parse_url(config('app.url'), PHP_URL_HOST) ? config('app.url') : $this->domain->url) : config('app.url')) . '/' . $this->alias;
    }
}
