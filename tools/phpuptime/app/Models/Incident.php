<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'acknowledged_at' => 'datetime',
        'ended_at' => 'datetime',
        'started_at' => 'datetime',
        'alerted' => 'object'
    ];

    /**
     * Get the user that owns the incident.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the monitor associated with the incident.
     */
    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }

    /**
     * Scope a query to filter results by a partial match on the cause.
     */
    public function scopeSearchCause(Builder $query, string $value): Builder
    {
        return $query->where('cause', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to filter results by a partial match on the comment.
     */
    public function scopeSearchComment(Builder $query, string $value): Builder
    {
        return $query->where('comment', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to include results of a given user.
     */
    public function scopeOfUser(Builder $query, string|int $value): Builder
    {
        return $query->where('user_id', '=', $value);
    }

    /**
     * Scope a query to include results of a given monitor.
     */
    public function scopeOfMonitor(Builder $query, string|int $value): Builder
    {
        return $query->where('monitor_id', '=', $value);
    }

    /**
     * Scope a query to include results of a given status.
     */
    public function scopeOfStatus(Builder $query, string $value): Builder
    {
        if ($value == 'unresolved') {
            return $query->whereNull('ended_at')->whereNull('acknowledged_at');
        } elseif ($value == 'acknowledged') {
            return $query->whereNotNull('acknowledged_at')->whereNull('ended_at');
        } elseif ($value == 'resolved') {
            return $query->whereNotNull('ended_at');
        }

        return $query;
    }

    /**
     * Get the incident status.
     */
    public function getStatusAttribute(): string
    {
        if ($this->ended_at) {
            return "resolved";
        } elseif ($this->acknowledged_at) {
            return "acknowledged";
        }

        return "unresolved";
    }

    /**
     * Get the incident display URL.
     */
    public function getDisplayUrlAttribute(): string
    {
        $url = $this->url;
        if (parse_url($url, PHP_URL_PATH) == '/') {
            $url = rtrim($url, '/');
        }

        return preg_replace('/^https?:\/\/(www\.)?/i', '', $url);
    }
}
