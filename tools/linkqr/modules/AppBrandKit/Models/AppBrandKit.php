<?php

namespace Modules\AppBrandKit\Models;

use Illuminate\Database\Eloquent\Model;

class AppBrandKit extends Model
{
    protected $fillable = [
        'owner_user_id',
        'team_id',
        'brand_name',
        'logo_url',
        'primary_color',
        'secondary_color',
        'accent_color',
        'font_family',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'owner_user_id' => 'integer',
            'team_id' => 'integer',
            'settings' => 'array',
        ];
    }
}
