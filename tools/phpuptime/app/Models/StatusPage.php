<?php

namespace App\Models;

use App\Traits\Webhookable;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class StatusPage extends Model
{
    use Webhookable;

    /**
     * Get the user that owns the status page.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the status page's monitors.
     */
    public function monitors(): BelongsToMany
    {
        return $this->belongsToMany(Monitor::class)->withPivot('order')->orderByPivot('order', 'asc');
    }

    /**
     * Scope a query to filter results by a partial match on the name.
     */
    public function scopeSearchName(Builder $query, string $value): Builder
    {
        return $query->where('name', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to include results of a given user.
     */
    public function scopeOfUser(Builder $query, string|int $value): Builder
    {
        return $query->where('user_id', '=', $value);
    }

    /**
     * Get the status page's URL.
     */
    public function getUrlAttribute(): string
    {
        return $this->domain ? config('settings.domain_protocol') . '://' . $this->domain : route('status_pages.show', ['id' => $this->slug]);
    }

    /**
     * Get the status page's logo URL.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return Storage::disk(config('settings.storage_driver'))->url('users/'. $this->user_id .'/status-pages/' . $this->id . '/' . $this->logo);
        }

        return $this->logo;
    }

    /**
     * Get the status page's favicon URL.
     */
    public function faviconUrlAttribute(): string
    {
        if ($this->favicon) {
            return Storage::disk(config('settings.storage_driver'))->url('users/'. $this->user_id .'/status-pages/' . $this->id . '/' . $this->favicon);
        }

        return $this->favicon;
    }

    /**
     * Get the status page's display URL.
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
     * Set and encrypt status page's password.
     */
    public function setPasswordAttribute(?string $value): void
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get the decrypted status page's password.
     */
    public function getPasswordAttribute(?string $value): ?string
    {
        try {
            return Crypt::decryptString($value);
        } catch (Exception) {}

        return null;
    }

    /**
     * Determine if the status page's privacy is public.
     */
    public function isPublic(): bool
    {
        return $this->privacy == 0;
    }

    /**
     * Determine if the status page's privacy is private.
     */
    public function isPrivate(): bool
    {
        return $this->privacy == 1;
    }

    /**
     * Determine if the status page's privacy is password protected.
     */
    public function isPasswordProtected(): bool
    {
        return $this->privacy == 2;
    }

    /**
     * Define the status page's password session key.
     */
    public function passwordSessionKey(): string
    {
        return 'status_page_' . $this->id;
    }
}
