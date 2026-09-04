<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A composed Social Media Studio post and its per-network delivery targets.
 */
class SocialMediaStudioPost extends Model
{
    protected $table = 'social_media_studio_posts';

    protected $guarded = [];

    protected $casts = [
        'scheduled_at'             => 'datetime',
        'repost_start_at'          => 'datetime',
        'repost_end_at'            => 'datetime',
        'repost_interval_minutes'  => 'integer',
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
        return $this->hasMany(SocialMediaStudioTarget::class, 'post_id');
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
     * partial = some posted + some failed; completed = all posted.
     */
    public function syncStatus(): void
    {
        $targets = $this->targets()->get();

        if ($targets->isEmpty()) {
            return;
        }

        $statuses = $targets->pluck('status');
        $posted   = $statuses->filter(fn ($s) => $s === 'posted')->count();
        $failed   = $statuses->filter(fn ($s) => $s === 'failed')->count();
        $pending  = $statuses->filter(fn ($s) => in_array($s, ['pending', 'scheduled', 'publishing'], true))->count();

        // Repost posts stay "scheduled" while any target still has a future run.
        if ($this->schedule_type === 'repost' && $pending > 0) {
            $status = 'scheduled';
        } elseif ($pending > 0) {
            $status = $posted > 0 ? 'publishing' : 'scheduled';
        } elseif ($failed === 0) {
            $status = 'completed';
        } elseif ($posted === 0) {
            $status = 'failed';
        } else {
            $status = 'partial';
        }

        if ($status !== $this->status) {
            $this->forceFill(['status' => $status])->save();
        }
    }
}
