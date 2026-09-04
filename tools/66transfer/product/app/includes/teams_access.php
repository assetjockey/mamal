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

$access = [
    'read' => [
        'read.all' => l('global.all')
    ],

    'create' => [
        'create.transfers' => l('transfers.title'),
        'create.notification_handlers' => l('notification_handlers.title'),
    ],

    'update' => [
        'update.transfers' => l('transfers.title'),
        'update.notification_handlers' => l('notification_handlers.title'),
    ],

    'delete' => [
        'delete.transfers' => l('transfers.title'),
        'delete.notification_handlers' => l('notification_handlers.title'),
    ],
];

if(settings()->transfers->transfer_requests_is_enabled) {
	$access['create']['create.transfer_requests'] = l('transfer_requests.title');
	$access['update']['update.transfer_requests'] = l('transfer_requests.title');
	$access['delete']['delete.transfer_requests'] = l('transfer_requests.title');
}

if(settings()->transfers->projects_is_enabled) {
    $access['create']['create.projects'] = l('projects.title');
    $access['update']['update.projects'] = l('projects.title');
    $access['delete']['delete.projects'] = l('projects.title');
}

if(settings()->transfers->domains_is_enabled) {
    $access['create']['create.domains'] = l('domains.title');
    $access['update']['update.domains'] = l('domains.title');
    $access['delete']['delete.domains'] = l('domains.title');
}

if(settings()->transfers->pixels_is_enabled) {
    $access['create']['create.pixels'] = l('pixels.title');
    $access['update']['update.pixels'] = l('pixels.title');
    $access['delete']['delete.pixels'] = l('pixels.title');
}

return $access;
