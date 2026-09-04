<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

defined('ALTUMCODE') || die();

return [
    'dns_lookup' => [
        'icon' => 'fas fa-network-wired',
        'similar' => [
            'ip_lookup',
            'whois_lookup',
            'ping',
            'reverse_ip_lookup',
        ]
    ],

    'ip_lookup' => [
        'icon' => 'fas fa-search-location',
        'similar' => [
            'dns_lookup',
            'whois_lookup',
            'ping',
            'reverse_ip_lookup',
        ]
    ],

    'ssl_lookup' => [
        'icon' => 'fas fa-lock',
        'similar' => [
            'whois_lookup',
            'http_headers_lookup',
            'http2_checker',
        ]
    ],

    'whois_lookup' => [
        'icon' => 'fas fa-fingerprint',
        'similar' => [
            'dns_lookup',
            'ip_lookup',
            'ping',
            'reverse_ip_lookup',
        ]
    ],

    'ping' => [
        'icon' => 'fas fa-server',
        'similar' => [
            'dns_lookup',
            'ip_lookup',
            'whois_lookup',
            'reverse_ip_lookup',
        ]
    ],

    'website_text_extractor' => [
        'icon' => 'fas fa-file-alt',
        'similar' => [
            'meta_tags_checker',
            'url_redirect_checker',
            'google_cache_checker',
        ]
    ],

    'ttfb_checker' => [
        'icon' => 'fas fa-stopwatch',
        'similar' => [
            'http_headers_lookup',
            'http2_checker',
            'ssl_lookup',
        ]
    ],

    'website_page_size_checker' => [
        'icon' => 'fas fa-weight-hanging',
        'similar' => [
            'ttfb_checker',
            'http_headers_lookup',
            'brotli_checker',
        ]
    ],

    'meta_tags_checker' => [
        'icon' => 'fas fa-external-link-alt',
        'similar' => [
            'http_headers_lookup',
            'url_redirect_checker',
            'website_hosting_checker',
        ]
    ],

    'website_hosting_checker' => [
        'icon' => 'fas fa-server',
        'similar' => [
            'ping',
            'http_headers_lookup',
            'meta_tags_checker',
        ]
    ],

    'http_headers_lookup' => [
        'icon' => 'fas fa-asterisk',
        'similar' => [
            'ssl_lookup',
            'http2_checker',
            'meta_tags_checker',
        ]
    ],

    'http2_checker' => [
        'icon' => 'fas fa-satellite',
        'similar' => [
            'ssl_lookup',
            'http_headers_lookup',
        ]
    ],

    'google_cache_checker' => [
        'icon' => 'fas fa-history',
        'similar' => [
            'url_redirect_checker',
        ]
    ],

    'url_redirect_checker' => [
        'icon' => 'fas fa-directions',
        'similar' => [
            'meta_tags_checker',
            'google_cache_checker',
        ]
    ],

    'reverse_ip_lookup' => [
        'icon' => 'fas fa-book',
        'similar' => [
            'dns_lookup',
            'ip_lookup',
            'whois_lookup',
            'ping',
        ],
    ],

    'brotli_checker' => [
        'icon' => 'fas fa-compress-alt',
        'similar' => [
            'ssl_lookup',
            'http_headers_lookup',
            'http2_checker',
        ]
    ],

    'text_to_html_ratio_checker' => [
        'icon' => 'fas fa-percentage',
        'similar' => [
            'meta_tags_checker',
            'website_text_extractor',
            'http_headers_lookup',
        ]
    ],

    'noindex_checker' => [
        'icon' => 'fas fa-eye-slash',
        'similar' => [
            'meta_tags_checker',
            'website_text_extractor',
            'google_cache_checker',
            'url_redirect_checker',
        ]
    ],

    'image_alt_tags_checker' => [
        'icon' => 'fas fa-image',
        'similar' => [
            'website_text_extractor',
            'meta_tags_checker',
            'text_to_html_ratio_checker',
        ]
    ],

    'deprecated_html_tags_checker' => [
        'icon' => 'fas fa-code',
        'similar' => [
            'meta_tags_checker',
            'text_to_html_ratio_checker',
            'website_text_extractor',
        ]
    ],

    'hsts_checker' => [
        'icon' => 'fas fa-shipping-fast',
        'similar' => [
            'ssl_lookup',
            'http_headers_lookup',
            'http2_checker',
            'brotli_checker',
        ]
    ],

    'server_signature_checker' => [
        'icon' => 'fas fa-signature',
        'similar' => [
            'http_headers_lookup',
            'ssl_lookup',
            'hsts_checker',
        ]
    ],

    'favicon_checker' => [
        'icon' => 'fas fa-image',
        'similar' => [
            'meta_tags_checker',
            'website_text_extractor',
            'image_alt_tags_checker',
        ]
    ],

    'safe_url_checker' => [
        'icon' => 'fab fa-google',
        'similar' => [
            'url_redirect_checker',
            'ssl_lookup',
            'google_cache_checker',
        ]
    ],

    'lazy_loading_images_checker' => [
        'icon' => 'fas fa-images',
        'similar' => [
            'image_alt_tags_checker',
            'website_page_size_checker',
            'text_to_html_ratio_checker',
            'meta_tags_checker',
            'deprecated_html_tags_checker',
        ]
    ],

    'google_search_preview' => [
        'icon' => 'fab fa-google',
        'similar' => [
            'bing_search_preview',
            'yandex_search_preview',
        ]
    ],

    'bing_search_preview' => [
        'icon' => 'fab fa-microsoft',
        'similar' => [
            'google_search_preview',
            'yandex_search_preview',
        ]
    ],

    'yandex_search_preview' => [
        'icon' => 'fab fa-yandex',
        'similar' => [
            'google_search_preview',
            'bing_search_preview',
        ]
    ],

    'opengraph_checker' => [
        'icon' => 'fas fa-images',
        'similar' => [
            'meta_tags_checker',
            'image_alt_tags_checker',
            'google_search_preview',
            'favicon_checker',
        ]
    ],

    'html_minifier' => [
        'icon' => 'fab fa-html5',
        'similar' => [
            'css_minifier',
            'js_minifier'
        ]
    ],

    'css_minifier' => [
        'icon' => 'fab fa-css3',
        'similar' => [
            'html_minifier',
            'js_minifier'
        ]
    ],

    'js_minifier' => [
        'icon' => 'fab fa-js',
        'similar' => [
            'html_minifier',
            'css_minifier'
        ]
    ],
];

