<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FashionStudioPhotoshoot extends Model
{
    protected $guarded = [];

    protected $casts = [
        'source_files' => 'array',
        'meta'         => 'array',
        'is_video'     => 'boolean',
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

    /**
     * Public URL for the generated result. Stored on the `results` disk
     * (public/results/...) exactly like AdCreative, so it is served by the
     * web server with no symlink involvement.
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
        return ucwords(str_replace('_', ' ', (string) $this->type));
    }
}
