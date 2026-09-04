<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A single UGC Factory render (fal.ai VEED Fabric 1.0 talking video).
 *
 * Lifecycle mirrors AvatarStudioCreation: a `pending` row is created up front
 * and advanced through submit → poll → completed by the cron command
 * `ugc-factory:process` so no long-lived worker is required.
 */
class UgcFactoryCreation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'source_files' => 'array',
        'meta'         => 'array',
        'is_favorite'  => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Public URL for the finished MP4. Stored on the `results` disk
     * (public/results/...) so the web server serves it directly.
     */
    public function resultUrl(): ?string
    {
        if (! $this->result_path) {
            return null;
        }

        return Storage::disk('results')->url($this->result_path);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'ugc_video' => __('UGC Video'),
            default     => ucwords(str_replace('_', ' ', (string) $this->type)),
        };
    }
}
