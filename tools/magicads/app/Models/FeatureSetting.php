<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureSetting extends Model
{
    protected $guarded = [];

    protected $table = 'feature_settings';

    protected $casts = [
        'image_studio_feature' => 'boolean',
        'video_studio_feature' => 'boolean',
        'copy_studio_feature'  => 'boolean',
        'image_studio_free_tier' => 'boolean',
        'video_studio_free_tier' => 'boolean',
        'copy_studio_free_tier'  => 'boolean',
        'image_task_cost'      => 'integer',
        'video_task_cost'      => 'integer',
        'copy_task_cost'       => 'integer',
    ];
}
