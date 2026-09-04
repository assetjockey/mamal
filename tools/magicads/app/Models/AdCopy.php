<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdCopy extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'variants'     => 'array',
        'meta'         => 'array',
        'is_favorite'  => 'boolean',
        'credits'      => 'float',
        'words'        => 'integer',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function brand() { return $this->belongsTo(Brand::class); }
    public function project() { return $this->belongsTo(Project::class); }

    public function scopeForUser($q, int $userId) { return $q->where('user_id', $userId); }
    public function scopeFavorites($q)            { return $q->where('is_favorite', true); }
    public function scopeCompleted($q)            { return $q->where('status', 'completed'); }

    public function platformLabel(): string
    {
        return config("ad-copy.platforms.{$this->platform}.label", $this->platform);
    }

    public function platformIcon(): string
    {
        return config("ad-copy.platforms.{$this->platform}.icon", 'document-text');
    }

    public function platformTint(): string
    {
        return config("ad-copy.platforms.{$this->platform}.tint", 'zinc');
    }
}
