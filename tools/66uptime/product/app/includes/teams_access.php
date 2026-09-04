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
        'create.notification_handlers' => l('notification_handlers.title'),
    ],

    'update' => [
        'update.notification_handlers' => l('notification_handlers.title'),
    ],

    'delete' => [
        'delete.notification_handlers' => l('notification_handlers.title'),
    ],
];

if(settings()->monitors_heartbeats->monitors_is_enabled || settings()->monitors_heartbeats->heartbeats_is_enabled || settings()->monitors_heartbeats->game_servers_is_enabled) {
    $access['create']['create.incidents'] = l('incidents.title');
    $access['update']['update.incidents'] = l('incidents.title');
    $access['delete']['delete.incidents'] = l('incidents.title');
}

if(settings()->monitors_heartbeats->projects_is_enabled) {
    $access['create']['create.projects'] = l('projects.title');
    $access['update']['update.projects'] = l('projects.title');
    $access['delete']['delete.projects'] = l('projects.title');
}

if(settings()->status_pages->domains_is_enabled) {
    $access['create']['create.domains'] = l('domains.title');
    $access['update']['update.domains'] = l('domains.title');
    $access['delete']['delete.domains'] = l('domains.title');
}

if(settings()->status_pages->status_pages_is_enabled) {
    $access['create']['create.status_pages'] = l('status_pages.title');
    $access['update']['update.status_pages'] = l('status_pages.title');
    $access['delete']['delete.status_pages'] = l('status_pages.title');
}

if(settings()->monitors_heartbeats->monitors_is_enabled) {
    $access['create']['create.monitors'] = l('monitors.title');
    $access['update']['update.monitors'] = l('monitors.title');
    $access['delete']['delete.monitors'] = l('monitors.title');
}

if(settings()->monitors_heartbeats->domain_names_is_enabled) {
    $access['create']['create.domain_names'] = l('domain_names.title');
    $access['update']['update.domain_names'] = l('domain_names.title');
    $access['delete']['delete.domain_names'] = l('domain_names.title');
}

if(settings()->monitors_heartbeats->heartbeats_is_enabled) {
    $access['create']['create.heartbeats'] = l('heartbeats.title');
    $access['update']['update.heartbeats'] = l('heartbeats.title');
    $access['delete']['delete.heartbeats'] = l('heartbeats.title');
}

if(settings()->monitors_heartbeats->dns_monitors_is_enabled) {
    $access['create']['create.dns_monitors'] = l('dns_monitors.title');
    $access['update']['update.dns_monitors'] = l('dns_monitors.title');
    $access['delete']['delete.dns_monitors'] = l('dns_monitors.title');
}

if(settings()->monitors_heartbeats->server_monitors_is_enabled) {
    $access['create']['create.server_monitors'] = l('server_monitors.title');
    $access['update']['update.server_monitors'] = l('server_monitors.title');
    $access['delete']['delete.server_monitors'] = l('server_monitors.title');
}

if(settings()->monitors_heartbeats->game_servers_is_enabled) {
	$access['create']['create.game_servers'] = l('game_servers.title');
	$access['update']['update.game_servers'] = l('game_servers.title');
	$access['delete']['delete.game_servers'] = l('game_servers.title');
}

return $access;
