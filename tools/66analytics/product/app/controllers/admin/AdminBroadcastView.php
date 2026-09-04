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

use Altum\Alerts;

defined('ALTUMCODE') || die();

class AdminBroadcastView extends Controller {

    public function index() {

        $broadcast_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$broadcast = db()->where('broadcast_id', $broadcast_id)->getOne('broadcasts')) {
            redirect('admin/broadcasts');
        }

        $broadcast->users_ids = implode(',', json_decode($broadcast->users_ids));
        $broadcast->settings = json_decode($broadcast->settings);
        $is_system_email = isset($broadcast->settings->is_system_email) ? (bool) $broadcast->settings->is_system_email : true;

        /* Prepare the content preview */
        $content = json_decode($broadcast->content) ? convert_editorjs_json_to_html($broadcast->content) : output_blog_post_content($broadcast->content);

        /* Set the selected date range */
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : (new \DateTime($broadcast->datetime))->format('Y-m-d');
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : (new \DateTime())->format('Y-m-d');
        $datetime = \Altum\Date::get_start_end_dates_new($start_date, $end_date);

        /* Get statistics */
        $statistics_chart = [];
        $convert_tz_sql = get_convert_tz_sql('`broadcasts_statistics`.`datetime`', $this->user->timezone);
        $result = database()->query("
            SELECT
                `type`,
                COUNT(*) AS `total`,
                DATE_FORMAT({$convert_tz_sql}, '{$datetime['query_date_format']}') AS `formatted_date`
            FROM
                `broadcasts_statistics`
            WHERE
                `broadcast_id` = {$broadcast->broadcast_id}
              AND `broadcasts_statistics`.`datetime` >= '{$datetime['query_start_date']}' AND `broadcasts_statistics`.`datetime` < '{$datetime['query_end_date']}'
            GROUP BY
                `formatted_date`,
                `type`
        ");

        while($row = $result->fetch_object()) {
            $row->formatted_date = $datetime['process']($row->formatted_date, true);

            $statistics_chart[$row->formatted_date] =
                isset($statistics_chart[$row->formatted_date]) ?
                [
                    'clicks' => $statistics_chart[$row->formatted_date]['clicks'] + ($row->type == 'click' ? $row->total : 0),
                    'views' => $statistics_chart[$row->formatted_date]['views'] + ($row->type == 'view' ? $row->total : 0),
                ] :
                [
                    'clicks' => $row->type == 'click' ? $row->total : 0,
                    'views' => $row->type == 'view' ? $row->total : 0
                ];
        }

        $statistics_chart = get_chart_data($statistics_chart);

        /* Get last views */
        $users = [];
        if($is_system_email) {
            $users_result = database()->query("
                SELECT
                    `users`.`user_id`, `users`.`name`, `users`.`email`, `users`.`avatar`, `broadcasts_statistics`.`datetime`
                FROM
                    `broadcasts_statistics`
                LEFT JOIN
                    `users` ON `broadcasts_statistics`.`user_id` = `users`.`user_id`
                WHERE
                    `broadcast_id` = {$broadcast->broadcast_id}
                    AND `broadcasts_statistics`.`type` = 'view'
                    AND `broadcasts_statistics`.`datetime` >= '{$datetime['query_start_date']}' AND `broadcasts_statistics`.`datetime` < '{$datetime['query_end_date']}'
                ORDER BY
                    `broadcasts_statistics`.`id` DESC
                LIMIT 5
            ");
        }

        else {
            $users_result = database()->query("
                SELECT
                    `broadcast_subscribers`.`user_id`, `broadcast_subscribers`.`name`, `broadcast_subscribers`.`email`, `users`.`avatar`, `broadcasts_statistics`.`datetime`
                FROM
                    `broadcasts_statistics`
                LEFT JOIN
                    `broadcast_subscribers` ON `broadcasts_statistics`.`broadcast_subscriber_id` = `broadcast_subscribers`.`broadcast_subscriber_id`
                LEFT JOIN
                    `users` ON `broadcast_subscribers`.`user_id` = `users`.`user_id`
                WHERE
                    `broadcast_id` = {$broadcast->broadcast_id}
                    AND `broadcasts_statistics`.`type` = 'view'
                    AND `broadcasts_statistics`.`datetime` >= '{$datetime['query_start_date']}' AND `broadcasts_statistics`.`datetime` < '{$datetime['query_end_date']}'
                ORDER BY
                    `broadcasts_statistics`.`id` DESC
                LIMIT 5
            ");
        }

        while($row = $users_result->fetch_object()) {
            $users[] = $row;
        }

        /* Get link clicks */
        $clicks = [];
        $clicks_result = database()->query("
            SELECT
                `target`, COUNT(*) AS `clicks`
            FROM
                `broadcasts_statistics`
            WHERE
                `broadcast_id` = {$broadcast->broadcast_id}
                AND `type` = 'click'
                AND `target` IS NOT NULL
                AND `broadcasts_statistics`.`datetime` >= '{$datetime['query_start_date']}' AND `broadcasts_statistics`.`datetime` < '{$datetime['query_end_date']}'
            GROUP BY
                `target`
            ORDER BY
                `clicks` DESC
        ");
        while($row = $clicks_result->fetch_object()) {
            $clicks[] = $row;
        }

        /* Main View */
        $data = [
            'broadcast_id' => $broadcast_id,
            'broadcast' => $broadcast,
            'is_system_email' => $is_system_email,
            'content' => $content,
            'datetime' => $datetime,
            'statistics_chart' => $statistics_chart,
            'clicks' => $clicks,
            'users' => $users,
        ];

        $view = new \Altum\View('admin/broadcast-view/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
