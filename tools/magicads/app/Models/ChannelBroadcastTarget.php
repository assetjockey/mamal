<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single channel delivery for a broadcast.
 */
class ChannelBroadcastTarget extends Model
{
    protected $table = 'channel_broadcast_targets';

    protected $guarded = [];

    protected $casts = [
        'next_run_at'     => 'datetime',
        'last_attempt_at' => 'datetime',
        'sent_at'         => 'datetime',
        'attempts'        => 'integer',
        'run_count'       => 'integer',
        'recipients_sent' => 'integer',
    ];

    public function broadcast()
    {
        return $this->belongsTo(ChannelBroadcast::class, 'broadcast_id');
    }

    public function destination()
    {
        return $this->belongsTo(ChannelBroadcastDestination::class, 'destination_id');
    }

    public function definition(): array
    {
        return config("channel-broadcast.channels.{$this->channel}", []);
    }

    public function label(): string
    {
        return $this->definition()['label'] ?? ucfirst(str_replace('_', ' ', $this->channel));
    }
}
