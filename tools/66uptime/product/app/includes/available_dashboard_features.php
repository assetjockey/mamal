<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 *  View all other existing AltumCode projects via https://altumcode.com/
 *  Get in touch for support or general queries via https://altumcode.com/contact
 *  Download the latest version via https://altumcode.com/downloads
 *
 *  X/Twitter: https://x.com/AltumCode
 *  Facebook: https://facebook.com/altumcode
 *  Instagram: https://instagram.com/altumcode
 */

$features = [];

if(settings()->monitors_heartbeats->game_servers_is_enabled) {
	$features[] = 'game_servers';
}

if(settings()->monitors_heartbeats->monitors_is_enabled) {
    $features[] = 'monitors';
}

if(settings()->monitors_heartbeats->heartbeats_is_enabled) {
    $features[] = 'heartbeats';
}

if(settings()->monitors_heartbeats->domain_names_is_enabled) {
    $features[] = 'domain_names';
}

if(settings()->monitors_heartbeats->dns_monitors_is_enabled) {
    $features[] = 'dns_monitors';
}

if(settings()->monitors_heartbeats->server_monitors_is_enabled) {
    $features[] = 'server_monitors';
}

if(settings()->status_pages->status_pages_is_enabled) {
    $features[] = 'status_pages';
}

return $features;
