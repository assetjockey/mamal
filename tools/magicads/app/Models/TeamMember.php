<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A single team membership. The owner of a team also has a row here with
 * role "owner"; everyone else is "member". A user has at most one row
 * (enforced by a unique index on user_id).
 */
class TeamMember extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }
}
