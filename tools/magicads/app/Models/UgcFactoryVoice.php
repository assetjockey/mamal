<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A selectable TTS voice for UGC Factory's "Text" mode.
 *
 * `voice_ref` is what we send to fal's ElevenLabs `voice` field — either a
 * premade voice NAME (e.g. "Rachel") or a shared-library `voice_id`. Premade
 * voices ship seeded; the shared library is pulled in by an admin sync.
 */
class UgcFactoryVoice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
