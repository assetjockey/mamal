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
use Altum\AnalyticsFilters;
use Altum\Models\SessionsReplays;

defined('ALTUMCODE') || die();

class Replays extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!$this->website || !settings()->analytics->sessions_replays_is_enabled || ($this->website && $this->website->tracking_type == 'lightweight')) {
            redirect('websites');
        }

        /* Establish the start and end date for the statistics */
        list($start_date, $end_date) = AnalyticsFilters::get_date();

        $datetime = \Altum\Date::get_start_end_dates_new($start_date, $end_date);

        /* Filters */
        $filters = AnalyticsFilters::get_filters_sql(['websites_visitors']);

        /* Prepare the paginator */
        $replays_data = database()->query("
            SELECT
                COUNT(DISTINCT `sessions_replays`.`session_id`) AS `total`,
                AVG(`sessions_replays`.`events`) AS `average_events`
            FROM
                `visitors_sessions`
            LEFT JOIN
                `sessions_replays` ON `sessions_replays`.`session_id` = `visitors_sessions`.`session_id`
            LEFT JOIN
            	`websites_visitors` ON `visitors_sessions`.`visitor_id` = `websites_visitors`.`visitor_id`
            WHERE
                `visitors_sessions`.`website_id` = {$this->website->website_id}
                AND `sessions_replays`.`session_id` IS NOT NULL
                AND `visitors_sessions`.`date` >= '{$datetime['query_start_date']}' AND `visitors_sessions`.`date` < '{$datetime['query_end_date']}'
                {$filters}
        ")->fetch_object();
        $paginator = (new \Altum\Paginator($replays_data->total ?? 0, settings()->main->default_results_per_page, $_GET['page'] ?? 1, url('replays?page={{PAGE}}')));

        /* Duration average */
        $total_duration = 0;

        /* Get the websites list for the user */
        $replays = [];
        $replays_result = database()->query("
            SELECT
                `visitors_sessions`.`session_id`,
                `websites_visitors`.`visitor_uuid_binary`,
                `websites_visitors`.`custom_parameters`,
                `websites_visitors`.`ip`,
                `websites_visitors`.`visitor_id`,
                `websites_visitors`.`date`,
                `websites_visitors`.`country_code`,
                `websites_visitors`.`city_name`,
                `websites_visitors`.`device_type`,
                `websites_visitors`.`os_name`,
                `websites_visitors`.`browser_name`,

                `sessions_replays`.`replay_id`,
                `sessions_replays`.`events`,
                `sessions_replays`.`datetime`,
                `sessions_replays`.`last_datetime`,
                `sessions_replays`.`expiration_date`,
                `sessions_replays`.`size`
            FROM
            	`visitors_sessions`
            LEFT JOIN
                `sessions_replays` ON `sessions_replays`.`session_id` = `visitors_sessions`.`session_id`
            LEFT JOIN
            	`websites_visitors` ON `visitors_sessions`.`visitor_id` = `websites_visitors`.`visitor_id`
            WHERE
			     `visitors_sessions`.`website_id` = {$this->website->website_id}
			     AND `sessions_replays`.`session_id` IS NOT NULL
			     AND `visitors_sessions`.`date` >= '{$datetime['query_start_date']}' AND `visitors_sessions`.`date` < '{$datetime['query_end_date']}'
			     {$filters}
			GROUP BY
				`visitors_sessions`.`session_id`
			ORDER BY
				`visitors_sessions`.`session_id` DESC

            {$paginator->get_sql_limit()}
        ");
        while($row = $replays_result->fetch_object()) {
            $row->duration = (new \DateTime($row->last_datetime))->getTimestamp() - (new \DateTime($row->datetime))->getTimestamp();
            $total_duration += $row->duration;
            $replays[] = $row;
        }

        /* Calculate average duration */
        $average_duration = count($replays) ? $total_duration / count($replays) : 0;

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'datetime' => $datetime,
            'replays_data' => $replays_data,
            'replays' => $replays,
            'pagination' => $pagination,
            'average_duration' => $average_duration,
        ];

        $view = new \Altum\View('replays/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        if(!$this->website || ($this->website && $this->website->tracking_type == 'lightweight')) {
            redirect('websites');
        }

        if($this->team) {
            redirect('websites');
        }

        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('replays');
        }

        if(!isset($_POST['type'])) {
            redirect('replays');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            set_time_limit(0);

            session_write_close();

            $_POST['selected'] = is_array($_POST['selected']) ? array_filter(array_unique(array_map('intval', $_POST['selected']))) : [];

            switch($_POST['type']) {
                case 'delete':

                    foreach($_POST['selected'] as $replay_id) {
                        /* Database query */
                        if(db()->where('replay_id', $replay_id)->where('website_id', $this->website->website_id)->has('sessions_replays')) {
                            (new SessionsReplays())->delete($replay_id);
                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('replays');
    }

    public function delete() {

        if(!$this->website || ($this->website && $this->website->tracking_type == 'lightweight')) {
            redirect('websites');
        }

        if($this->team) {
            redirect('websites');
        }

        \Altum\Authentication::guard();

        if (empty($_POST)) {
            throw_404();
        }

        $replay_id = (int) $_POST['replay_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!db()->where('replay_id', $replay_id)->where('website_id', $this->website->website_id)->has('sessions_replays')) {
            redirect('replays');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new SessionsReplays())->delete($replay_id);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.delete2'));
        }

        redirect('replays');
    }
}
