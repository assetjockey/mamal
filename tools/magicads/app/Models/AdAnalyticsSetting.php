<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ad Performance Analytics — singleton settings row.
 *
 * Holds the plugin's feature flags, the AI-insight pricing override and every
 * provider's OAuth app credentials (encrypted at rest). Mirrors the
 * SocialMediaStudioSetting shape so the admin experience is consistent.
 */
class AdAnalyticsSetting extends Model
{
    protected $table = 'ad_analytics_settings';

    protected $guarded = [];

    protected $casts = [
        'ad_analytics_feature'   => 'boolean',
        'ad_analytics_free_tier' => 'boolean',
        'ai_pricing'             => 'array',

        'meta_enabled'   => 'boolean',
        'google_enabled' => 'boolean',
        'tiktok_enabled' => 'boolean',

        'meta_client_id'          => 'encrypted',
        'meta_client_secret'      => 'encrypted',
        'google_client_id'        => 'encrypted',
        'google_client_secret'    => 'encrypted',
        'google_developer_token'  => 'encrypted',
        'google_login_customer_id'=> 'encrypted',
        'tiktok_app_id'           => 'encrypted',
        'tiktok_client_secret'    => 'encrypted',
    ];

    /** Convenience accessor — always returns the single settings row. */
    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }

    /** Is a given provider switched on by the admin? */
    public function providerEnabled(string $provider): bool
    {
        return match ($provider) {
            'meta'   => (bool) $this->meta_enabled,
            'google' => (bool) $this->google_enabled,
            'tiktok' => (bool) $this->tiktok_enabled,
            default  => false,
        };
    }
}
