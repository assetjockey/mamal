<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExtensionSetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'extension_settings';

    public $timestamps = false;

    protected $casts = [
        'fashion_studio_pricing'     => 'array',
        'product_photoshoot_pricing' => 'array',
        // Avatar Studio plugin — per-feature pricing matrix override.
        'avatar_studio_pricing'      => 'array',
        // Avatar Studio plugin — keep the admin's HeyGen API key encrypted.
        'heygen_api_key'             => 'encrypted',
        // UGC Factory plugin — per-feature pricing matrix override.
        'ugc_factory_pricing'        => 'array',
        // UGC Factory plugin — keep the admin's fal.ai API key encrypted.
        'fal_api_key'                => 'encrypted',
        // UGC Factory plugin — optional ElevenLabs key (voice-library sync only).
        'elevenlabs_api_key'         => 'encrypted',
    ];
}
