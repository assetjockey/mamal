<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Channel Broadcast — singleton settings row.
 *
 * Holds the plugin's feature flags, the AI message pricing override and every
 * global channel credential (WhatsApp Cloud API token, Telegram bot token, …).
 * All secrets are encrypted at rest via the `encrypted` casts.
 *
 * Webhook-based channels (Slack, Teams) and Email need no global key — the
 * destination URL / recipient list lives on each user's own destination row.
 */
class ChannelBroadcastSetting extends Model
{
    protected $table = 'channel_broadcast_settings';

    protected $guarded = [];

    protected $casts = [
        'channel_broadcast_feature'   => 'boolean',
        'channel_broadcast_free_tier' => 'boolean',
        'ai_pricing'                  => 'array',

        'whatsapp_enabled'  => 'boolean',
        'telegram_enabled'  => 'boolean',
        'slack_enabled'     => 'boolean',
        'messenger_enabled' => 'boolean',
        'email_enabled'     => 'boolean',
    ];

    /** Convenience accessor — always returns the single settings row. */
    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }

    /** Is a given channel switched on by the admin? */
    public function channelEnabled(string $channel): bool
    {
        return match ($channel) {
            'whatsapp'  => (bool) $this->whatsapp_enabled,
            'telegram'  => (bool) $this->telegram_enabled,
            'slack'     => (bool) $this->slack_enabled,
            'messenger' => (bool) $this->messenger_enabled,
            'email'     => (bool) $this->email_enabled,
            default     => false,
        };
    }
}
