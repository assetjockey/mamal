<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A HeyGen avatar look, persisted locally so the spokesperson picker loads
 * instantly from the database instead of a live API call. Synced from HeyGen
 * by AvatarStudioCatalog::sync().
 */
class AvatarStudioAvatar extends Model
{
    protected $guarded = [];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
