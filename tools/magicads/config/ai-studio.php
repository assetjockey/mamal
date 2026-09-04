<?php

return [
    'presets' => [
        'image' => [
            'meta' => [
                ['slug' => 'ig-feed',         'label' => 'Instagram Feed',          'width' => 1080, 'height' => 1080, 'ratio' => '1:1'],
                ['slug' => 'ig-story',        'label' => 'Instagram Story/Reel',    'width' => 1080, 'height' => 1920, 'ratio' => '9:16'],
                ['slug' => 'ig-portrait',     'label' => 'Instagram Portrait',      'width' => 1080, 'height' => 1350, 'ratio' => '4:5'],
                ['slug' => 'ig-landscape',    'label' => 'Instagram Landscape',     'width' => 1080, 'height' => 566,  'ratio' => '1.91:1'],
                ['slug' => 'ig-carousel',     'label' => 'Instagram Carousel',      'width' => 1080, 'height' => 1080, 'ratio' => '1:1'],
                ['slug' => 'ig-reel-cover',   'label' => 'Instagram Reel Cover',    'width' => 1080, 'height' => 1920, 'ratio' => '9:16'],
                ['slug' => 'fb-feed',         'label' => 'Facebook Feed',           'width' => 1200, 'height' => 628,  'ratio' => '1.91:1'],
                ['slug' => 'fb-square',       'label' => 'Facebook Square Ad',      'width' => 1080, 'height' => 1080, 'ratio' => '1:1'],
                ['slug' => 'fb-story',        'label' => 'Facebook Story',          'width' => 1080, 'height' => 1920, 'ratio' => '9:16'],
                ['slug' => 'fb-marketplace',  'label' => 'Facebook Marketplace',    'width' => 1200, 'height' => 1200, 'ratio' => '1:1'],
                ['slug' => 'fb-cover',        'label' => 'Facebook Cover',          'width' => 1640, 'height' => 856,  'ratio' => '1.91:1'],
                ['slug' => 'fb-event',        'label' => 'Facebook Event Cover',    'width' => 1920, 'height' => 1005, 'ratio' => '1.91:1'],
                ['slug' => 'th-feed',         'label' => 'Threads Feed',            'width' => 1080, 'height' => 1080, 'ratio' => '1:1'],
                ['slug' => 'th-portrait',     'label' => 'Threads Portrait',        'width' => 1080, 'height' => 1350, 'ratio' => '4:5'],
            ],
            'tiktok' => [
                ['slug' => 'tt-infeed',       'label' => 'TikTok In-Feed Ad',       'width' => 1080, 'height' => 1920, 'ratio' => '9:16'],
                ['slug' => 'tt-spark',        'label' => 'TikTok Spark Ad',         'width' => 1080, 'height' => 1920, 'ratio' => '9:16'],
                ['slug' => 'tt-topview',      'label' => 'TikTok TopView',          'width' => 1080, 'height' => 1920, 'ratio' => '9:16'],
                ['slug' => 'tt-square',       'label' => 'TikTok Square',           'width' => 1080, 'height' => 1080, 'ratio' => '1:1'],
            ],
            'x_twitter' => [
                ['slug' => 'x-single',        'label' => 'X Single Image',          'width' => 1600, 'height' => 900,  'ratio' => '16:9'],
                ['slug' => 'x-square',        'label' => 'X Square Image',          'width' => 1080, 'height' => 1080, 'ratio' => '1:1'],
                ['slug' => 'x-portrait',      'label' => 'X Portrait',              'width' => 1080, 'height' => 1350, 'ratio' => '4:5'],
                ['slug' => 'x-header',        'label' => 'X Profile Header',        'width' => 1500, 'height' => 500,  'ratio' => '3:1'],
            ],
            'linkedin' => [
                ['slug' => 'li-sponsored',    'label' => 'LinkedIn Sponsored',      'width' => 1200, 'height' => 627,  'ratio' => '1.91:1'],
                ['slug' => 'li-square',       'label' => 'LinkedIn Square',         'width' => 1080, 'height' => 1080, 'ratio' => '1:1'],
                ['slug' => 'li-message',      'label' => 'LinkedIn Message Ad',     'width' => 300,  'height' => 250,  'ratio' => '6:5'],
                ['slug' => 'li-cover',        'label' => 'LinkedIn Cover',          'width' => 1584, 'height' => 396,  'ratio' => '4:1'],
                ['slug' => 'li-company',      'label' => 'LinkedIn Company Banner', 'width' => 1128, 'height' => 191,  'ratio' => '5.91:1'],
            ],
            'pinterest' => [
                ['slug' => 'pin-standard',    'label' => 'Pinterest Standard Pin',  'width' => 1000, 'height' => 1500, 'ratio' => '2:3'],
                ['slug' => 'pin-square',      'label' => 'Pinterest Square Pin',    'width' => 1080, 'height' => 1080, 'ratio' => '1:1'],
                ['slug' => 'pin-idea',        'label' => 'Pinterest Idea Pin',      'width' => 1080, 'height' => 1920, 'ratio' => '9:16'],
                ['slug' => 'pin-long',        'label' => 'Pinterest Long Pin',      'width' => 1000, 'height' => 2100, 'ratio' => '1:2.1'],
            ],
            'snap_reddit' => [
                ['slug' => 'sc-single',       'label' => 'Snapchat Single Image',   'width' => 1080, 'height' => 1920, 'ratio' => '9:16'],
                ['slug' => 'sc-story',        'label' => 'Snapchat Story Ad',       'width' => 1080, 'height' => 1920, 'ratio' => '9:16'],
                ['slug' => 'rd-promoted',     'label' => 'Reddit Promoted Post',    'width' => 1200, 'height' => 1200, 'ratio' => '1:1'],
                ['slug' => 'rd-banner',       'label' => 'Reddit Banner',           'width' => 1200, 'height' => 628,  'ratio' => '1.91:1'],
            ],
            'youtube' => [
                ['slug' => 'yt-thumbnail',    'label' => 'YouTube Thumbnail',       'width' => 1280, 'height' => 720,  'ratio' => '16:9'],
                ['slug' => 'yt-bumper',       'label' => 'YouTube Bumper Cover',    'width' => 1920, 'height' => 1080, 'ratio' => '16:9'],
                ['slug' => 'yt-display',      'label' => 'YouTube Display Ad',      'width' => 300,  'height' => 250,  'ratio' => '6:5'],
                ['slug' => 'yt-shorts-cover', 'label' => 'YouTube Shorts Cover',    'width' => 1080, 'height' => 1920, 'ratio' => '9:16'],
                ['slug' => 'yt-channel',      'label' => 'YouTube Channel Art',     'width' => 2560, 'height' => 1440, 'ratio' => '16:9'],
            ],
            'google_ads' => [
                ['slug' => 'gads-square',     'label' => 'Google Ads Square',       'width' => 1200, 'height' => 1200, 'ratio' => '1:1'],
                ['slug' => 'gads-landscape',  'label' => 'Google Ads Landscape',    'width' => 1200, 'height' => 628,  'ratio' => '1.91:1'],
                ['slug' => 'gads-portrait',   'label' => 'Google Ads Portrait',     'width' => 960,  'height' => 1200, 'ratio' => '4:5'],
                ['slug' => 'gads-pmax',       'label' => 'Performance Max',         'width' => 1200, 'height' => 1200, 'ratio' => '1:1'],
                ['slug' => 'gads-discovery',  'label' => 'Discovery Ad',            'width' => 1200, 'height' => 628,  'ratio' => '1.91:1'],
                ['slug' => 'gads-logo',       'label' => 'Google Ads Logo',         'width' => 1200, 'height' => 300,  'ratio' => '4:1'],
            ],
            'web_display' => [
                ['slug' => 'web-leaderboard',  'label' => 'Leaderboard',            'width' => 728,  'height' => 90,   'ratio' => '8.09:1'],
                ['slug' => 'web-large-lead',   'label' => 'Large Leaderboard',      'width' => 970,  'height' => 90,   'ratio' => '10.78:1'],
                ['slug' => 'web-medium-rect',  'label' => 'Medium Rectangle',       'width' => 300,  'height' => 250,  'ratio' => '6:5'],
                ['slug' => 'web-large-rect',   'label' => 'Large Rectangle',        'width' => 336,  'height' => 280,  'ratio' => '6:5'],
                ['slug' => 'web-half-page',    'label' => 'Half Page',              'width' => 300,  'height' => 600,  'ratio' => '1:2'],
                ['slug' => 'web-skyscraper',   'label' => 'Skyscraper',             'width' => 160,  'height' => 600,  'ratio' => '4:15'],
                ['slug' => 'web-wide-sky',     'label' => 'Wide Skyscraper',        'width' => 300,  'height' => 600,  'ratio' => '1:2'],
                ['slug' => 'web-billboard',    'label' => 'Billboard',              'width' => 970,  'height' => 250,  'ratio' => '3.88:1'],
                ['slug' => 'web-mobile-bnr',   'label' => 'Mobile Banner',          'width' => 320,  'height' => 50,   'ratio' => '6.4:1'],
                ['slug' => 'web-large-mob',    'label' => 'Large Mobile Banner',    'width' => 320,  'height' => 100,  'ratio' => '3.2:1'],
            ],
            'email' => [
                ['slug' => 'email-header',    'label' => 'Email Header',            'width' => 600,  'height' => 200,  'ratio' => '3:1'],
                ['slug' => 'email-hero',      'label' => 'Email Hero',              'width' => 600,  'height' => 400,  'ratio' => '3:2'],
                ['slug' => 'email-square',    'label' => 'Email Square',            'width' => 600,  'height' => 600,  'ratio' => '1:1'],
            ],
        ],
        'video' => [
            ['slug' => 'vid-landscape', 'label' => 'Landscape 16:9',  'width' => 1920, 'height' => 1080, 'ratio' => '16:9'],
            ['slug' => 'vid-portrait',  'label' => 'Portrait 9:16',   'width' => 1080, 'height' => 1920, 'ratio' => '9:16'],
            ['slug' => 'vid-square',    'label' => 'Square 1:1',      'width' => 1080, 'height' => 1080, 'ratio' => '1:1'],
            ['slug' => 'vid-vertical',  'label' => 'Vertical 4:5',    'width' => 1080, 'height' => 1350, 'ratio' => '4:5'],
        ],
    ],

    'custom_preset_limits' => [
        'min' => 256,
        'max' => 4096,
    ],

    'video_durations' => [4, 5, 6, 8, 10, 12, 15],

    /**
     * Seedance multi-vendor settings.
     *
     * Seedance can be powered by ByteDance's direct API, fal.ai or kie.ai
     * (selected in admin AI Settings). The ByteDance direct route (Volcengine
     * Ark / BytePlus ModelArk) is region-specific — override the base URL here
     * if your ByteDance account lives on a different host (e.g. the BytePlus
     * global endpoint https://ark.ap-southeast.bytepluses.com/api/v3).
     */
    'seedance' => [
        'bytedance_base_url' => env('SEEDANCE_BYTEDANCE_BASE_URL', 'https://ark.cn-beijing.volces.com/api/v3'),
    ],

    /**
     * Kling multi-vendor settings.
     *
     * Kling can be powered by Kling AI's direct API, fal.ai or kie.ai. The
     * Kling AI direct route (Kuaishou) uses JWT auth from an access/secret key
     * pair stored as "accessKey:secretKey" in admin_keys.kling_key. Override
     * the base URL if your account lives on a different regional host (the
     * default is the Singapore/international endpoint).
     */
    'kling' => [
        'base_url' => env('KLING_BASE_URL', 'https://api-singapore.klingai.com'),
    ],

    /**
     * Video overlay (post-processing) configuration.
     *
     * After a video provider returns a finished MP4, the studio runs an
     * FFmpeg overlay pass to burn the user-typed headline / overlayText
     * and CTA into the clip with the brand colors. This sits ABOVE
     * whatever in-frame text the AI engine rendered, giving us crisp,
     * pixel-perfect text that doesn't depend on the model's spelling.
     *
     * Always-on for video by design (your decision in the studio brief):
     * the AI engine handles the visual mood, the FFmpeg layer handles
     * the literal headline + CTA. Toggle off via env if you ever need to.
     */
    'overlay' => [
        // Master switch. Disable to skip post-processing entirely and
        // ship whatever the AI engine produced.
        'enabled' => (bool) env('AI_STUDIO_OVERLAY_ENABLED', true),

        // Default font path (legacy single-font config — kept for backwards
        // compatibility with anything that reads `overlay.font_path`). The
        // headline/cta blocks below are the source of truth from now on.
        'font_path' => env('AI_STUDIO_OVERLAY_FONT', resource_path('fonts/Inter-Bold.ttf')),

        // Bundled Inter weights. Each declared file has to actually exist
        // on disk — VideoOverlayService and the check-ffmpeg command both
        // verify presence before running.
        'fonts' => [
            'bold'        => resource_path('fonts/Inter-Bold.ttf'),
            'extra_bold'  => resource_path('fonts/Inter-ExtraBold.ttf'),
            'black'       => resource_path('fonts/Inter-Black.ttf'),
        ],

        // Headline (large, top-third). Uses Inter Black for the highest
        // visual impact at large sizes.
        'headline' => [
            'font_path'    => resource_path('fonts/Inter-Black.ttf'),
            'size_ratio'   => 0.06,   // 6% of clip height → ~65px on a 1080-tall clip
            'color'        => '#FFFFFF',
            'box_color'    => '#0F172A', // brand secondary
            'box_opacity'  => 0.55,
            'box_padding'  => 18,
            'position'     => 'top', // top | center | bottom
            'top_margin_ratio' => 0.06,
        ],

        // CTA (smaller, bottom band, fades in last 25% of clip).
        // Uses Inter ExtraBold — slightly less heavy than Black so the
        // CTA pill doesn't overpower the headline visually.
        'cta' => [
            'font_path'       => resource_path('fonts/Inter-ExtraBold.ttf'),
            'size_ratio'      => 0.045,  // ~50px on a 1080-tall clip
            'color'           => '#FFFFFF',
            'background'      => '#4F46E5', // brand primary
            'background_opacity' => 0.92,
            'padding_x'       => 22,
            'padding_y'       => 10,
            'corner_radius'   => 10,
            'bottom_margin_ratio' => 0.06,
            // Fade in over the last N seconds of the clip. Null = always on.
            'fade_in_seconds' => 1.0,
        ],
    ],
];

