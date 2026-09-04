<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A single entry in a team's activity feed: credit consumption by a member,
 * a credit transfer from the owner, project sharing, membership changes, etc.
 */
class TeamActivity extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'meta'    => 'array',
        'credits' => 'integer',
    ];

    // Canonical activity types.
    public const TYPE_CREDIT_CONSUME  = 'credit.consume';
    public const TYPE_CREDIT_TRANSFER = 'credit.transfer';
    public const TYPE_PROJECT_SHARE   = 'project.share';
    public const TYPE_PROJECT_UNSHARE = 'project.unshare';
    public const TYPE_MEMBER_JOIN     = 'member.join';
    public const TYPE_MEMBER_LEAVE    = 'member.leave';
    public const TYPE_MEMBER_REMOVE   = 'member.remove';
    public const TYPE_INVITE_SENT     = 'invite.sent';
    public const TYPE_INVITE_REVOKED  = 'invite.revoked';

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
