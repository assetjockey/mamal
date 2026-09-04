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

class NewsletterView extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        \Altum\Authentication::guard();

        $newsletter_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$newsletter = db()->where('newsletter_id', $newsletter_id)->where('user_id', $this->user->user_id)->getOne('newsletters')) {
            redirect('newsletters');
        }

        $newsletter->subscribers_ids = implode(',', json_decode($newsletter->subscribers_ids ?? '[]'));
        $newsletter->settings = json_decode($newsletter->settings ?? '');

        $start_date = (new \DateTime($_GET['start_date'] ?? $newsletter->datetime))->format('Y-m-d');
        $end_date = (new \DateTime($_GET['end_date'] ?? 'now'))->format('Y-m-d');
        $datetime = \Altum\Date::get_start_end_dates_new($start_date, $end_date);

        /* Get statistics */
        $statistics_chart = [];
        $subscribers = [];
        $clicks = [];

        if(settings()->newsletters->statistics_is_enabled ?? true) {
            $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);
            $result = database()->query("
                SELECT 
                    `type`,
                    COUNT(*) AS `total`,
                    DATE_FORMAT({$convert_tz_sql}, '{$datetime['query_date_format']}') AS `formatted_date`
                FROM 
                    `newsletters_statistics`
                WHERE 
                    `newsletter_id` = {$newsletter->newsletter_id} 
                    AND `user_id` = {$this->user->user_id}
                    AND {$convert_tz_sql} BETWEEN '{$datetime['query_start_date']}' 
                    AND '{$datetime['query_end_date']}' 
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

            /* Get latest views */
            $subscribers_result = database()->query("
                SELECT
                    `newsletter_subscribers`.`newsletter_subscriber_id`, `newsletter_subscribers`.`name`, `newsletter_subscribers`.`email`, `newsletters_statistics`.`datetime`
                FROM
                    `newsletters_statistics`
                LEFT JOIN
                    `newsletter_subscribers` ON `newsletters_statistics`.`newsletter_subscriber_id` = `newsletter_subscribers`.`newsletter_subscriber_id`
                WHERE
                    `newsletters_statistics`.`newsletter_id` = {$newsletter->newsletter_id}
                    AND `newsletters_statistics`.`user_id` = {$this->user->user_id}
                    AND `newsletters_statistics`.`type` = 'view'
                ORDER BY
                    `newsletters_statistics`.`id` DESC
                LIMIT 5
            ");
            while($row = $subscribers_result->fetch_object()) {
                $subscribers[] = $row;
            }

            /* Get link clicks */
            $clicks_result = database()->query("
                SELECT
                    `target`, COUNT(*) AS `clicks`
                FROM
                    `newsletters_statistics`
                WHERE
                    `newsletter_id` = {$newsletter->newsletter_id}
                    AND `user_id` = {$this->user->user_id}
                    AND `type` = 'click'
                    AND `target` IS NOT NULL
                GROUP BY
                    `target`
            ");
            while($row = $clicks_result->fetch_object()) {
                $clicks[] = $row;
            }
        }

        $statistics_chart = get_chart_data($statistics_chart);

        /* Main View */
        $data = [
            'newsletter_id' => $newsletter_id,
            'newsletter' => $newsletter,
            'datetime' => $datetime,
            'statistics_chart' => $statistics_chart,
            'clicks' => $clicks,
            'subscribers' => $subscribers,
        ];

        $view = new \Altum\View(\Altum\Plugin::get('newsletters')->path . 'views/newsletter-view/index', (array) $this, true);

        $this->add_view_content('content', $view->run($data));

    }

}
