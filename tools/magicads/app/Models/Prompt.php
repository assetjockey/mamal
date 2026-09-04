<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Prompt extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_global' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'prompt_favorites')->withTimestamps();
    }

    /** Prompts of a given studio type. */
    public function scopeOfType($q, string $type)
    {
        return $q->where('type', $type);
    }

    /** Admin-authored prompts available to everyone. */
    public function scopeGlobal($q)
    {
        return $q->where('is_global', true);
    }

    /**
     * Everything a given user is allowed to see: the global library plus
     * their own private prompts.
     */
    public function scopeVisibleTo($q, int $userId)
    {
        return $q->where(function ($sub) use ($userId) {
            $sub->where('is_global', true)
                ->orWhere('user_id', $userId);
        });
    }
}
