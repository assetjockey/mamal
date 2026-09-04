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

use Altum\Models\Plan;

defined('ALTUMCODE') || die();

class AdminUserView extends Controller {

    public function index() {

        $user_id = (isset($this->params[0])) ? (int) $this->params[0] : null;

        /* Check if user exists */
        if(!$user = db()->where('user_id', $user_id)->getOne('users')) {
            redirect('admin/users');
        }

        /* Get widget stats */
        $monitors = db()->where('user_id', $user_id)->getValue('monitors', 'count(`monitor_id`)');
        $heartbeats = db()->where('user_id', $user_id)->getValue('heartbeats', 'count(`heartbeat_id`)');
        $domain_names = db()->where('user_id', $user_id)->getValue('domain_names', 'count(`domain_name_id`)');
        $dns_monitors = db()->where('user_id', $user_id)->getValue('dns_monitors', 'count(`dns_monitor_id`)');
        $server_monitors = db()->where('user_id', $user_id)->getValue('server_monitors', 'count(`server_monitor_id`)');
        $game_servers = db()->where('user_id', $user_id)->getValue('game_servers', 'count(`game_server_id`)');
        $status_pages = db()->where('user_id', $user_id)->getValue('status_pages', 'count(`status_page_id`)');
        $incidents = db()->where('user_id', $user_id)->getValue('incidents', 'count(`incident_id`)');
        $projects = db()->where('user_id', $user_id)->getValue('projects', 'count(`project_id`)');
        $domains = db()->where('user_id', $user_id)->getValue('domains', 'count(`domain_id`)');
        $notification_handlers = db()->where('user_id', $user_id)->getValue('notification_handlers', 'count(`notification_handler_id`)');
        $payments = in_array(settings()->license->type, ['Extended License', 'extended']) ? db()->where('user_id', $user_id)->getValue('payments', 'count(`id`)') : 0;

        /* Get the current plan details */
        $user->plan = (new Plan())->get_plan_by_id($user->plan_id);

        /* Check if its a custom plan */
        if($user->plan_id == 'custom') {
            $user->plan->settings = $user->plan_settings;
        }

        $user->billing = json_decode($user->billing ?? '');

        /* Get lat long of user for map card */
        /* Detect the location */
        try {
            if($user->ip) {
                $maxmind = (new \MaxMind\Db\Reader(APP_PATH . 'includes/GeoLite2-City.mmdb'))->get($user->ip);
            }
        } catch(\Exception $exception) {
            /* :) */
        }
        /* Detect extra details about the user */
        $user_location = [
            'latitude' => $maxmind['location']['latitude'] ?? null,
            'longitude' => $maxmind['location']['longitude'] ?? null,
        ];

        /* Total earned */
        $payments_total_earned = in_array(settings()->license->type, ['Extended License', 'extended']) ? db()->where('user_id', $user->user_id)->getValue('payments', 'sum(`total_amount_default_currency`)') : 0;

        /* Main View */
        $data = [
            'user' => $user,
            'user_location' => $user_location,
            'payments_total_earned' => $payments_total_earned,
            'monitors' => $monitors,
            'heartbeats' => $heartbeats,
            'domain_names' => $domain_names,
            'dns_monitors' => $dns_monitors,
            'server_monitors' => $server_monitors,
            'game_servers' => $game_servers,
            'status_pages' => $status_pages,
            'incidents' => $incidents,
            'projects' => $projects,
            'domains' => $domains,
            'notification_handlers' => $notification_handlers,
            'payments' => $payments,
        ];

        $view = new \Altum\View('admin/user-view/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
