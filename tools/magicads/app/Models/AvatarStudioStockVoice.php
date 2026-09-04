<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A HeyGen stock voice, persisted locally so the voice pickers load instantly
 * from the database instead of a live API call. Synced from HeyGen by
 * AvatarStudioCatalog::sync().
 */
class AvatarStudioStockVoice extends Model
{
    protected $guarded = [];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
