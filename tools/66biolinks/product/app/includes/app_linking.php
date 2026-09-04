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
    'aliexpress' => [
        'name' => 'AliExpress',
        'icon' => 'fas fa-shopping-cart',
        'color' => '#ff4747',

        'display_formats' => [
            'aliexpress.com/item/{item_id}.html',
        ],

        'formats' => [
            'aliexpress.com/item/%s.html' => [
                'regex' => 'aliexpress\.com\/item\/([0-9]+)\.html',
                'iOS' => 'aliexpress://product/detail?productId=%s',
                'Android' => 'intent://www.aliexpress.com/item/%s.html?/#Intent;package=com.alibaba.aliexpresshd;scheme=https;end',
            ],
        ],
    ],

    'tiktok' => [
        'name' => 'TikTok',
        'icon' => 'fab fa-tiktok',
        'color' => '#fc295d',

        'display_formats' => [
            'tiktok.com/@{username}',
            'tiktok.com/@{username}/video/{video_id}',
            'tiktok.com/@{username}/photo/{video_id}',
        ],

        'formats' => [
            'tiktok.com/@%s/video/%s' => [
                'regex' => 'tiktok\.com\/@([a-zA-Z0-9_.]+)\/video\/([0-9]+)/*(?:[?#].*)?',
                'iOS' => 'snssdk1233://aweme/detail/%2$s',
                'Android' => 'intent://aweme/detail/%2$s#Intent;package=com.zhiliaoapp.musically;scheme=snssdk1233;end',
            ],
            'tiktok.com/@%s/photo/%s' => [
                'regex' => 'tiktok\.com\/@([a-zA-Z0-9_.]+)\/photo\/([0-9]+)/*(?:[?#].*)?',
                'iOS' => 'snssdk1233://aweme/detail/%2$s',
                'Android' => 'intent://aweme/detail/%2$s#Intent;package=com.zhiliaoapp.musically;scheme=snssdk1233;end',
            ],
            'tiktok.com/@%s' => [
                'regex' => 'tiktok\.com\/@([a-zA-Z0-9_.]+)/*(?:[?#].*)?',
                'iOS' => 'snssdk1233://user/profile/%s',
                'Android' => 'intent://user/profile/%s#Intent;package=com.zhiliaoapp.musically;scheme=snssdk1233;end',
            ],
        ],
    ],

    'reddit' => [
        'name' => 'Reddit',
        'icon' => 'fab fa-reddit',
        'color' => '#fc3a06',

        'display_formats' => [
            'reddit.com/r/{subreddit}',
            'reddit.com/user/{username}',
            'reddit.com/r/{subreddit}/comments/{post_id}/{slug}',
        ],

        'formats' => [
            'reddit.com/r/%s/comments/%s/%s' => [
                'regex' => 'reddit\.com\/r\/([a-zA-Z0-9_]+)\/comments\/([a-zA-Z0-9]+)\/([^\/?#]+)/*(?:[?#].*)?',
                'iOS' => 'reddit://reddit.com/r/%s/comments/%s/%s',
                'Android' => 'intent://reddit.com/r/%s/comments/%s/%s#Intent;package=com.reddit.frontpage;scheme=https;end',
            ],
            'reddit.com/user/%s' => [
                'regex' => 'reddit\.com\/user\/([a-zA-Z0-9_-]+)/*(?:[?#].*)?',
                'iOS' => 'reddit://reddit.com/user/%s',
                'Android' => 'intent://reddit.com/user/%s#Intent;package=com.reddit.frontpage;scheme=https;end',
            ],
            'reddit.com/r/%s' => [
                'regex' => 'reddit\.com\/r\/([a-zA-Z0-9_]+)/*(?:[?#].*)?',
                'iOS' => 'reddit://reddit.com/r/%s',
                'Android' => 'intent://reddit.com/r/%s#Intent;package=com.reddit.frontpage;scheme=https;end',
            ],
        ],
    ],

    'x' => [
        'name' => 'X',
        'icon' => 'fab fa-x-twitter',
        'color' => '#1da1f2',

        'display_formats' => [
            'x.com/{username}',
            'x.com/{username}/status/{post_id}',
            'twitter.com/{username}',
            'twitter.com/{username}/status/{post_id}',
        ],

        'formats' => [
            'x.com/%s/status/%s' => [
                'regex' => 'x\.com\/([a-zA-Z0-9_]{1,15})\/status\/([0-9]+)/*(?:[?#].*)?',
                'iOS' => 'twitter://status?id=%2$s',
                'Android' => 'intent://twitter.com/%s/status/%s#Intent;package=com.twitter.android;scheme=https;end',
            ],
            'twitter.com/%s/status/%s' => [
                'regex' => 'twitter\.com\/([a-zA-Z0-9_]{1,15})\/status\/([0-9]+)/*(?:[?#].*)?',
                'iOS' => 'twitter://status?id=%2$s',
                'Android' => 'intent://twitter.com/%s/status/%s#Intent;package=com.twitter.android;scheme=https;end',
            ],
            'x.com/%s' => [
                'regex' => 'x\.com\/([a-zA-Z0-9_]{1,15})/*(?:[?#].*)?',
                'iOS' => 'twitter://user?screen_name=%s',
                'Android' => 'intent://twitter.com/%s#Intent;package=com.twitter.android;scheme=https;end',
            ],
            'twitter.com/%s' => [
                'regex' => 'twitter\.com\/([a-zA-Z0-9_]{1,15})/*(?:[?#].*)?',
                'iOS' => 'twitter://user?screen_name=%s',
                'Android' => 'intent://twitter.com/%s#Intent;package=com.twitter.android;scheme=https;end',
            ],
        ],
    ],

    'youtube' => [
        'name' => 'YouTube',
        'icon' => 'fab fa-youtube',
        'color' => '#ff0000',

        'display_formats' => [
            'youtube.com/watch?v={video}',
            'youtu.be/{video}',
            'youtube.com/live/{video}',
            'youtube.com/shorts/{video}',
            'youtube.com/embed/{video}',
            'youtube.com/playlist?list={playlist}',
            'youtube.com/user/{user}',
            'youtube.com/channel/{channel}',
            'youtube.com/@{user}',
        ],

        'formats' => [
            'youtube.com/watch?v=%s' => [
                'regex' => 'youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)(?:&.*)?',
                'iOS' => 'vnd.youtube://www.youtube.com/watch?v=%s',
                'Android' => 'intent://www.youtube.com/watch?v=%s#Intent;package=com.google.android.youtube;scheme=https;end',
            ],
            'youtu.be/%s' => [
                'regex' => 'youtu\.be\/([a-zA-Z0-9_-]+)/*(?:[?#].*)?',
                'iOS' => 'vnd.youtube://www.youtube.com/watch?v=%s',
                'Android' => 'intent://www.youtube.com/watch?v=%s#Intent;package=com.google.android.youtube;scheme=https;end',
            ],
            'youtube.com/live/%s' => [
                'regex' => 'youtube\.com\/live\/([a-zA-Z0-9_-]+)/*(?:[?#].*)?',
                'iOS' => 'vnd.youtube://www.youtube.com/live/%s',
                'Android' => 'intent://www.youtube.com/live/%s#Intent;package=com.google.android.youtube;scheme=https;end',
            ],
            'youtube.com/shorts/%s' => [
                'regex' => 'youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/*(?:[?#].*)?',
                'iOS' => 'vnd.youtube://www.youtube.com/shorts/%s',
                'Android' => 'intent://www.youtube.com/shorts/%s#Intent;package=com.google.android.youtube;scheme=https;end',
            ],
            'youtube.com/embed/%s' => [
                'regex' => 'youtube\.com\/embed\/([a-zA-Z0-9_-]+)/*(?:[?#].*)?',
                'iOS' => 'vnd.youtube://www.youtube.com/watch?v=%s',
                'Android' => 'intent://www.youtube.com/watch?v=%s#Intent;package=com.google.android.youtube;scheme=https;end',
            ],
            'youtube.com/playlist?list=%s' => [
                'regex' => 'youtube\.com\/playlist\?list=([a-zA-Z0-9_-]+)(?:&.*)?',
                'iOS' => 'vnd.youtube://www.youtube.com/playlist?list=%s',
                'Android' => 'intent://www.youtube.com/playlist?list=%s#Intent;package=com.google.android.youtube;scheme=https;end',
            ],
            'youtube.com/user/%s' => [
                'regex' => 'youtube\.com\/user\/([a-zA-Z0-9_-]+)/*(?:[?#].*)?',
                'iOS' => 'vnd.youtube://user/%s',
                'Android' => 'intent://www.youtube.com/user/%s#Intent;package=com.google.android.youtube;scheme=https;end',
            ],
            'youtube.com/channel/%s' => [
                'regex' => 'youtube\.com\/channel\/([a-zA-Z0-9_-]+)/*(?:[?#].*)?',
                'iOS' => 'vnd.youtube://www.youtube.com/channel/%s',
                'Android' => 'intent://www.youtube.com/channel/%s#Intent;package=com.google.android.youtube;scheme=https;end',
            ],
            'youtube.com/@%s' => [
                'regex' => 'youtube\.com\/@([a-zA-Z0-9_.-]+)/*(?:[?#].*)?',
                'iOS' => 'vnd.youtube://www.youtube.com/@%s',
                'Android' => 'intent://www.youtube.com/@%s#Intent;package=com.google.android.youtube;scheme=https;end',
            ],
        ],
    ],

    'instagram' => [
        'name' => 'Instagram',
        'icon' => 'fab fa-instagram',
        'color' => '#e1306c',

        'display_formats' => [
            'instagram.com/{username}',
            'instagram.com/p/{post_id}',
            'instagram.com/reel/{reel_id}',
            'instagram.com/stories/{username}/{story_id}',
        ],

        'formats' => [
            'instagram.com/p/%s' => [
                'regex' => 'instagram\.com\/p\/([^\/?#]+)/*(?:[?#].*)?',
                'iOS' => 'https://www.instagram.com/p/%s',
                'Android' => 'intent://www.instagram.com/p/%s/#Intent;package=com.instagram.android;scheme=https;end',
            ],
            'instagram.com/reel/%s' => [
                'regex' => 'instagram\.com\/reel\/([^\/?#]+)/*(?:[?#].*)?',
                'iOS' => 'https://www.instagram.com/reel/%s',
                'Android' => 'intent://www.instagram.com/reel/%s/#Intent;package=com.instagram.android;scheme=https;end',
            ],
            'instagram.com/stories/%s/%s' => [
                'regex' => 'instagram\.com\/stories\/([a-zA-Z0-9_.]+)\/([0-9]+)/*(?:[?#].*)?',
                'iOS' => 'https://www.instagram.com/stories/%s/%s',
                'Android' => 'intent://www.instagram.com/stories/%s/%s/#Intent;package=com.instagram.android;scheme=https;end',
            ],
            'instagram.com/%s' => [
                'regex' => 'instagram\.com\/([a-zA-Z0-9_.]+)/*(?:[?#].*)?',
                'iOS' => 'instagram://user?username=%s',
                'Android' => 'intent://www.instagram.com/%s/#Intent;package=com.instagram.android;scheme=https;end',
            ],
        ],
    ],

    'whatsapp' => [
        'name' => 'WhatsApp',
        'icon' => 'fab fa-whatsapp',
        'color' => '#128c7e',

        'display_formats' => [
            'wa.me/{phone_number}',
        ],

        'formats' => [
            'wa.me/%s' => [
                'regex' => 'wa\.me\/([0-9]+)/*(?:[?#].*)?',
                'iOS' => 'whatsapp://send?phone=%s',
                'Android' => 'intent://send?phone=%s#Intent;package=com.whatsapp;scheme=whatsapp;end',
            ],
        ],
    ],

    'snapchat' => [
        'name' => 'Snapchat',
        'icon' => 'fab fa-snapchat',
        'color' => '#FFB700',

        'display_formats' => [
            'snapchat.com/add/{username}',
        ],

        'formats' => [
            'snapchat.com/add/%s' => [
                'regex' => 'snapchat\.com\/add\/(.+)',
                'iOS' => 'snapchat://add/%s',
                'Android' => 'intent://add/%s#Intent;scheme=snapchat;package=com.snapchat.android;end;',
            ],
        ],
    ],

    'facebook_messenger' => [
        'name' => 'Facebook Messenger',
        'icon' => 'fab fa-facebook-messenger',
        'color' => '#0084ff',

        'display_formats' => [
            'messenger.com/t/{id}',
        ],

        'formats' => [
            'messenger.com/t/%s' => [
                'regex' => 'messenger\.com\/t\/(.+)',
                'iOS' => 'fb-messenger-public://user-thread/%s',
                'Android' => 'intent://user/%s/#Intent;scheme=fb-messenger;package=com.facebook.orca;end',
            ],
        ],
    ],

    'facebook' => [
        'name' => 'Facebook',
        'icon' => 'fab fa-facebook',
        'color' => '#1877f2',

        'display_formats' => [
            'www.facebook.com/{page}',
        ],

        'formats' => [
            'www.facebook.com/%s' => [
                'regex' => 'facebook\.com\/(.+)',
                'iOS' => 'https://www.facebook.com/%s',
                'Android' => 'intent://%s#Intent;package=com.facebook.katana;scheme=https;end',
            ],
        ],
    ],

    'telegram' => [
        'name' => 'Telegram',
        'icon' => 'fab fa-telegram',
        'color' => '#0088cc',

        'display_formats' => [
            't.me/{username}',
            't.me/+{invite_code}',
        ],

        'formats' => [
            't.me/+%s' => [
                'regex' => 't\.me\/\+([a-zA-Z0-9_-]+)/*(?:[?#].*)?',
                'iOS' => 'https://t.me/+%s',
                'Android' => 'intent://t.me/+%s#Intent;package=org.telegram.messenger;scheme=https;end',
            ],
            't.me/%s' => [
                'regex' => 't\.me\/([a-zA-Z0-9_]{5,32})/*(?:[?#].*)?',
                'iOS' => 'tg://resolve?domain=%s',
                'Android' => 'intent://resolve?domain=%s#Intent;package=org.telegram.messenger;scheme=tg;end',
            ],
        ],
    ],

    'spotify' => [
        'name' => 'Spotify',
        'icon' => 'fab fa-spotify',
        'color' => '#2CD85C',

        'display_formats' => [
            'open.spotify.com/track/{id}',
            'open.spotify.com/artist/{id}',
            'open.spotify.com/album/{id}',
            'open.spotify.com/episode/{id}',
            'open.spotify.com/playlist/{id}',
            'open.spotify.com/show/{id}',
        ],

        'formats' => [
            'open.spotify.com/track/%s' => [
                'regex' => 'open\.spotify\.com\/track\/([a-zA-Z0-9]+)/*(?:[?#].*)?',
                'iOS' => 'spotify://track/%s',
                'Android' => 'spotify://track/%s',
            ],
            'open.spotify.com/artist/%s' => [
                'regex' => 'open\.spotify\.com\/artist\/([a-zA-Z0-9]+)/*(?:[?#].*)?',
                'iOS' => 'spotify://artist/%s',
                'Android' => 'spotify://artist/%s',
            ],
            'open.spotify.com/album/%s' => [
                'regex' => 'open\.spotify\.com\/album\/([a-zA-Z0-9]+)/*(?:[?#].*)?',
                'iOS' => 'spotify://album/%s',
                'Android' => 'spotify://album/%s',
            ],
            'open.spotify.com/episode/%s' => [
                'regex' => 'open\.spotify\.com\/episode\/([a-zA-Z0-9]+)/*(?:[?#].*)?',
                'iOS' => 'spotify://episode/%s',
                'Android' => 'spotify://episode/%s',
            ],
            'open.spotify.com/playlist/%s' => [
                'regex' => 'open\.spotify\.com\/playlist\/([a-zA-Z0-9]+)/*(?:[?#].*)?',
                'iOS' => 'spotify://playlist/%s',
                'Android' => 'spotify://playlist/%s',
            ],
            'open.spotify.com/show/%s' => [
                'regex' => 'open\.spotify\.com\/show\/([a-zA-Z0-9]+)/*(?:[?#].*)?',
                'iOS' => 'spotify://show/%s',
                'Android' => 'spotify://show/%s',
            ],
        ],
    ],

    'applemusic' => [
        'name' => 'Apple Music',
        'icon' => 'fab fa-apple',
        'color' => '#f8506b',

        'display_formats' => [
            'music.apple.com/{language_code}/album/{id}',
            'music.apple.com/{language_code}/playlist/{id}',
            'music.apple.com/{language_code}/artist/{id}',
        ],

        'formats' => [
            'music.apple.com/%s/album/%s' => [
                'regex'   => 'music\.apple\.com\/(.*)\/album\/(.+)',
                'iOS'     => 'music://album/%2$s',
                'Android' => 'intent://music.apple.com/%s/album/%s#Intent;package=com.apple.android.music;scheme=https;end',
            ],
            'music.apple.com/%s/playlist/%s' => [
                'regex'   => 'music\.apple\.com\/(.*)\/playlist\/(.+)',
                'iOS'     => 'music://playlist/%2$s',
                'Android' => 'intent://music.apple.com/%s/playlist/%s#Intent;package=com.apple.android.music;scheme=https;end',
            ],
            'music.apple.com/%s/artist/%s' => [
                'regex'   => 'music\.apple\.com\/(.*)\/artist\/(.+)',
                'iOS'     => 'music://artist/%2$s',
                'Android' => 'intent://music.apple.com/%s/artist/%s#Intent;package=com.apple.android.music;scheme=https;end',
            ],
        ],
    ],

    'linkedin' => [
        'name' => 'LinkedIn',
        'icon' => 'fab fa-linkedin',
        'color' => '#0a66c2',

        'display_formats' => [
            'linkedin.com/in/{id}',
            'linkedin.com/company/{id}',
        ],

        'formats' => [
            'linkedin.com/in/%s' => [
                'regex' => 'linkedin\.com\/in\/(.+)',
                'iOS' => 'linkedin://in/%s',
                'Android' => 'intent://www.linkedin.com/in/%s/#Intent;package=com.linkedin.android;scheme=https;end',
            ],
            'linkedin.com/company/%s' => [
                'regex' => 'linkedin\.com\/company\/(.+)',
                'iOS' => 'linkedin://company/%s',
                'Android' => 'intent://www.linkedin.com/company/%s/#Intent;package=com.linkedin.android;scheme=https;end',
            ],
        ],
    ],

    'pinterest' => [
        'name' => 'Pinterest',
        'icon' => 'fab fa-pinterest',
        'color' => '#e60023',

        'display_formats' => [
            'pinterest.com/{username}',
            'pinterest.com/pin/{id}',
        ],

        'formats' => [
            'pinterest.com/pin/%s' => [
                'regex' => 'pinterest\.com\/pin\/(.+)',
                'iOS' => 'pinterest://pin/%s',
                'Android' => 'intent://www.pinterest.com/pin/%s/#Intent;package=com.pinterest;scheme=https;end',
            ],
            'pinterest.com/%s' => [
                'regex' => 'pinterest\.com\/(.+)',
                'iOS' => 'pinterest://user/%s',
                'Android' => 'pinterest://pinterest.com/%s',
            ],
        ],
    ],

    'twitch' => [
        'name' => 'Twitch',
        'icon' => 'fab fa-twitch',
        'color' => '#9146ff',

        'display_formats' => [
            'twitch.tv/{username}',
        ],

        'formats' => [
            'twitch.tv/%s' => [
                'regex' => 'twitch\.tv\/(.+)',
                'iOS' => 'twitch://stream/%s',
                'Android' => 'twitch://stream/%s',
            ],
        ],
    ],

    'netflix' => [
        'name' => 'Netflix',
        'icon' => 'fas fa-film',
        'color' => '#e50914',

        'display_formats' => [
            'netflix.com/{id}',
        ],

        'formats' => [
            'netflix.com/%s' => [
                'regex' => 'netflix\.com\/(.+)',
                'iOS' => 'nflx://www.netflix.com/%s',
                'Android' => 'intent://www.netflix.com/%s#Intent;package=com.netflix.mediaclient;scheme=https;end&trkid=262617323&s=i',
            ],
        ],
    ],

    'google_sheets' => [
        'name' => 'Google Sheets',
        'icon' => 'fas fa-file',
        'color' => '#25a465',

        'display_formats' => [
            'docs.google.com/spreadsheets/{id}',
        ],

        'formats' => [
            'docs.google.com/spreadsheets/%s' => [
                'regex' => 'docs\.google\.com\/spreadsheets\/(.+)',
                'iOS' => 'googlesheets://docs.google.com/spreadsheets/%s',
                'Android' => 'intent://docs.google.com/spreadsheets/%s#Intent;package=com.google.android.apps.docs.editors.sheets;scheme=https;end',
            ],
        ],
    ],

    'google_docs' => [
        'name' => 'Google Docs',
        'icon' => 'fas fa-file-word',
        'color' => '#2a7efc',

        'display_formats' => [
            'docs.google.com/document/{id}',
        ],

        'formats' => [
            'docs.google.com/document/%s' => [
                'regex' => 'docs\.google\.com\/document\/(.+)',
                'iOS' => 'googledocs://docs.google.com/document/%s',
                'Android' => 'intent://docs.google.com/document/%s#Intent;package=com.google.android.apps.docs.editors.docs;scheme=https;end',
            ],
        ],
    ],

    'google_slides' => [
        'name' => 'Google Slides',
        'icon' => 'fas fa-image',
        'color' => '#fabe0b',

        'display_formats' => [
            'docs.google.com/presentation/{id}',
        ],

        'formats' => [
            'docs.google.com/presentation/%s' => [
                'regex' => 'docs\.google\.com\/presentation\/(.+)',
                'iOS' => 'googleslides://docs.google.com/presentation/%s',
                'Android' => 'intent://docs.google.com/presentation/%s#Intent;package=com.google.android.apps.docs.editors.slides;scheme=https;end',
            ],
        ],
    ],

    'google_maps' => [
        'name' => 'Google Maps',
        'icon' => 'fas fa-map-location-dot',
        'color' => '#4285f4',

        'display_formats' => [
            'google.com/maps/{map}',
        ],

        'formats' => [
            'google.com/maps/%s' => [
                'regex' => 'google\.com\/maps\/(.+)',
                'iOS' => 'comgooglemapsurl://www.google.com/maps/%s',
                'Android' => 'intent://www.google.com/maps/%s#Intent;package=com.google.android.apps.maps;scheme=https;end?entry=ttu',
            ],
        ],
    ],

    'airbnb' => [
        'name' => 'Airbnb',
        'icon' => 'fab fa-airbnb',
        'color' => '#FF385c',

        'display_formats' => [
            'airbnb.com/rooms/{room}',
        ],

        'formats' => [
            'airbnb.com/rooms/%s' => [
                'regex' => 'airbnb\.com\/rooms\/(.+)',
                'iOS' => 'airbnb://rooms/%s',
                'Android' => 'intent://www.airbnb.com/rooms/%s#Intent;package=com.airbnb.android;scheme=https;end',
            ],
        ],
    ],

    'tripadvisor' => [
        'name' => 'TripAdvisor',
        'icon' => 'fas fa-plane',
        'color' => '#00AF87',

        'display_formats' => [
            'tripadvisor.com/{location}',
        ],

        'formats' => [
            'tripadvisor.com/%s' => [
                'regex' => 'tripadvisor\.com\/(.+)',
                'iOS' => 'tripadvisor://www.tripadvisor.com/%s',
                'Android' => 'intent://www.tripadvisor.com/%s#Intent;package=com.tripadvisor.tripadvisor;scheme=https;end',
            ],
        ],
    ],

    'amazon' => [
        'name' => 'Amazon',
        'icon' => 'fab fa-amazon',
        'color' => '#ff9900',

        'display_formats' => [
            'amazon.com/{product}',
            'amazon.co.uk/{product}',
            'amazon.de/{product}',
            'amazon.fr/{product}',
            'amazon.es/{product}',
            'amazon.it/{product}',
            'amazon.nl/{product}',
            'amazon.co.jp/{product}',
            'amazon.in/{product}',
            'amazon.com.br/{product}',
            'amazon.com.mx/{product}',
            'amazon.com.au/{product}',
            'amazon.sg/{product}',
            'amazon.ae/{product}',
            'amazon.sa/{product}',
            'amazon.se/{product}',
            'amazon.pl/{product}',
            'amazon.be/{product}',
            'amazon.com.tr/{product}',
            'amazon.cn/{product}',
            'amazon.eg/{product}',
        ],

        'formats' => [
            'amazon.com/%s' => [
                'regex' => 'amazon\.com\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.com/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.co.uk/%s' => [
                'regex' => 'amazon\.co\.uk\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.co.uk/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.de/%s' => [
                'regex' => 'amazon\.de\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.de/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.fr/%s' => [
                'regex' => 'amazon\.fr\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.fr/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.es/%s' => [
                'regex' => 'amazon\.es\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.es/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.it/%s' => [
                'regex' => 'amazon\.it\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.it/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.nl/%s' => [
                'regex' => 'amazon\.nl\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.nl/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.co.jp/%s' => [
                'regex' => 'amazon\.co\.jp\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.co.jp/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.in/%s' => [
                'regex' => 'amazon\.in\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.in/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.com.br/%s' => [
                'regex' => 'amazon\.com\.br\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.com.br/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.com.mx/%s' => [
                'regex' => 'amazon\.com\.mx\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.com.mx/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.com.au/%s' => [
                'regex' => 'amazon\.com\.au\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.com.au/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.sg/%s' => [
                'regex' => 'amazon\.sg\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.sg/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.ae/%s' => [
                'regex' => 'amazon\.ae\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.ae/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.sa/%s' => [
                'regex' => 'amazon\.sa\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.sa/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.se/%s' => [
                'regex' => 'amazon\.se\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.se/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.pl/%s' => [
                'regex' => 'amazon\.pl\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.pl/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.be/%s' => [
                'regex' => 'amazon\.be\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.be/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.com.tr/%s' => [
                'regex' => 'amazon\.com\.tr\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.com.tr/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.cn/%s' => [
                'regex' => 'amazon\.cn\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.cn/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
            'amazon.eg/%s' => [
                'regex' => 'amazon\.eg\/(.+)',
                'iOS' => 'com.amazon.mobile.shopping.web://amazon.eg/%s',
                'Android' => 'com.amazon.mobile.shopping.web://%s',
            ],
        ],
    ],

    'stockx' => [
        'name' => 'StockX',
        'icon' => 'fab fa-mixer',
        'color' => '#00AF87',

        'display_formats' => [
            'stockx.com/{product}',
        ],

        'formats' => [
            'stockx.com/%s' => [
                'regex' => 'stockx\.com\/(.+)',
                'iOS' => 'stockx://stockx.com/%s',
                'Android' => 'intent://stockx.com/%s/#Intent;package=com.stockx.stockx;scheme=https;end',
            ],
        ],
    ],

    'booking' => [
        'name' => 'Booking.com',
        'icon' => 'fas fa-hotel',
        'color' => '#003580',

        'display_formats' => [
            'booking.com/hotel/{id}',
        ],

        'formats' => [
            'booking.com/hotel/%s' => [
                'regex' => 'booking\.com\/hotel\/(.+)',
                'iOS' => 'https://www.booking.com/hotel/%s',
                'Android' => 'intent://www.booking.com/hotel/%s#Intent;package=com.booking;scheme=https;end',
            ],
        ],
    ],

    'waze' => [
        'name' => 'Waze',
        'icon' => 'fas fa-car',
        'color' => '#33ccff',

        'display_formats' => [
            'waze.com/ul/{place_id}',
            'waze.com/ul?ll={lat},{lng}',
            'ul.waze.com/ul?ll={lat},{lng}',
            'waze.com/ul?q={query}',
            'ul.waze.com/ul?q={query}',
        ],

        'formats' => [
            'ul.waze.com/ul?ll=%s,%s' => [
                'regex' => 'ul\.waze\.com\/ul\?ll=(-?[0-9.]+)(?:,|%2C)(-?[0-9.]+)(?:&.*)?',
                'iOS' => 'waze://?ll=%s,%s&navigate=yes',
                'Android' => 'intent://?ll=%s,%s&navigate=yes#Intent;package=com.waze;scheme=waze;end',
            ],
            'waze.com/ul?ll=%s,%s' => [
                'regex' => 'waze\.com\/ul\?ll=(-?[0-9.]+)(?:,|%2C)(-?[0-9.]+)(?:&.*)?',
                'iOS' => 'waze://?ll=%s,%s&navigate=yes',
                'Android' => 'intent://?ll=%s,%s&navigate=yes#Intent;package=com.waze;scheme=waze;end',
            ],
            'ul.waze.com/ul?q=%s' => [
                'regex' => 'ul\.waze\.com\/ul\?q=([^&#]+)(?:&.*)?',
                'iOS' => 'waze://?q=%s&navigate=yes',
                'Android' => 'intent://?q=%s&navigate=yes#Intent;package=com.waze;scheme=waze;end',
            ],
            'waze.com/ul?q=%s' => [
                'regex' => 'waze\.com\/ul\?q=([^&#]+)(?:&.*)?',
                'iOS' => 'waze://?q=%s&navigate=yes',
                'Android' => 'intent://?q=%s&navigate=yes#Intent;package=com.waze;scheme=waze;end',
            ],
        ],
    ],
];
