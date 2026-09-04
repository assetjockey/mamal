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

return [
    /* Main */
    'logo_light' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'svg', 'gif', 'webp', 'avif'],
        'path' => 'main/',
    ],
    'logo_dark' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'svg', 'gif', 'webp', 'avif'],
        'path' => 'main/',
    ],
    'logo_email' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
        'path' => 'main/',
    ],
    'favicon' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'ico', 'svg', 'gif', 'webp'],
        'path' => 'main/',
    ],
    'opengraph' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'svg', 'gif', 'webp', 'avif'],
        'path' => 'main/',
    ],
    'custom_images' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'svg', 'gif', 'webp', 'avif'],
        'path' => 'main/',
    ],
    'taxes_csv' => [
        'whitelisted_file_extensions' => ['csv'],
        'path' => 'main/',
    ],
    'resources_csv' => [
        'whitelisted_file_extensions' => ['csv'],
        'path' => 'main/',
    ],
    'default_avatar' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'ico', 'svg', 'gif', 'webp'],
        'path' => 'main/',
    ],

    /* Users misc */
    'users' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'svg', 'gif', 'webp'],
        'path' => 'users/',
    ],

    /* PWA plugin */
    'app_icon' => [
        'whitelisted_file_extensions' => ['png'],
        'path' => 'pwa/',
    ],
    'app_screenshots' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png'],
        'path' => 'pwa/',
    ],
    'pwa' => [
        'path' => 'pwa/',
    ],

    /* Dynamic OG images plugin */
    'dynamic_og_images' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
    ],

    'push_notifications_icon' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png'],
        'path' => 'main/',
    ],

    /* Blog featured images */
    'blog' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'svg', 'gif', 'webp', 'avif'],
        'path' => 'blog/',
    ],

    /* Payment proofs for offline payments */
    'offline_payment_proofs' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'avif', 'pdf'],
        'path' => 'offline_payment_proofs/',
    ],

    /* AI QR codes */
    'ai_qr_codes' => [
        'path' => 'ai_qr_codes/'
    ],

    'ai_qr_codes/temp' => [
        'path' => 'ai_qr_codes/temp/'
    ],

    'ai_qr_code_default_image' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'avif'],
        'path' => 'ai_qr_codes/'
    ],


    /* QR codes */
    'qr_codes' => [
        'path' => 'qr_codes/logo/'
    ],
    'qr_code' => [
        'path' => 'qr_codes/logo/'
    ],

    'qr_codes/logo' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
        'path' => 'qr_codes/logo/'
    ],
    'qr_code_logo' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
        'path' => 'qr_codes/logo/'
    ],

    'qr_code_default_image' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'avif'],
        'path' => 'qr_codes/logo/'
    ],

    'qr_code_background' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'gif']
    ],

    'qr_code_foreground' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'gif']
    ],

    'qr_code_reader' => [
        'whitelisted_file_extensions' => ['png', 'jpg', 'jpeg', 'svg', 'webp']
    ],

    'qr_code_files' => [
        'whitelisted_file_extensions' => ['pdf', 'zip', 'rar', '7z', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'rtf', 'odt', 'ods', 'odp', 'json', 'xml'],
        'path' => 'qr_code_files/'
    ],

    /* Barcodes */
    'barcodes' => [
    ],

    'barcode_reader' => [
        'whitelisted_file_extensions' => ['png', 'jpg', 'jpeg', 'svg', 'webp']
    ],

    /* Favicons */
    'favicons' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'ico', 'svg', 'gif', 'webp'],
        'path' => 'favicons/'
    ],

    /* Opengraphs */
    'opengraphs' => [
        'whitelisted_file_extensions' => ['jpg', 'jpeg', 'png', 'ico', 'svg', 'gif', 'webp'],
        'path' => 'opengraphs/'
    ],
];
