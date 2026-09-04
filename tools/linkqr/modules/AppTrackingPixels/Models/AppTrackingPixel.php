<?php

namespace Modules\AppTrackingPixels\Models;

use Illuminate\Database\Eloquent\Model;

class AppTrackingPixel extends Model
{
    protected $fillable = [
        'owner_user_id',
        'team_id',
        'name',
        'provider',
        'pixel_id',
        'status',
        'is_default',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'owner_user_id' => 'integer',
            'team_id' => 'integer',
            'is_default' => 'boolean',
            'settings' => 'array',
        ];
    }
}
