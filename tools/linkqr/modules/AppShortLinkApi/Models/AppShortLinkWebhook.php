<?php

namespace Modules\AppShortLinkApi\Models;

use Illuminate\Database\Eloquent\Model;

class AppShortLinkWebhook extends Model
{
    protected $fillable = ['owner_user_id', 'team_id', 'name', 'url', 'events', 'secret', 'is_active', 'last_delivered_at'];

    protected function casts(): array
    {
        return [
            'owner_user_id' => 'integer',
            'team_id' => 'integer',
            'events' => 'array',
            'is_active' => 'boolean',
            'last_delivered_at' => 'datetime',
        ];
    }
}
