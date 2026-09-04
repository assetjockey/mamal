<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A user's authorized publishing destination on a social network.
 *
 * Tokens are encrypted at rest. `platform_id` is the remote identifier used
 * when publishing (channel id, page id, business account id, user id …).
 */
class SocialMediaStudioAccount extends Model
{
    protected $table = 'social_media_studio_accounts';

    protected $guarded = [];

    protected $casts = [
        'access_token'             => 'encrypted',
        'refresh_token'            => 'encrypted',
        'metadata'                 => 'array',
        'status'                   => 'boolean',
        'access_token_expires_at'  => 'datetime',
        'refresh_token_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function scopeActive($q)
    {
        return $q->where('status', true);
    }

    /** Platform definition from config/social-media-studio.php. */
    public function definition(): array
    {
        return config("social-media-studio.platforms.{$this->platform}", []);
    }

    public function label(): string
    {
        return $this->definition()['label'] ?? ucfirst($this->platform);
    }

    /** True when the access token is known to be expired. */
    public function accessTokenExpired(): bool
    {
        return $this->access_token_expires_at !== null
            && $this->access_token_expires_at->isPast();
    }
}
