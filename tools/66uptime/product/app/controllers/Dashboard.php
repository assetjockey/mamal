<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
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

namespace Altum\Controllers;


defined('ALTUMCODE') || die();

class Dashboard extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $dashboard_features = ((array) $this->user->preferences->dashboard) + array_fill_keys(require APP_PATH . 'includes/available_dashboard_features.php', true);

        /* Get the dns monitors */
        if($dashboard_features['dns_monitors'] && settings()->monitors_heartbeats->dns_monitors_is_enabled) {
            $dns_monitors = db()->where('user_id', $this->user->user_id)->get('dns_monitors', 6);
        }

        /* Get the server monitors */
        if($dashboard_features['server_monitors'] && settings()->monitors_heartbeats->server_monitors_is_enabled) {
            $server_monitors = db()->where('user_id', $this->user->user_id)->get('server_monitors', 6);
        }

        /* Get the monitors */
        if($dashboard_features['monitors'] && settings()->monitors_heartbeats->monitors_is_enabled) {
            $monitors = db()->where('user_id', $this->user->user_id)->get('monitors', 6);
            $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();
        }

        /* Get the heartbeats */
        if($dashboard_features['heartbeats'] && settings()->monitors_heartbeats->heartbeats_is_enabled) {
            $heartbeats = db()->where('user_id', $this->user->user_id)->get('heartbeats', 6);
        }

        if($dashboard_features['status_pages'] && settings()->status_pages->status_pages_is_enabled) {
            /* Get available projects */
            $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

            /* Get available custom domains */
            $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user, false);

            /* Get the status pages */
            $status_pages = \Altum\Cache::cache_function_result('status_pages_dashboard?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function () use ($domains) {
                $status_pages = [];
                $status_pages_result = database()->query("SELECT * FROM `status_pages` WHERE `user_id` = {$this->user->user_id} LIMIT 6");

                while ($row = $status_pages_result->fetch_object()) {

                    /* Generate the status page full URL base */
                    $row->full_url = (new \Altum\Models\StatusPage())->get_status_page_full_url($row, $this->user, $domains);

                    $status_pages[] = $row;
                }

                return $status_pages;
            });
        }

        if($dashboard_features['domain_names'] && settings()->monitors_heartbeats->domain_names_is_enabled) {
            /* Get the domain names */
            $domain_names = [];
            $domain_names_result = database()->query("SELECT * FROM `domain_names` WHERE `user_id` = {$this->user->user_id} LIMIT 6");

            while ($row = $domain_names_result->fetch_object()) {
                $row->whois = json_decode($row->whois ?? '');
                $row->ssl = json_decode($row->ssl ?? '');
                $domain_names[] = $row;
            }
        }

        /* Get the game servers */
        if($dashboard_features['game_servers'] && settings()->monitors_heartbeats->game_servers_is_enabled) {
            $game_servers = db()->where('user_id', $this->user->user_id)->get('game_servers', 6);
        }

        /* Prepare the view */
        $data = [
            'dns_monitors' => $dns_monitors ?? null,
            'server_monitors' => $server_monitors ?? null,
            'monitors' => $monitors ?? null,
            'ping_servers' => $ping_servers ?? [],
            'heartbeats' => $heartbeats ?? null,
            'status_pages' => $status_pages ?? null,
            'domain_names' => $domain_names ?? null,
            'projects' => $projects ?? null,
            'game_servers' => $game_servers ?? null,
            'game_server_types' => require APP_PATH . 'includes/game_server_types.php'
        ];

        $view = new \Altum\View('dashboard/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
