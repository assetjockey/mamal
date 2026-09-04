<?php

namespace Modules\AppLinkBioShareKit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppLinkBio\Models\LinkBioPage;

class LinkBioQrDesign extends Model
{
    protected $table = 'link_bio_qr_designs';

    protected $fillable = [
        'link_bio_page_id',
        'owner_user_id',
        'name',
        'pattern',
        'finder_shape',
        'finder_dot_shape',
        'foreground_color',
        'background_color',
        'gradient_type',
        'gradient_stops',
        'logo_url',
        'logo_background_color',
        'sticker_text',
        'sticker_font',
        'sticker_shape',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'link_bio_page_id' => 'integer',
            'owner_user_id' => 'integer',
            'gradient_stops' => 'array',
            'settings' => 'array',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(LinkBioPage::class, 'link_bio_page_id');
    }
}
