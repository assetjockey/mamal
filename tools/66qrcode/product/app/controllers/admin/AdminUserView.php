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

class AdminUserView extends Controller
{

    public function index()
    {

        $user_id = (isset($this->params[0])) ? (int)$this->params[0] : null;

        /* Check if user exists */
        if (!$user = db()->where('user_id', $user_id)->getOne('users')) {
            redirect('admin/users');
        }

        /* Get the current plan details */
        $user->plan = (new Plan())->get_plan_by_id($user->plan_id);

        /* Check if its a custom plan */
        if ($user->plan_id == 'custom') {
            $user->plan->settings = $user->plan_settings;
        }

        $user->billing = json_decode($user->billing ?? '');

        /* Get lat long of user for map card */
        /* Detect the location */
        try {
            if ($user->ip) {
                $maxmind = (new \MaxMind\Db\Reader(APP_PATH . 'includes/GeoLite2-City.mmdb'))->get($user->ip);
            }
        } catch (\Exception $exception) {
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

        $view = new \Altum\View('admin/user-view/index', (array)$this);

        $this->add_view_content('content', $view->run($data));

    }


    public function get_stats_ajax()
    {

        session_write_close();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw_404();
        }

        set_time_limit(0);

        $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

        if (!$user_id) {
            throw_404();
        }

        /* Get stats */
        $ai_qr_codes = db()->where('user_id', $user_id)->getValue('ai_qr_codes', 'count(`ai_qr_code_id`)');
        $qr_codes = db()->where('user_id', $user_id)->getValue('qr_codes', 'count(`qr_code_id`)');
        $barcodes = db()->where('user_id', $user_id)->getValue('barcodes', 'count(`barcode_id`)');
        $links = db()->where('user_id', $user_id)->getValue('links', 'count(`link_id`)');
        $pixels = db()->where('user_id', $user_id)->getValue('pixels', 'count(`pixel_id`)');
        $projects = db()->where('user_id', $user_id)->getValue('projects', 'count(`project_id`)');
        $domains = db()->where('user_id', $user_id)->getValue('domains', 'count(`domain_id`)');
        $payments = in_array(settings()->license->type, ['Extended License', 'extended']) ? db()->where('user_id', $user_id)->getValue('payments', 'count(`id`)') : 0;

        /* Prepare the data */
        $data = [
            'ai_qr_codes' => $ai_qr_codes,
            'qr_codes' => $qr_codes,
            'barcodes' => $barcodes,
            'links' => $links,
            'pixels' => $pixels,
            'projects' => $projects,
            'domains' => $domains,
            'payments' => $payments,
        ];

        Response::json('', 'success', $data);

    }
}
