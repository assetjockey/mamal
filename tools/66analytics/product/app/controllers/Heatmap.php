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

use Altum\AnalyticsFilters;
use Altum\Date;
use Altum\Response;

defined('ALTUMCODE') || die();

class Heatmap extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!$this->website || !settings()->analytics->websites_heatmaps_is_enabled) {
            redirect('websites');
        }

        $heatmap_id = (isset($this->params[0])) ? (int) $this->params[0] : 0;
        $snapshot_type = (isset($this->params[1])) && in_array($this->params[1], ['desktop', 'tablet', 'mobile']) ? query_clean($this->params[1]) : 'desktop';
        $heatmap_data_type = (isset($this->params[2])) && in_array($this->params[2], ['clicks', 'scrolls']) ? query_clean($this->params[2]) : 'clicks';

        /* Get the Visitor basic data and make sure it exists */
        $heatmap = database()->query("SELECT * FROM `websites_heatmaps` WHERE `heatmap_id` = {$heatmap_id} AND `website_id` = {$this->website->website_id}")->fetch_object() ?? null;

        if(!$heatmap) {
            throw_404();
        };

        /* Establish the start and end date for the statistics */
        list($start_date, $end_date) = AnalyticsFilters::get_date();

        $datetime = \Altum\Date::get_start_end_dates_new($start_date, $end_date, Date::$default_timezone);

        /* Update Modal */
        $view = new \Altum\View('heatmap/heatmap_update_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Update Modal */
        $view = new \Altum\View('heatmap/heatmap_retake_snapshots_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Has snapshot */
        $has_snapshot = !!$heatmap->{'snapshot_id_' . $snapshot_type};

        /* Prepare the view */
        $data = [
            'heatmap_data_type' => $heatmap_data_type,
            'datetime'   => $datetime,
            'heatmap'   => $heatmap,
            'snapshot_type' => $snapshot_type,
            'has_snapshot' => $has_snapshot,
        ];

        $view = new \Altum\View('heatmap/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function read() {

        \Altum\Authentication::guard();

        $heatmap_id = (isset($this->params[0])) ? (int) $this->params[0] : 0;
        $snapshot_type = (isset($this->params[1])) && in_array($this->params[1], ['desktop', 'tablet', 'mobile']) ? query_clean($this->params[1]) : 'desktop';
        $heatmap_data_type = (isset($this->params[2])) && in_array($this->params[2], ['clicks', 'scrolls']) ? query_clean($this->params[2]) : 'clicks';

        /* Get snapshot data */
        $snapshot = database()->query("SELECT `snapshot_id`, `data` FROM `heatmaps_snapshots` WHERE `heatmap_id` = {$heatmap_id} AND `website_id` = {$this->website->website_id} AND `type` = '{$snapshot_type}' ORDER BY `snapshot_id` DESC LIMIT 1")->fetch_object() ?? null;

        if($snapshot) {
            /* Establish the start and end date for the statistics */
            list($start_date, $end_date) = AnalyticsFilters::get_date();

            $datetime = \Altum\Date::get_start_end_dates_new($start_date, $end_date, Date::$default_timezone);

            /* Decode the snapshot */
            $snapshot->data = json_decode(gzdecode($snapshot->data));

            /* Get all the data needed for the heatmap */
            $heatmap_data = [];
            $counter = 0;

            switch($heatmap_data_type) {
                case 'clicks':
                    $result = database()->query("SELECT `x_normalized`, `y_normalized`, `count` FROM `heatmap_snapshot_clicks` WHERE `snapshot_id` = {$snapshot->snapshot_id} AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}'");

                    while($row = $result->fetch_object()) {
                        $heatmap_data[] = [
                            (float) $row->x_normalized,
                            (float) $row->y_normalized,
                            (int) $row->count
                        ];

                        $counter += $row->count;
                    }
                    break;

                case 'scrolls':
                    $result = database()->query("SELECT `max_scroll`, COUNT(*) AS `count` FROM `heatmap_snapshot_scrolls` WHERE `snapshot_id` = {$snapshot->snapshot_id} AND `datetime` >= '{$datetime['query_start_date']}' AND `datetime` < '{$datetime['query_end_date']}' GROUP BY `max_scroll`");

                    while($row = $result->fetch_object()) {
                        $heatmap_data[] = [
                            (int) $row->max_scroll,
                            (int) $row->count
                        ];

                        $counter += $row->count;
                    }

                    break;
            }



            echo json_encode([
                'snapshot_data' => $snapshot->data,
                'heatmap_data' => $heatmap_data,
                'heatmap_data_count' => (int) $counter
            ]);

            die();
        }

    }

}
