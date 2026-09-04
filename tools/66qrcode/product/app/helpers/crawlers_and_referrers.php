<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
 *
 * This software is proprietary software owned and licensed by AltumCode.
 * A valid license is required to use, modify, or distribute this software.
 * Unauthorized use, reproduction, modification, or distribution is prohibited.
 *
 * 🌍 Explore all AltumCode projects: https://altumcode.com/
 * 📧 Support & general inquiries: https://altumcode.com/contact
 * 📤 Download the latest version: https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 */

defined('ALTUMCODE') || die();

function get_crawlers() {
    return [
        /* OpenAI */
        'oai-searchbot' => [
            'name' => 'OAI-SearchBot',
            'family' => 'OpenAI',
            'category' => 'ai_search',
        ],
        'gptbot' => [
            'name' => 'GPTBot',
            'family' => 'OpenAI',
            'category' => 'ai_training',
        ],
        'chatgpt-user' => [
            'name' => 'ChatGPT-User',
            'family' => 'OpenAI',
            'category' => 'ai_agent',
        ],
        'oai-adsbot' => [
            'name' => 'OAI-AdsBot',
            'family' => 'OpenAI',
            'category' => 'other',
        ],

        /* Anthropic */
        'claude-searchbot' => [
            'name' => 'Claude-SearchBot',
            'family' => 'Anthropic',
            'category' => 'ai_search',
        ],
        'claudebot' => [
            'name' => 'ClaudeBot',
            'family' => 'Anthropic',
            'category' => 'ai_training',
        ],
        'claude-user' => [
            'name' => 'Claude-User',
            'family' => 'Anthropic',
            'category' => 'ai_agent',
        ],

        /* Perplexity */
        'perplexitybot' => [
            'name' => 'PerplexityBot',
            'family' => 'Perplexity',
            'category' => 'ai_search',
        ],
        'perplexity-user' => [
            'name' => 'Perplexity-User',
            'family' => 'Perplexity',
            'category' => 'ai_agent',
        ],

        /* Amazon */
        'amzn-searchbot' => [
            'name' => 'Amzn-SearchBot',
            'family' => 'Amazon',
            'category' => 'ai_search',
        ],
        'amzn-user' => [
            'name' => 'Amzn-User',
            'family' => 'Amazon',
            'category' => 'ai_agent',
        ],
        'amazonbot' => [
            'name' => 'Amazonbot',
            'family' => 'Amazon',
            'category' => 'ai_training',
        ],

        /* Meta */
        'meta-externalfetcher' => [
            'name' => 'Meta-ExternalFetcher',
            'family' => 'Meta',
            'category' => 'ai_agent',
        ],
        'meta-externalagent' => [
            'name' => 'Meta-ExternalAgent',
            'family' => 'Meta',
            'category' => 'ai_training',
        ],

        /* DuckDuckGo AI */
        'duckassistbot' => [
            'name' => 'DuckAssistBot',
            'family' => 'DuckDuckGo',
            'category' => 'ai_search',
        ],

        /* Google */
        'google-cloudvertexbot' => [
            'name' => 'Google-CloudVertexBot',
            'family' => 'Google',
            'category' => 'ai_agent',
        ],
        'googlebot-image' => [
            'name' => 'Googlebot Image',
            'family' => 'Google',
            'category' => 'search_engine',
        ],
        'googlebot-video' => [
            'name' => 'Googlebot Video',
            'family' => 'Google',
            'category' => 'search_engine',
        ],
        'googlebot' => [
            'name' => 'Googlebot',
            'family' => 'Google',
            'category' => 'search_engine',
        ],
        'googleother-image' => [
            'name' => 'GoogleOther Image',
            'family' => 'Google',
            'category' => 'other',
        ],
        'googleother-video' => [
            'name' => 'GoogleOther Video',
            'family' => 'Google',
            'category' => 'other',
        ],
        'googleother' => [
            'name' => 'GoogleOther',
            'family' => 'Google',
            'category' => 'other',
        ],

        /* Microsoft */
        'bingbot' => [
            'name' => 'Bingbot',
            'family' => 'Microsoft',
            'category' => 'search_engine',
        ],

        /* DuckDuckGo */
        'duckduckbot' => [
            'name' => 'DuckDuckBot',
            'family' => 'DuckDuckGo',
            'category' => 'search_engine',
        ],

        /* Apple */
        'applebot' => [
            'name' => 'Applebot',
            'family' => 'Apple',
            'category' => 'search_engine',
        ],

        /* Yandex */
        'yandeximages' => [
            'name' => 'Yandex Images',
            'family' => 'Yandex',
            'category' => 'search_engine',
        ],
        'yandexbot' => [
            'name' => 'YandexBot',
            'family' => 'Yandex',
            'category' => 'search_engine',
        ],

        /* Baidu */
        'baiduspider' => [
            'name' => 'Baiduspider',
            'family' => 'Baidu',
            'category' => 'search_engine',
        ],

        /* Naver */
        'yeti' => [
            'name' => 'Yeti',
            'family' => 'Naver',
            'category' => 'search_engine',
        ],

        /* Common Crawl */
        'ccbot' => [
            'name' => 'CCBot',
            'family' => 'Common Crawl',
            'category' => 'other',
        ],
    ];
}

function get_crawler_data($user_agent) {

    $user_agent = strtolower($user_agent);

    $crawlers = get_crawlers();

    foreach($crawlers as $identifier => $crawler) {
        if(str_contains($user_agent, $identifier)) {
            return $crawler;
        }
    }

    return null;
}

/* Referrer display names */
function get_referrer_display_name($referrer_host) {

    switch($referrer_host) {
        case 'bing.com':
            return 'Bing';

        case 'baidu.com':
            return 'Baidu';

        case 'google.com':
            return 'Google';

        case 'yahoo.com':
            return 'Yahoo';

        case 'yandex.com':
            return 'Yandex';

        case 'duckduckgo.com':
            return 'DuckDuckGo';

        case 'ecosia.org':
            return 'Ecosia';

        case 'startpage.com':
            return 'Startpage';

        case 'aol.com':
            return 'AOL';

        case 'brave.com':
            return 'Brave';

        case 'openai.com':
            return 'ChatGPT';

        case 'claude.ai':
            return 'Claude';

        case 'perplexity.ai':
            return 'Perplexity';

        case 'copilot.microsoft.com':
            return 'Microsoft Copilot';

        case 'threads.com':
            return 'Threads';

        case 'facebook.com':
            return 'Facebook';

        case 'instagram.com':
            return 'Instagram';

        case 'pinterest.com':
            return 'Pinterest';

        case 'x.com':
        case 'twitter.com':
            return 'X';

        case 'youtube.com':
            return 'YouTube';

        case 'tiktok.com':
            return 'TikTok';

        case 'reddit.com':
            return 'Reddit';

        case 'linkedin.com':
            return 'LinkedIn';

        case 'snapchat.com':
            return 'Snapchat';

        case 'telegram.org':
            return 'Telegram';

        default:
            return $referrer_host;
    }
}
