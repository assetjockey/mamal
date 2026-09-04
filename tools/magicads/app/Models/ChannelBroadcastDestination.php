<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A user's connected broadcast destination on a channel.
 *
 * One row per connected target — a Telegram group, a Slack channel webhook, a
 * Teams channel, a WhatsApp recipient list or an email list. Channel-specific
 * credentials (chat id, webhook url, recipient list, …) live in the encrypted
 * `credentials` JSON blob so each channel can store exactly what it needs.
 */
class ChannelBroadcastDestination extends Model
{
    protected $table = 'channel_broadcast_destinations';

    protected $guarded = [];

    protected $casts = [
        'credentials'    => 'encrypted:array',
        'metadata'       => 'array',
        'status'         => 'boolean',
        'last_test_ok'   => 'boolean',
        'last_tested_at' => 'datetime',
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

    /** Channel definition from config/channel-broadcast.php. */
    public function definition(): array
    {
        return config("channel-broadcast.channels.{$this->channel}", []);
    }

    public function label(): string
    {
        return $this->definition()['label'] ?? ucfirst($this->channel);
    }

    /** A single credential value. */
    public function credential(string $key, mixed $default = null): mixed
    {
        return data_get($this->credentials, $key, $default);
    }

    /**
     * Recipient list for fan-out channels (WhatsApp / Email). Returns the
     * parsed, trimmed list of values; empty for single-target channels.
     *
     * @return array<int, string>
     */
    public function recipients(): array
    {
        $raw = $this->credential('recipients', []);

        if (is_string($raw)) {
            $raw = preg_split('/[\r\n,;]+/', $raw) ?: [];
        }

        return array_values(array_filter(array_map('trim', (array) $raw)));
    }

    /** Short, human summary of where this destination points. */
    public function endpointSummary(): string
    {
        return match ($this->channel) {
            'telegram' => (string) $this->credential('chat_id', ''),
            'slack'    => __('Webhook configured'),
            'whatsapp' => trans_choice(':count recipient|:count recipients', count($this->recipients()), ['count' => count($this->recipients())]),
            'messenger' => trans_choice(':count recipient|:count recipients', count($this->recipients()), ['count' => count($this->recipients())]),
            'email'    => trans_choice(':count subscriber|:count subscribers', count($this->recipients()), ['count' => count($this->recipients())]),
            default    => '',
        };
    }
}
