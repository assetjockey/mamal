<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSetting extends Model
{
    protected $guarded = [];

    protected $table = 'page_settings';

    protected $casts = [
        'privacy_updated_at' => 'datetime',
        'terms_updated_at' => 'datetime',
    ];
}
