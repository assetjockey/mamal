<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductPhotoshootProduct extends Model
{
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

    /** Product images live on the `results` disk under product-photoshoot/products. */
    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('results')->url($this->image_path);
    }
}
