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

use Altum\Response;

defined('ALTUMCODE') || die();

class AdminIndex extends Controller {

    public function index() {

        if(settings()->internal_notifications->admins_is_enabled) {
            $internal_notifications = db()->where('for_who', 'admin')->orderBy('internal_notification_id', 'DESC')->get('internal_notifications', 5);

            $should_set_all_read = false;
            foreach($internal_notifications as $notification) {
                if(!$notification->is_read) $should_set_all_read = true;
            }

            if($should_set_all_read) {
                db()->where('for_who', 'admin')->update('internal_notifications', [
                    'is_read' => 1,
                    'read_datetime' => get_date(),
                ]);
            }
        }

        /* Requested plan details */
        $plans = (new \Altum\Models\Plan())->get_plans();

        /* Main View */
        $data = [
            'plans' => $plans,
            'internal_notifications' => $internal_notifications ?? [],
            'payment_processors' => require APP_PATH . 'includes/payment_processors.php',
        ];

        $view = new \Altum\View('admin/index/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function get_stats_ajax() {

        session_write_close();
        
        if($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw_404();
        }

        set_time_limit(0);

        /* Get stats */
        $ai_qr_codes = db()->getValue('ai_qr_codes', 'count(`ai_qr_code_id`)');
        $qr_codes = db()->getValue('qr_codes', 'count(`qr_code_id`)');
        $links = db()->getValue('links', 'count(`link_id`)');
        $barcodes = db()->getValue('barcodes', 'count(`barcode_id`)');
        $domains = db()->getValue('domains', 'count(`domain_id`)');
        $users = db()->getValue('users', 'count(`user_id`)');

        if(in_array(settings()->license->type, ['Extended License', 'extended'])) {
            $payments = db()->getValue('payments', 'count(`id`)');
            $payments_total_amount = db()->getValue('payments', 'sum(`total_amount_default_currency`)');
        } else {
            $payments = $payments_total_amount = 0;
        }

        /* Widgets stats: current month */
        $ai_qr_codes_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('ai_qr_codes', 'count(*)');
        $qr_codes_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('qr_codes', 'count(*)');
        $barcodes_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('barcodes', 'count(*)');
        $links_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('links', 'count(*)');
        $domains_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('domains', 'count(*)');
        $users_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('users', 'count(*)');
        $payments_current_month = in_array(settings()->license->type, ['Extended License', 'extended']) ? db()->where('datetime', date('Y-m-01'), '>=')->getValue('payments', 'count(*)') : 0;
        $payments_amount_current_month = in_array(settings()->license->type, ['Extended License', 'extended']) ? db()->where('datetime', date('Y-m-01'), '>=')->getValue('payments', 'sum(`total_amount_default_currency`)') : 0;

        /* Get currently active users */
        $fifteen_minutes_ago_datetime = (new \DateTime())->modify('-15 minutes')->format('Y-m-d H:i:s');

        /* Get online users list */
        $online_users = db()
            ->where('last_activity', $fifteen_minutes_ago_datetime, '>=')
            ->get('users', null, [
                'user_id',
                'name',
                'email',
                'latitude',
                'longitude',
                'city_name',
                'country',
                'device_type',
                'last_activity'
            ]);

        foreach($online_users as &$user) {
            $user->user_id = (int) $user->user_id;
            $user->latitude = (float) $user->latitude;
            $user->longitude = (float) $user->longitude;
            $user->city_name = $user->city_name ?: l('global.unknown');
            $user->country = $user->country ? get_country_from_country_code($user->country) : l('global.unknown');
            $user->active_ago = \Altum\Date::get_timeago($user->last_activity);
            $user->device_type = l('global.device.' . $user->device_type);
            unset($user->last_activity);
        }
        unset($user);

        /* Active users count */
        $active_users = count($online_users);

        /* Prepare the data */
        $data = [
            'qr_codes' => $qr_codes,
            'links' => $links,
            'ai_qr_codes' => $ai_qr_codes,
            'barcodes' => $barcodes,
            'domains' => $domains,
            'payments_total_amount' => $payments_total_amount,
            'users' => $users,
            'payments' => $payments,

            'ai_qr_codes_current_month' => $ai_qr_codes_current_month,
            'qr_codes_current_month' => $qr_codes_current_month,
            'links_current_month' => $links_current_month,
            'barcodes_current_month' => $barcodes_current_month,
            'domains_current_month' => $domains_current_month,
            'users_current_month' => $users_current_month,
            'payments_current_month' => $payments_current_month,
            'payments_amount_current_month' => $payments_amount_current_month,

            'active_users' => $active_users,
            'online_users' => $online_users,
        ];

        /* Set a nice success message */
        Response::json('', 'success', $data);

    }

}
