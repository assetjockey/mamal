<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A user's saved voice for the "bring your own voice" flow in Talking Product
 * Photo. Holds either a HeyGen audio asset id (an uploaded sample used as the
 * audio source directly) and/or a cloned HeyGen voice id.
 */
class AvatarStudioVoice extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function sampleUrl(): ?string
    {
        if (! $this->sample_path) {
            return null;
        }

        return Storage::disk('results')->url($this->sample_path);
    }
}
