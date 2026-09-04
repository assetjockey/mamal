<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A single Avatar Studio render (spokesperson / talking photo / localized
 * video). Lifecycle mirrors AdCreative: a `pending` row is created up front
 * and advanced through submit → poll → completed by the cron command
 * `avatar-studio:process` so no long-lived worker is required.
 */
class AvatarStudioCreation extends Model
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
     * (public/results/...) exactly like AdCreative, so the web server serves
     * it directly with no symlink involvement.
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
            'spokesperson'  => __('AI Spokesperson'),
            'talking_photo' => __('Talking Product Photo'),
            'localizer'     => __('Ad Localizer'),
            default         => ucwords(str_replace('_', ' ', (string) $this->type)),
        };
    }
}
