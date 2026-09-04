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

$access = [
    'read' => [
        'read.all' => l('global.all')
    ],

    'create' => [
        'create.links' => l('links.title'),
    ],

    'update' => [
        'update.links' => l('links.title'),
    ],

    'delete' => [
        'delete.links' => l('links.title'),
    ],
];

if(settings()->links->projects_is_enabled) {
    $access['create']['create.projects'] = l('projects.title');
    $access['update']['update.projects'] = l('projects.title');
    $access['delete']['delete.projects'] = l('projects.title');
}

if(settings()->links->pixels_is_enabled) {
    $access['create']['create.pixels'] = l('pixels.title');
    $access['update']['update.pixels'] = l('pixels.title');
    $access['delete']['delete.pixels'] = l('pixels.title');
}

if(settings()->codes->ai_qr_codes_is_enabled) {
    $access['create']['create.ai_qr_codes'] = l('ai_qr_codes.title');
    $access['update']['update.ai_qr_codes'] = l('ai_qr_codes.title');
    $access['delete']['delete.ai_qr_codes'] = l('ai_qr_codes.title');
}

if(settings()->codes->qr_codes_is_enabled) {
    $access['create']['create.qr_codes'] = l('qr_codes.title');
    $access['update']['update.qr_codes'] = l('qr_codes.title');
    $access['delete']['delete.qr_codes'] = l('qr_codes.title');
}

if(settings()->codes->barcodes_is_enabled) {
    $access['create']['create.barcodes'] = l('barcodes.title');
    $access['update']['update.barcodes'] = l('barcodes.title');
    $access['delete']['delete.barcodes'] = l('barcodes.title');
}

if(settings()->links->domains_is_enabled) {
    $access['create']['create.domains'] = l('domains.title');
    $access['update']['update.domains'] = l('domains.title');
    $access['delete']['delete.domains'] = l('domains.title');
}

return $access;
