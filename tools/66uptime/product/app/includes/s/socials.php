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
    'facebook'=> [
        'format' => 'https://facebook.com/%s',
        'input_display_format' => true,
        'name' => 'Facebook',
        'icon' => 'fab fa-facebook',
        'max_length' => 64
    ],
    'instagram'=> [
        'format' => 'https://instagram.com/%s',
        'input_display_format' => true,
        'name' => 'Instagram',
        'icon' => 'fab fa-instagram',
        'max_length' => 64
    ],
    'x'=> [
        'format' => 'https://x.com/%s',
        'input_display_format' => true,
        'name' => 'X',
        'icon' => 'fab fa-x-twitter',
        'max_length' => 64
    ],
    'threads'=> [
        'format' => 'https://threads.com/@%s',
        'input_display_format' => true,
        'name' => 'Threads',
        'icon' => 'fab fa-threads',
        'max_length' => 64
    ],
    'email'=> [
        'format' => 'mailto:%s',
        'input_display_format' => false,
        'name' => 'Email',
        'icon' => 'fas fa-envelope',
        'max_length' => 320
    ],
    'website'=> [
        'format' => '%s',
        'input_display_format' => false,
        'name' => 'Website',
        'icon' => 'fas fa-globe',
        'max_length' => 2048
    ],
];
