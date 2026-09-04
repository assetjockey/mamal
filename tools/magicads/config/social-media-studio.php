<?php

/*
|--------------------------------------------------------------------------
| Social Media Studio — platform capabilities, AI options & billing
|--------------------------------------------------------------------------
|
| This file is the single source of truth for what each social network
| supports. The composer UI, the per-platform content-rule validator and the
| publishers all read from `platforms` so there is never a scattered
| "if ($platform === 'instagram')" check anywhere in the codebase.
|
| Pricing here covers ONLY the in-studio AI text generation (publishing is
| always free). The admin can override the AI rate from the config screen;
| overrides persist to social_media_studio_settings.ai_pricing.
|
| Plugin slug: magicads-social-media-studio
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Connectable platforms
    |----------------------------------------------------------------------
    |
    | Each entry declares:
    |   label / short          — display strings
    |   icon                   — Font Awesome brand/solid class used in previews
    |   flux_icon              — Heroicon name for Flux UI chrome
    |   color                  — brand hex (chips/badges only, never text-on-it)
    |   oauth                  — which provider auth driver to use
    |   text_only              — can publish a caption with no media
    |   requires_media         — must attach an image or video
    |   media                  — accepted creative kinds: image | video
    |   max_text               — hard caption/body limit (chars)
    |   images.max             — max images in one post (carousel/album)
    |   images.formats         — accepted image extensions
    |   images.max_mb          — per-image size ceiling
    |   video.formats          — accepted video extensions
    |   video.max_mb           — video size ceiling
    |   video.max_seconds      — duration ceiling (null = no explicit limit)
    |   pull_by_url            — platform fetches media from a public URL
    |                            (so the creative must resolve to a public/
    |                             signed URL, not a local-only path)
    |
    */
    'platforms' => [

        'twitter' => [
            'label'         => 'X (Twitter)',
            'short'         => 'X',
            'icon'          => 'fa-brands fa-x-twitter',
            'flux_icon'     => 'hashtag',
            'color'         => '#0F1419',
            'oauth'         => 'twitter',
            'text_only'     => true,
            'requires_media'=> false,
            'media'         => ['image', 'video'],
            'max_text'      => 280,
            'images'        => ['max' => 4, 'formats' => ['jpg', 'jpeg', 'png', 'webp', 'gif'], 'max_mb' => 5],
            'video'         => ['formats' => ['mp4', 'mov'], 'max_mb' => 512, 'max_seconds' => 140],
            'pull_by_url'   => false,
        ],

        'linkedin' => [
            'label'         => 'LinkedIn',
            'short'         => 'LinkedIn',
            'icon'          => 'fa-brands fa-linkedin-in',
            'flux_icon'     => 'briefcase',
            'color'         => '#0A66C2',
            'oauth'         => 'linkedin',
            'text_only'     => true,
            'requires_media'=> false,
            'media'         => ['image', 'video'],
            'max_text'      => 3000,
            'images'        => ['max' => 9, 'formats' => ['jpg', 'jpeg', 'png', 'webp', 'gif'], 'max_mb' => 10],
            'video'         => ['formats' => ['mp4', 'mov', 'webm'], 'max_mb' => 200, 'max_seconds' => 600],
            'pull_by_url'   => false,
        ],

        'facebook' => [
            'label'         => 'Facebook',
            'short'         => 'Facebook',
            'icon'          => 'fa-brands fa-facebook-f',
            'flux_icon'     => 'globe-alt',
            'color'         => '#1877F2',
            'oauth'         => 'facebook',
            'text_only'     => true,
            'requires_media'=> false,
            'media'         => ['image', 'video'],
            'max_text'      => 63206,
            'images'        => ['max' => 10, 'formats' => ['jpg', 'jpeg', 'png', 'webp', 'gif'], 'max_mb' => 10],
            'video'         => ['formats' => ['mp4', 'mov', 'webm'], 'max_mb' => 1024, 'max_seconds' => null],
            'pull_by_url'   => true,
        ],

        'instagram' => [
            'label'         => 'Instagram',
            'short'         => 'Instagram',
            'icon'          => 'fa-brands fa-instagram',
            'flux_icon'     => 'camera',
            'color'         => '#E4405F',
            'oauth'         => 'instagram',
            'text_only'     => false,
            'requires_media'=> true,
            'media'         => ['image', 'video'],
            'max_text'      => 2200,
            'images'        => ['max' => 10, 'formats' => ['jpg', 'jpeg', 'png'], 'max_mb' => 8],
            'video'         => ['formats' => ['mp4', 'mov'], 'max_mb' => 100, 'max_seconds' => 90],
            'pull_by_url'   => true,
        ],

        'tiktok' => [
            'label'         => 'TikTok',
            'short'         => 'TikTok',
            'icon'          => 'fa-brands fa-tiktok',
            'flux_icon'     => 'musical-note',
            'color'         => '#010101',
            'oauth'         => 'tiktok',
            'text_only'     => false,
            'requires_media'=> true,
            'media'         => ['image', 'video'],
            'max_text'      => 2200,
            'images'        => ['max' => 35, 'formats' => ['jpg', 'jpeg', 'webp'], 'max_mb' => 20],
            'video'         => ['formats' => ['mp4', 'mov', 'webm'], 'max_mb' => 512, 'max_seconds' => 600],
            'pull_by_url'   => true,
        ],

        'youtube' => [
            'label'         => 'YouTube',
            'short'         => 'YouTube',
            'icon'          => 'fa-brands fa-youtube',
            'flux_icon'     => 'play',
            'color'         => '#FF0000',
            'oauth'         => 'youtube',
            'text_only'     => false,
            'requires_media'=> true,
            'media'         => ['video'],
            'max_text'      => 5000,
            'images'        => ['max' => 0, 'formats' => [], 'max_mb' => 0],
            'video'         => ['formats' => ['mp4', 'mov', 'webm', 'avi', 'mkv'], 'max_mb' => 2048, 'max_seconds' => null],
            'pull_by_url'   => false,
        ],

        'youtube_shorts' => [
            'label'         => 'YouTube Shorts',
            'short'         => 'Shorts',
            'icon'          => 'fa-solid fa-bolt-lightning',
            'flux_icon'     => 'bolt',
            'color'         => '#FF0033',
            'oauth'         => 'youtube',
            'text_only'     => false,
            'requires_media'=> true,
            'media'         => ['video'],
            'max_text'      => 5000,
            'images'        => ['max' => 0, 'formats' => [], 'max_mb' => 0],
            'video'         => ['formats' => ['mp4', 'mov', 'webm'], 'max_mb' => 1024, 'max_seconds' => 180],
            'pull_by_url'   => false,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | LinkedIn API version
    |----------------------------------------------------------------------
    |
    | LinkedIn requires a LinkedIn-Version header (YYYYMM) and only keeps each
    | version active for ~12 months. Bump this when LinkedIn retires the
    | current value (symptom: "Requested version … is not active").
    |
    */
    'linkedin_version' => '202604',

    /*
    |----------------------------------------------------------------------
    | AI caption generation — billing
    |----------------------------------------------------------------------
    |
    | Publishing is free. The only billable action inside the studio is
    | generating a caption with AI. Rate is credits per 1000 words (mirrors
    | the core CopyStudio rate semantics). Admin overrides persist to
    | social_media_studio_settings.ai_pricing.
    |
    */
    'ai' => [
        'word_unit'        => 1000,
        'default_rate'     => 2,     // credits per 1000 words
        'max_words'        => 400,
    ],

    /*
    |----------------------------------------------------------------------
    | AI caption — tone options
    |----------------------------------------------------------------------
    */
    'tones' => [
        'professional' => 'Professional',
        'friendly'     => 'Friendly',
        'witty'        => 'Witty',
        'exciting'     => 'Exciting',
        'inspiring'    => 'Inspiring',
        'casual'       => 'Casual',
        'persuasive'   => 'Persuasive',
        'bold'         => 'Bold',
        'empathetic'   => 'Empathetic',
        'informative'  => 'Informative',
    ],

    /*
    |----------------------------------------------------------------------
    | Scheduling
    |----------------------------------------------------------------------
    |
    | repost cadence options surfaced in the composer for "auto-repost".
    |
    */
    'repost_days' => [
        'all'       => 'Every day',
        'weekdays'  => 'Weekdays only',
        'weekends'  => 'Weekends only',
        'even'      => 'Even days',
        'odd'       => 'Odd days',
        'monday'    => 'Mondays',
        'tuesday'   => 'Tuesdays',
        'wednesday' => 'Wednesdays',
        'thursday'  => 'Thursdays',
        'friday'    => 'Fridays',
        'saturday'  => 'Saturdays',
        'sunday'    => 'Sundays',
    ],

    /*
    |----------------------------------------------------------------------
    | Publisher worker tuning
    |----------------------------------------------------------------------
    */
    'worker' => [
        // How many minutes a target may sit in `publishing` before the worker
        // assumes it crashed mid-flight and is allowed to retry it.
        'stuck_after_minutes' => 15,
        // Max publish attempts per target before it is marked permanently failed.
        'max_attempts'        => 3,
        // How many targets to process per worker tick.
        'batch'               => 25,
    ],
];
