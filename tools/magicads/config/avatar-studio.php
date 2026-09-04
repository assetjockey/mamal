<?php

/*
|--------------------------------------------------------------------------
| Avatar Studio — HeyGen-powered talking-avatar tools
|--------------------------------------------------------------------------
|
| Avatar Studio ships as the "magicads-avatar-studio" plugin. It wraps the
| HeyGen API to add a human-presenter layer to MagicAds creatives:
|
|   - AI Spokesperson      → stock avatar + voice reads an ad script.
|   - Talking Product Photo→ a single photo becomes a talking presenter,
|                            optionally in the user's OWN cloned/recorded voice.
|   - Ad Localizer         → translate any video into 175+ languages with the
|                            speaker's face + voice preserved and lips re-synced.
|
| Everything runs on the ADMIN's single HeyGen API key (entered on the plugin
| settings page and stored encrypted on extension_settings.heygen_api_key) —
| users never bring their own key.
|
| This file is pure presentation + default pricing data so it can be tuned
| without touching component logic. Per-feature credit costs default here and
| can be overridden by the admin (persisted to
| extension_settings.avatar_studio_pricing).
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | HeyGen API surface
    |----------------------------------------------------------------------
    | Centralised so a future API version bump is a one-file change. All
    | requests authenticate with the admin key via the `X-Api-Key` header.
    */
    'api' => [
        'base'        => env('HEYGEN_API_BASE', 'https://api.heygen.com'),
        'upload_base' => env('HEYGEN_UPLOAD_BASE', 'https://upload.heygen.com'),
        // Per-request HTTP timeouts (seconds). Deliberately kept well under
        // PHP's default max_execution_time (60s) so a slow/unreachable host
        // makes curl throw a *catchable* ConnectionException instead of PHP
        // killing the request with an uncatchable FatalError. Downloads of a
        // finished MP4 run inside the cron pipeline, which lifts the time
        // limit, so they can afford a longer window.
        'timeout'          => 20,
        'connect_timeout'  => 10,
        // The /v2/avatars catalog is large (hundreds of avatars with preview
        // image + video URLs) and noticeably slower than other endpoints, so
        // it gets its own longer budget. The fetch lifts PHP's execution limit
        // for the duration and the result is cached for an hour, so only the
        // first load pays this cost.
        'avatars_timeout'  => 45,
        'download_timeout' => 300,
    ],

    /*
    |----------------------------------------------------------------------
    | Billing — feature × engine credit matrix
    |----------------------------------------------------------------------
    | Avatar Studio has a single engine (HeyGen), so the matrix is one column
    | wide; the structure mirrors Fashion Studio so the admin grid + override
    | mechanics are identical. A user is charged the matching cell only after
    | a successful render. For the localizer the cell cost is multiplied by the
    | number of target languages requested.
    */
    'engines' => [
        'heygen' => ['label' => 'HeyGen', 'sub' => 'Avatar IV · Video Translate'],
    ],

    'pricing' => [
        // feature key => ['label' => ..., 'default' => int, 'heygen' => int]
        'spokesperson'  => ['label' => 'AI Spokesperson',       'default' => 20, 'heygen' => 20],
        'talking_photo' => ['label' => 'Talking Product Photo',  'default' => 25, 'heygen' => 25],
        'localizer'     => ['label' => 'Ad Localizer (per language)', 'default' => 15, 'heygen' => 15],
    ],

    /*
    |----------------------------------------------------------------------
    | Output orientation presets
    |----------------------------------------------------------------------
    | HeyGen renders to a pixel dimension; these map a friendly label to the
    | width/height we send as `dimension`.
    */
    'orientations' => [
        ['key' => 'portrait',  'label' => 'Portrait 9:16',  'sub' => 'Reels · TikTok · Shorts', 'width' => 720,  'height' => 1280, 'icon' => 'device-phone-mobile'],
        ['key' => 'landscape', 'label' => 'Landscape 16:9', 'sub' => 'YouTube · Web',            'width' => 1280, 'height' => 720,  'icon' => 'tv'],
        ['key' => 'square',    'label' => 'Square 1:1',      'sub' => 'Feed posts',               'width' => 1080, 'height' => 1080, 'icon' => 'square-2-stack'],
    ],

    /*
    |----------------------------------------------------------------------
    | Background presets for the spokesperson / talking photo tools
    |----------------------------------------------------------------------
    | `color` builds a solid-colour HeyGen background. Gradient is only the
    | picker swatch hint. Brand colours from the project palette.
    */
    'backgrounds' => [
        ['key' => 'studio_white', 'label' => 'Studio White',  'color' => '#F8FAFC', 'gradient' => '#FFFFFF,#E2E8F0'],
        ['key' => 'brand_indigo', 'label' => 'Brand Indigo',  'color' => '#4F46E5', 'gradient' => '#4F46E5,#0F172A'],
        ['key' => 'slate_dark',   'label' => 'Slate Dark',    'color' => '#0F172A', 'gradient' => '#0F172A,#1E293B'],
        ['key' => 'warm_amber',   'label' => 'Warm Amber',    'color' => '#F59E0B', 'gradient' => '#F59E0B,#B45309'],
        ['key' => 'soft_indigo',  'label' => 'Soft Indigo',   'color' => '#EEF2FF', 'gradient' => '#EEF2FF,#C7D2FE'],
    ],

    /*
    |----------------------------------------------------------------------
    | Script starter chips (AI Spokesperson)
    |----------------------------------------------------------------------
    */
    'script_starters' => [
        'Introduce a new product launch in an upbeat, confident tone.',
        'Announce a limited-time discount and create urgency.',
        'Explain the top three benefits of our service in plain language.',
        'Give a warm welcome to new customers and set expectations.',
        'Deliver a punchy 15-second hook for a social ad.',
    ],

    /*
    |----------------------------------------------------------------------
    | Localizer — popular target languages
    |----------------------------------------------------------------------
    | HeyGen supports 175+; this is a curated quick-pick list. The live list
    | is fetched from the API and cached, falling back to this if unavailable.
    */
    'languages' => [
        'English', 'Spanish', 'French', 'German', 'Italian', 'Portuguese',
        'Dutch', 'Polish', 'Turkish', 'Arabic', 'Hindi', 'Japanese',
        'Korean', 'Chinese', 'Russian', 'Indonesian', 'Vietnamese', 'Thai',
    ],

    /*
    |----------------------------------------------------------------------
    | Limits
    |----------------------------------------------------------------------
    */
    'limits' => [
        'script_max_chars'    => 1500,
        'photo_max_mb'        => 10,
        'audio_max_mb'        => 25,
        'video_max_mb'        => 200,
        'localizer_max_langs' => 5,
    ],
];
