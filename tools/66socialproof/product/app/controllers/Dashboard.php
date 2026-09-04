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

class Dashboard extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user_id($this->user->user_id);

        /* Total campaigns */
        $total_campaigns = db()->where('user_id', $this->user->user_id)->getValue('campaigns', 'count(*)');

        /* Get the latest campaigns */
        $campaigns = [];
        $campaigns_result = database()->query("
            SELECT campaigns.*, COUNT(notifications.notification_id) AS notifications_count 
            FROM `campaigns` 
            LEFT JOIN `notifications` ON campaigns.campaign_id = notifications.campaign_id 
            WHERE campaigns.user_id = {$this->user->user_id} 
            GROUP BY campaigns.campaign_id 
            ORDER BY campaigns.campaign_id DESC 
            LIMIT 5
        ");
        while($row = $campaigns_result->fetch_object()) $campaigns[] = $row;

        /* Get the latest notifications */
        $notifications = [];
        $notifications_result = database()->query("SELECT * FROM `notifications` WHERE `user_id` = {$this->user->user_id} ORDER BY `notification_id` DESC LIMIT 5");
        while($row = $notifications_result->fetch_object()) $notifications[] = $row;

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Prepare the view */
        $data = [
            'total_campaigns' => $total_campaigns,
            'campaigns' => $campaigns,
            'notifications' => $notifications,
            'domains' => $domains,
            'notifications_config' => require APP_PATH . 'includes/enabled_notifications.php',
            'notification_handlers' => $notification_handlers
        ];

        $view = new \Altum\View('dashboard/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function get_stats_ajax() {

        session_write_close();

        \Altum\Authentication::guard();

        if($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw_404();
        }

        $start_date_query = (new \DateTime())->modify('-' . (settings()->main->chart_days ?? 30) . ' day')->format('Y-m-d');
        $end_date_query = (new \DateTime())->modify('+1 day')->format('Y-m-d');

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $statistics_result_query = "
            SELECT
                COUNT(`id`) AS `impressions`,
                DATE_FORMAT({$convert_tz_sql}, '%Y-%m-%d') AS `formatted_date`
            FROM
                `track_notifications`
            WHERE   
                `user_id` = {$this->user->user_id} 
                AND `type` = 'impression'
                AND ({$convert_tz_sql} BETWEEN '{$start_date_query}' AND '{$end_date_query}')
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ";

        $notifications_chart = \Altum\Cache::cache_function_result('statistics?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function() use ($statistics_result_query) {
            $notifications_chart = [];

            $statistics_result = database()->query($statistics_result_query);

            /* Generate the raw chart data and save logs for later usage */
            while($row = $statistics_result->fetch_object()) {
                $label = \Altum\Date::get($row->formatted_date, 5, \Altum\Date::$default_timezone);

                $notifications_chart[$label] = [
                    'impressions' => $row->impressions,
                ];
            }

            return $notifications_chart;
        }, 60 * 60 * (settings()->main->chart_cache ?? 12));

        $notifications_chart = get_chart_data($notifications_chart);

        /* Widgets stats */
        $total_campaigns = \Altum\Cache::cache_function_result('campaigns_total?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function() {
            return db()->where('user_id', $this->user->user_id)->getValue('campaigns', 'count(*)');
        });

        $total_notifications = \Altum\Cache::cache_function_result('notifications_total?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function() {
            return db()->where('user_id', $this->user->user_id)->getValue('notifications', 'count(*)');
        });

        $current_month_notifications_impressions = db()->where('user_id', $this->user->user_id)->getValue('users', 'current_month_notifications_impressions');

        /* Prepare the data */
        $data = [
            'notifications_chart' => $notifications_chart,

            /* Widgets */
            'total_campaigns' => $total_campaigns ?? null,
            'total_notifications' => $total_notifications ?? null,
            'current_month_notifications_impressions' => $current_month_notifications_impressions ?? null,
        ];

        /* Set a nice success message */
        Response::json('', 'success', $data);

    }

}
