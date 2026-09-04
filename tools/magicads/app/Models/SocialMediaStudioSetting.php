<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Social Media Studio — singleton settings row.
 *
 * Holds the plugin's feature flags, the AI caption pricing override and every
 * publishing OAuth app credential. All client secrets/ids are encrypted at
 * rest. This is intentionally distinct from the legacy SocialMediaSetting
 * (which powers social *login*); this model is for *publishing*.
 */
class SocialMediaStudioSetting extends Model
{
    protected $table = 'social_media_studio_settings';

    protected $guarded = [];

    protected $casts = [
        'social_media_studio_feature'   => 'boolean',
        'social_media_studio_free_tier' => 'boolean',
        'ai_pricing'                    => 'array',

        'twitter_enabled'   => 'boolean',
        'linkedin_enabled'  => 'boolean',
        'facebook_enabled'  => 'boolean',
        'instagram_enabled' => 'boolean',
        'tiktok_enabled'    => 'boolean',
        'youtube_enabled'   => 'boolean',

        'twitter_client_id'      => 'encrypted',
        'twitter_client_secret'  => 'encrypted',
        'linkedin_client_id'     => 'encrypted',
        'linkedin_client_secret' => 'encrypted',
        'facebook_client_id'     => 'encrypted',
        'facebook_client_secret' => 'encrypted',
        'instagram_client_id'    => 'encrypted',
        'instagram_client_secret'=> 'encrypted',
        'tiktok_client_key'      => 'encrypted',
        'tiktok_client_secret'   => 'encrypted',
        'youtube_client_id'      => 'encrypted',
        'youtube_client_secret'  => 'encrypted',
    ];

    /** Convenience accessor — always returns the single settings row. */
    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }

    /** Is a given platform switched on by the admin (and its OAuth configured)? */
    public function platformEnabled(string $platform): bool
    {
        return match ($platform) {
            'twitter'        => (bool) $this->twitter_enabled,
            'linkedin'       => (bool) $this->linkedin_enabled,
            'facebook'       => (bool) $this->facebook_enabled,
            'instagram'      => (bool) $this->instagram_enabled,
            'tiktok'         => (bool) $this->tiktok_enabled,
            'youtube',
            'youtube_shorts' => (bool) $this->youtube_enabled,
            default          => false,
        };
    }
}
