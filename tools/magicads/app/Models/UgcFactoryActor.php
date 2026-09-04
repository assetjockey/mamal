<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A reusable "actor" — the still photo whose face is animated into a talking
 * UGC video. Actors are either uploaded by the user or generated from a text
 * prompt (text-to-image on fal.ai), and persist across renders.
 */
class UgcFactoryActor extends Model
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

    /** Public URL for the actor image, served off the `results` disk. */
    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('results')->url($this->image_path);
    }
}
