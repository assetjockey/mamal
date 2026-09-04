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
    'title' => [
        'icon' => 'fas fa-heading',
        'emoji' => '🔤',
        'tests' => ['missing', 'too_short', 'too_long'],
    ],
    'meta_description' => [
        'icon' => 'fas fa-align-left',
        'emoji' => '📝',
        'tests' => ['missing', 'too_short', 'too_long'],
    ],
    'h1' => [
        'icon' => 'fas fa-heading',
        'emoji' => '📢',
        'tests' => ['missing', 'too_many', 'too_short', 'too_long'],
    ],
    'meta_keywords' => [
        'icon' => 'fas fa-tags',
        'emoji' => '🏷️',
        'tests' => ['missing'],
    ],
    'not_found' => [
        'icon' => 'fas fa-exclamation-triangle',
        'emoji' => '❌',
        'tests' => ['missing'],
    ],
    'robots' => [
        'icon' => 'fas fa-robot',
        'emoji' => '🤖',
        'tests' => ['missing', 'excluded'],
    ],
    'language' => [
        'icon' => 'fas fa-globe',
        'emoji' => '🌐',
        'tests' => ['missing'],
    ],
    'favicon' => [
        'icon' => 'fas fa-image',
        'emoji' => '🖼️',
        'tests' => ['missing'],
    ],
    'meta_robots' => [
        'icon' => 'fas fa-robot',
        'emoji' => '🤖',
        'tests' => ['excluded'],
    ],
    'meta_robots_images' => [
        'icon' => 'fas fa-id-card',
        'emoji' => '🪪',
        'tests' => ['excluded'],
    ],
    'header_robots' => [
        'icon' => 'fas fa-robot',
        'emoji' => '🤖',
        'tests' => ['excluded'],
    ],
    'header_robots_images' => [
        'icon' => 'fas fa-eye-slash',
        'emoji' => '👁️‍🗨️',
        'tests' => ['excluded'],
    ],
    'meta_refresh' => [
        'icon' => 'fas fa-redo-alt',
        'emoji' => '🔄',
        'tests' => ['existing'],
    ],
    'response_time' => [
        'icon' => 'fas fa-clock',
        'emoji' => '⏱️',
        'tests' => ['too_slow'],
    ],
    'page_size' => [
        'icon' => 'fas fa-file-alt',
        'emoji' => '📄',
        'tests' => ['too_big'],
    ],
    'dom_size' => [
        'icon' => 'fas fa-stream',
        'emoji' => '📊',
        'tests' => ['too_big'],
    ],
    'meta_viewport' => [
        'icon' => 'fas fa-mobile-alt',
        'emoji' => '📱',
        'tests' => ['missing'],
    ],
    'meta_charset' => [
        'icon' => 'fas fa-font',
        'emoji' => '🔤',
        'tests' => ['missing'],
    ],
    'deprecated_html_tags' => [
        'icon' => 'fas fa-code',
        'emoji' => '💻',
        'tests' => ['existing'],
    ],
    'header_server' => [
        'icon' => 'fas fa-server',
        'emoji' => '🖥️',
        'tests' => ['existing'],
    ],
    'server_compression' => [
        'icon' => 'fas fa-compress',
        'emoji' => '📦',
        'tests' => ['missing'],
    ],
    'doctype' => [
        'icon' => 'fas fa-file-code',
        'emoji' => '📑',
        'tests' => ['missing'],
    ],
    'social_links' => [
        'icon' => 'fas fa-share-alt',
        'emoji' => '🔗',
        'tests' => ['missing'],
    ],
    'words_count' => [
        'icon' => 'fas fa-sort-numeric-up',
        'emoji' => '🔢',
        'tests' => ['too_few'],
    ],
    'words_used' => [
        'icon' => 'fas fa-file-alt',
        'emoji' => '📝',
        'tests' => ['missing'],
    ],
    'emails' => [
        'icon' => 'fas fa-envelope',
        'emoji' => '✉️',
        'tests' => ['existing'],
    ],
    'text_to_html_ratio' => [
        'icon' => 'fas fa-percentage',
        'emoji' => '📊',
        'tests' => ['too_low'],
    ],
    'is_https' => [
        'icon' => 'fas fa-lock',
        'emoji' => '🔒',
        'tests' => ['missing'],
    ],
    'is_ssl_valid' => [
        'icon' => 'fas fa-shield-alt',
        'emoji' => '🛡️',
        'tests' => ['invalid'],
    ],
    'inline_css' => [
        'icon' => 'fas fa-paint-brush',
        'emoji' => '🎨',
        'tests' => ['existing'],
    ],
    'image_formats' => [
        'icon' => 'fas fa-images',
        'emoji' => '🖼️',
        'tests' => ['existing'],
    ],
    'image_alt' => [
        'icon' => 'fas fa-comment-alt',
        'emoji' => '💬',
        'tests' => ['missing'],
    ],
    'image_lazy_loading' => [
        'icon' => 'fas fa-image',
        'emoji' => '🌄',
        'tests' => ['missing'],
    ],
    'other_headings' => [
        'icon' => 'fas fa-list',
        'emoji' => '📋',
        'tests' => [],
    ],
    'canonical' => [
        'icon' => 'fas fa-link',
        'emoji' => '🔗',
        'tests' => ['missing'],
    ],
    'is_seo_friendly_url' => [
        'icon' => 'fas fa-search',
        'emoji' => '🔍',
        'tests' => ['false'],
    ],
    'is_http2' => [
        'icon' => 'fas fa-network-wired',
        'emoji' => '🌐',
        'tests' => ['invalid'],
    ],
    'unsafe_forms' => [
        'icon' => 'fas fa-lock-open',
        'emoji' => '🔓',
        'tests' => ['existing'],
    ],
    'unsafe_external_links' => [
        'icon' => 'fas fa-external-link-alt',
        'emoji' => '⚠️',
        'tests' => ['existing'],
    ],
    'non_deferred_scripts' => [
        'icon' => 'fas fa-code',
        'emoji' => '💻',
        'tests' => ['existing'],
    ],
    'http_requests' => [
        'icon' => 'fas fa-network-wired',
        'emoji' => '🌐',
        'tests' => ['too_many'],
    ],
    'internal_links' => [
        'icon' => 'fas fa-link',
        'emoji' => '🔗',
        'tests' => ['too_many'],
    ],
    'external_links' => [
        'icon' => 'fas fa-external-link-alt',
        'emoji' => '🔗',
        'tests' => ['too_many'],
    ],
    'opengraph' => [
        'icon' => 'fas fa-share-square',
        'emoji' => '📤',
        'tests' => ['missing'],
    ],
    'schemas' => [
        'icon' => 'fas fa-sitemap',
        'emoji' => '🗂️',
        'tests' => ['missing', 'invalid'],
    ],
    'safe_browsing' => [
        'icon' => 'fas fa-shield-alt',
        'emoji' => '🛡️',
        'tests' => ['false'],
    ],
    'hsts' => [
        'icon' => 'fas fa-shipping-fast',
        'emoji' => '🚙',
        'tests' => ['missing', 'invalid'],
    ],
	'csp' => [
		'icon' => 'fas fa-shield-alt',
		'emoji' => '🛡️',
		'tests' => ['missing'],
	],
    'spf' => [
        'icon' => 'fas fa-envelope-open-text',
        'emoji' => '✉️',
        'tests' => ['missing', 'multiple', 'invalid'],
    ],
    'referrer_policy' => [
        'icon' => 'fas fa-user-shield',
        'emoji' => '🧭',
        'tests' => ['missing', 'unsafe'],
    ],
];
