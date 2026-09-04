<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * An invitation to join a team, addressed to an email (which may or may not
 * already belong to a registered user). Accepted by the invitee from within
 * the app once authenticated with a matching email.
 */
class TeamInvitation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'expires_at'  => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public static function freshToken(): string
    {
        return Str::random(64);
    }
}
