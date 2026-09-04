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
namespace Altum\Controllers;

use Altum\Models\Plan;
use Altum\Response;

defined('ALTUMCODE') || die();

class AdminUserView extends Controller {

    public function index() {

        $user_id = (isset($this->params[0])) ? (int) $this->params[0] : null;

        /* Check if user exists */
        if(!$user = db()->where('user_id', $user_id)->getOne('users')) {
            redirect('admin/users');
        }

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
        ];

        $view = new \Altum\View('admin/user-view/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function get_stats_ajax() {

        session_write_close();

        if($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw_404();
        }

        set_time_limit(0);

        $user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

        if(!$user_id) {
            throw_404();
        }

        /* Get widget stats */
        $websites = db()->where('user_id', $user_id)->getValue('websites', 'count(`website_id`)');
        $heatmaps = db()->where('user_id', $user_id)->getValue('websites_heatmaps', 'count(`heatmap_id`)');
        $replays = db()->where('user_id', $user_id)->getValue('sessions_replays', 'count(`replay_id`)');
        $teams = db()->where('user_id', $user_id)->getValue('teams', 'count(`team_id`)');
        $teams_associations = db()->where('user_id', $user_id)->getValue('teams_associations', 'count(`team_association_id`)');
        $email_reports = db()->where('user_id', $user_id)->getValue('email_reports', 'count(`id`)');
        $payments = in_array(settings()->license->type, ['Extended License', 'extended']) ? db()->where('user_id', $user_id)->getValue('payments', 'count(`id`)') : 0;
        $domains = db()->where('user_id', $user_id)->getValue('domains', 'count(`domain_id`)');

        /* Prepare the data */
        $data = [
            'websites' => $websites,
            'heatmaps' => $heatmaps,
            'replays' => $replays,
            'teams' => $teams,
            'teams_associations' => $teams_associations,
            'email_reports' => $email_reports,
            'payments' => $payments,
            'domains' => $domains,
        ];

        Response::json('', 'success', $data);

    }
}
