<?php

namespace Modules\AppShortLinks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppShortLinkAbuseReport extends Model
{
    protected $fillable = [
        'app_short_link_id',
        'reason',
        'details',
        'reporter_email',
        'ip_address',
        'user_agent',
        'status',
    ];

    public function shortLink(): BelongsTo
    {
        return $this->belongsTo(AppShortLink::class, 'app_short_link_id');
    }
}
