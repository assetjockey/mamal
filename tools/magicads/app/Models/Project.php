<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user() { return $this->belongsTo(User::class); }

    /** The associated Brand_Kit (the Brand model). */
    public function brand() { return $this->belongsTo(Brand::class); }

    public function adCopies() { return $this->hasMany(AdCopy::class); }

    public function adCreatives() { return $this->hasMany(AdCreative::class); }

    public function scopeForUser($q, int $userId) { return $q->where('user_id', $userId); }

    /** Users this Project has been shared with (team collaborators). */
    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot(['access', 'shared_by'])
            ->withTimestamps();
    }

    /**
     * Projects the given user can access: owned, or shared with them via the
     * team sharing pivot (Team plugin). Use alongside the owner-scoped queries
     * the core uses; this widens visibility without changing ownership.
     */
    public function scopeAccessibleBy($q, int $userId)
    {
        return $q->where(function ($w) use ($userId) {
            $w->where('user_id', $userId)
                ->orWhereHas('collaborators', fn ($c) => $c->where('users.id', $userId));
        });
    }

    /** Whether the given user owns or has been granted access to this Project. */
    public function isAccessibleBy(int $userId): bool
    {
        if ((int) $this->user_id === $userId) {
            return true;
        }

        return $this->collaborators()->where('users.id', $userId)->exists();
    }

    /**
     * Total associated creatives across all three surfaces:
     * Ad_Copy + image Ad_Creative + video Ad_Creative
     * (i.e. all ad copies plus all ad creatives).
     */
    public function getCreativeCountAttribute(): int
    {
        return $this->adCopies()->count() + $this->adCreatives()->count();
    }
}
