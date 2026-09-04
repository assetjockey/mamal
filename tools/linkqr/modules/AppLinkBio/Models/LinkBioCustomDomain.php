<?php

namespace Modules\AppLinkBio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkBioCustomDomain extends Model
{
    protected $fillable = [
        'owner_user_id',
        'team_id',
        'domain',
        'default_link_bio_page_id',
        'status',
        'is_default',
        'verification_token',
        'verified_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'owner_user_id' => 'integer',
            'team_id' => 'integer',
            'default_link_bio_page_id' => 'integer',
            'is_default' => 'boolean',
            'verified_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function defaultPage(): BelongsTo
    {
        return $this->belongsTo(LinkBioPage::class, 'default_link_bio_page_id');
    }
}
