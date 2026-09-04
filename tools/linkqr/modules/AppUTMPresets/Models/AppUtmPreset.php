<?php

namespace Modules\AppUTMPresets\Models;

use Illuminate\Database\Eloquent\Model;

class AppUtmPreset extends Model
{
    protected $fillable = [
        'owner_user_id',
        'team_id',
        'name',
        'campaign_key',
        'source',
        'medium',
        'campaign',
        'term',
        'content',
        'is_default',
        'auto_apply',
    ];

    protected function casts(): array
    {
        return [
            'owner_user_id' => 'integer',
            'team_id' => 'integer',
            'is_default' => 'boolean',
            'auto_apply' => 'boolean',
        ];
    }
}
