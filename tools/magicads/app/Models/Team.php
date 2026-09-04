<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A Team owned by a single user. Members belong to exactly one team; the owner
 * is also stored as a {@see TeamMember} row with role "owner".
 *
 * Ships with the "magicads-team" plugin.
 */
class Team extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** All membership rows (owner + members). */
    public function members()
    {
        return $this->hasMany(TeamMember::class);
    }

    /** Membership rows excluding the owner. */
    public function memberSeats()
    {
        return $this->hasMany(TeamMember::class)->where('role', 'member');
    }

    public function invitations()
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function pendingInvitations()
    {
        return $this->hasMany(TeamInvitation::class)->where('status', 'pending');
    }

    public function activities()
    {
        return $this->hasMany(TeamActivity::class);
    }

    /** Users that are members of this team (owner + members), via the pivot table. */
    public function users()
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    /** Count of occupied member seats (excludes the owner). */
    public function seatCount(): int
    {
        return $this->memberSeats()->count();
    }
}
