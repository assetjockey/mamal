<?php

namespace Modules\AppShortLinkApi\Models;

use Illuminate\Database\Eloquent\Model;

class AppShortLinkApiKey extends Model
{
    protected $fillable = ['owner_user_id', 'team_id', 'name', 'token_hash', 'last_used_at', 'is_active'];

    protected function casts(): array
    {
        return [
            'owner_user_id' => 'integer',
            'team_id' => 'integer',
            'last_used_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
