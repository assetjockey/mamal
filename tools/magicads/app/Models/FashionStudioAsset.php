<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FashionStudioAsset extends Model
{
    protected $table = 'fashion_studio_assets';

    protected $guarded = [];

    protected $casts = [
        'is_favorite' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeKind($query, string $kind)
    {
        return $query->where('kind', $kind);
    }

    /** Asset images live on the `results` disk under fashion-studio/assets. */
    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('results')->url($this->image_path);
    }
}
