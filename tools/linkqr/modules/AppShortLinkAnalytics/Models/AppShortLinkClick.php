<?php

namespace Modules\AppShortLinkAnalytics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppShortLinks\Models\AppShortLink;

class AppShortLinkClick extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'app_short_link_id',
        'owner_user_id',
        'ip_address',
        'user_agent',
        'referer',
        'country',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'app_short_link_id' => 'integer',
            'owner_user_id' => 'integer',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function shortLink(): BelongsTo
    {
        return $this->belongsTo(AppShortLink::class, 'app_short_link_id');
    }
}
