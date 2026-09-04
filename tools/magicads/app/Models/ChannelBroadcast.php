<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A composed broadcast and its per-destination delivery targets.
 */
class ChannelBroadcast extends Model
{
    protected $table = 'channel_broadcasts';

    protected $guarded = [];

    protected $casts = [
        'scheduled_at'              => 'datetime',
        'recurrence_start_at'       => 'datetime',
        'recurrence_end_at'         => 'datetime',
        'recurrence_interval_minutes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function adCopy()
    {
        return $this->belongsTo(AdCopy::class);
    }

    public function adCreative()
    {
        return $this->belongsTo(AdCreative::class);
    }

    public function targets()
    {
        return $this->hasMany(ChannelBroadcastTarget::class, 'broadcast_id');
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function hasMedia(): bool
    {
        return ! empty($this->media_path);
    }

    /**
     * Recompute the rollup status from the child targets and persist it.
     * partial = some sent + some failed; completed = all sent.
     */
    public function syncStatus(): void
    {
        $targets = $this->targets()->get();

        if ($targets->isEmpty()) {
            return;
        }

        $statuses = $targets->pluck('status');
        $sent    = $statuses->filter(fn ($s) => $s === 'sent')->count();
        $failed  = $statuses->filter(fn ($s) => $s === 'failed')->count();
        $pending = $statuses->filter(fn ($s) => in_array($s, ['pending', 'scheduled', 'sending'], true))->count();

        if ($this->schedule_type === 'recurring' && $pending > 0) {
            $status = 'scheduled';
        } elseif ($pending > 0) {
            $status = $sent > 0 ? 'sending' : 'scheduled';
        } elseif ($failed === 0) {
            $status = 'completed';
        } elseif ($sent === 0) {
            $status = 'failed';
        } else {
            $status = 'partial';
        }

        if ($status !== $this->status) {
            $this->forceFill(['status' => $status])->save();
        }
    }
}
