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
        $domains = db()->getValue('domains', 'count(`domain_id`)');
        $websites = db()->getValue('websites', 'count(`website_id`)');
        $campaigns = db()->getValue('campaigns', 'count(`campaign_id`)');
        $subscribers = db()->getValue('websites', 'sum(`total_subscribers`)');
        $total_sent_push_notifications = db()->getValue('websites', 'SUM(`total_sent_campaigns`)');
        $users = db()->getValue('users', 'count(`user_id`)');

        if(in_array(settings()->license->type, ['Extended License', 'extended'])) {
            $payments = db()->getValue('payments', 'count(`id`)');
            $payments_total_amount = db()->getValue('payments', 'sum(`total_amount_default_currency`)');
        } else {
            $payments = $payments_total_amount = 0;
        }

        /* Widgets stats: current month */
        $domains_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('domains', 'count(*)');
        $websites_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('websites', 'count(*)');
        $campaigns_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('campaigns', 'count(*)');
        $subscribers_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('subscribers', 'count(*)');
        $sent_push_notifications_current_month = db()->getValue('users', 'sum(`pusher_sent_push_notifications_current_month`)');
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
            'websites' => $websites,
            'campaigns' => $campaigns,
            'subscribers' => $subscribers,
            'domains' => $domains,
            'total_sent_push_notifications' => $total_sent_push_notifications,
            'payments_total_amount' => $payments_total_amount,
            'users' => $users,
            'payments' => $payments,

            'domains_current_month' => $domains_current_month,
            'websites_current_month' => $websites_current_month,
            'campaigns_current_month' => $campaigns_current_month,
            'subscribers_current_month' => $subscribers_current_month,
            'sent_push_notifications_current_month' => $sent_push_notifications_current_month,
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
