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
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiHeatmaps extends Controller {
    use Apiable;

    public function index() {

        $this->verify_request();

        if(!settings()->analytics->websites_heatmaps_is_enabled) {
            $this->return_404();
        }

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                if(isset($this->params[0], $this->params[1])) {
                    if($this->params[1] == 'data') {
                        $this->get_data();
                    }

                    $this->return_404();
                }

                /* Detect if we only need an object, or the whole list */
                if(isset($this->params[0])) {
                    $this->get();
                } else {
                    $this->get_all();
                }

                break;

            case 'POST':

                if(isset($this->params[1])) {
                    $this->return_404();
                }

                /* Detect what method to use */
                if(isset($this->params[0])) {
                    $this->patch();
                } else {
                    $this->post();
                }

                break;

            case 'DELETE':
                if(isset($this->params[1])) {
                    $this->return_404();
                }

                $this->delete();
                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['website_id', 'user_id', 'is_enabled'], ['name', 'path'], ['heatmap_id', 'website_id', 'name', 'path', 'is_enabled', 'desktop_size', 'tablet_size', 'mobile_size', 'last_datetime', 'datetime'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by('heatmap_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `websites_heatmaps` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/heatmaps?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `websites_heatmaps`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {
            $data[] = $this->process_heatmap($row);
        }

        /* Prepare the data */
        $meta = [
            'page' => $_GET['page'] ?? 1,
            'total_pages' => $paginator->getNumPages(),
            'results_per_page' => $filters->get_results_per_page(),
            'total_results' => (int) $total_rows,
        ];

        /* Prepare the pagination links */
        $others = ['links' => [
            'first' => $paginator->getPageUrl(1),
            'last' => $paginator->getNumPages() ? $paginator->getPageUrl($paginator->getNumPages()) : null,
            'next' => $paginator->getNextUrl(),
            'prev' => $paginator->getPrevUrl(),
            'self' => $paginator->getPageUrl($_GET['page'] ?? 1)
        ]];

        Response::jsonapi_success($data, $meta, 200, $others);
    }

    private function get() {

        $heatmap_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $heatmap = db()->where('heatmap_id', $heatmap_id)->where('user_id', $this->user->user_id)->getOne('websites_heatmaps');

        /* We haven't found the resource */
        if(!$heatmap) {
            $this->return_404();
        }

        Response::jsonapi_success($this->process_heatmap($heatmap));

    }

    private function get_data() {

        $heatmap_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $heatmap = db()->where('heatmap_id', $heatmap_id)->where('user_id', $this->user->user_id)->getOne('websites_heatmaps');

        /* We haven't found the resource */
        if(!$heatmap) {
            $this->return_404();
        }

        $snapshot_type = isset($_GET['snapshot_type']) && in_array($_GET['snapshot_type'], ['desktop', 'tablet', 'mobile']) ? query_clean($_GET['snapshot_type']) : 'desktop';
        $type = isset($_GET['type']) && in_array($_GET['type'], ['clicks', 'scrolls']) ? query_clean($_GET['type']) : 'clicks';

        /* Get snapshot data */
        $snapshot = database()->query("SELECT `snapshot_id`, `data` FROM `heatmaps_snapshots` WHERE `heatmap_id` = {$heatmap->heatmap_id} AND `website_id` = {$heatmap->website_id} AND `type` = '{$snapshot_type}' ORDER BY `snapshot_id` DESC LIMIT 1")->fetch_object() ?? null;

        if(!$snapshot) {
            $this->return_404();
        }

        $datetime = \Altum\Date::get_start_end_dates_new(null, null, \Altum\Date::$default_timezone, \Altum\Date::$default_timezone);

        /* Decode the snapshot */
        $snapshot->data = json_decode(gzdecode($snapshot->data));

        /* Get all the data needed for the heatmap */
        $heatmap_data = [];
        $counter = 0;

        switch($type) {
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

        Response::jsonapi_success([
            'id' => (int) $heatmap->heatmap_id,
            'website_id' => (int) $heatmap->website_id,
            'snapshot_id' => (int) $snapshot->snapshot_id,
            'snapshot_type' => $snapshot_type,
            'type' => $type,
            'start_date' => $datetime['start_date'],
            'end_date' => $datetime['end_date'],
            'heatmap_data' => $heatmap_data,
            'heatmap_data_count' => (int) $counter,
        ]);

    }

    private function post() {

        /* Check for any errors */
        $required_fields = ['website_id', 'name'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['website_id'] = (int) $_POST['website_id'];
        $_POST['name'] = input_clean($_POST['name'], 256);
        $_POST['path'] = !empty($_POST['path']) ? '/' . ltrim(trim(input_clean($_POST['path'])), '/') : '/';
        $_POST['is_enabled'] = (int) (isset($_POST['is_enabled']) ? (bool) $_POST['is_enabled'] : true);

        $website = db()->where('website_id', $_POST['website_id'])->where('user_id', $this->user->user_id)->getOne('websites', ['website_id', 'tracking_type']);

        if(!$website) {
            $this->return_404();
        }

        if($website->tracking_type == 'lightweight') {
            $this->response_error(l('api.error_message.no_access'), 401);
        }

        /* Check for the plan limit */
        $total_rows = db()->where('website_id', $website->website_id)->getValue('websites_heatmaps', 'count(*)');

        if($this->user->plan_settings->websites_heatmaps_limit != -1 && $total_rows >= $this->user->plan_settings->websites_heatmaps_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Database query */
        $heatmap_id = db()->insert('websites_heatmaps', [
            'user_id' => $this->user->user_id,
            'website_id' => $_POST['website_id'],
            'name' => $_POST['name'],
            'path' => $_POST['path'],
            'is_enabled' => $_POST['is_enabled'],
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('website_heatmaps?website_id=' . $_POST['website_id']);

        /* Prepare the data */
        $data = [
            'id' => (int) $heatmap_id,
            'website_id' => (int) $_POST['website_id'],
            'user_id' => (int) $this->user->user_id,
            'snapshot_id_desktop' => null,
            'desktop_size' => 0,
            'snapshot_id_tablet' => null,
            'tablet_size' => 0,
            'snapshot_id_mobile' => null,
            'mobile_size' => 0,
            'name' => $_POST['name'],
            'path' => $_POST['path'],
            'is_enabled' => (bool) $_POST['is_enabled'],
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $heatmap_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $heatmap = db()->where('heatmap_id', $heatmap_id)->where('user_id', $this->user->user_id)->getOne('websites_heatmaps');

        /* We haven't found the resource */
        if(!$heatmap) {
            $this->return_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('website_id', $heatmap->website_id)->getValue('websites_heatmaps', 'count(*)');

        if($this->user->plan_settings->websites_heatmaps_limit != -1 && $total_rows > $this->user->plan_settings->websites_heatmaps_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->websites_heatmaps_limit, mb_strtolower(l('heatmaps.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $_POST['name'] = input_clean($_POST['name'] ?? $heatmap->name, 256);
        $_POST['is_enabled'] = (int) (isset($_POST['is_enabled']) ? (bool) $_POST['is_enabled'] : $heatmap->is_enabled);

        /* Database query */
        db()->where('heatmap_id', $heatmap_id)->where('user_id', $this->user->user_id)->update('websites_heatmaps', [
            'name' => $_POST['name'],
            'is_enabled' => $_POST['is_enabled'],
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('website_heatmaps?website_id=' . $heatmap->website_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $heatmap->heatmap_id,
            'website_id' => (int) $heatmap->website_id,
            'user_id' => (int) $this->user->user_id,
            'snapshot_id_desktop' => $heatmap->snapshot_id_desktop ? (int) $heatmap->snapshot_id_desktop : null,
            'desktop_size' => (int) $heatmap->desktop_size,
            'snapshot_id_tablet' => $heatmap->snapshot_id_tablet ? (int) $heatmap->snapshot_id_tablet : null,
            'tablet_size' => (int) $heatmap->tablet_size,
            'snapshot_id_mobile' => $heatmap->snapshot_id_mobile ? (int) $heatmap->snapshot_id_mobile : null,
            'mobile_size' => (int) $heatmap->mobile_size,
            'name' => $_POST['name'],
            'path' => $heatmap->path,
            'is_enabled' => (bool) $_POST['is_enabled'],
            'last_datetime' => get_date(),
            'datetime' => $heatmap->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $heatmap_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $heatmap = db()->where('heatmap_id', $heatmap_id)->where('user_id', $this->user->user_id)->getOne('websites_heatmaps');

        /* We haven't found the resource */
        if(!$heatmap) {
            $this->return_404();
        }

        /* Database query */
        db()->where('heatmap_id', $heatmap_id)->where('user_id', $this->user->user_id)->delete('websites_heatmaps');

        /* Clear the cache */
        cache()->deleteItem('website_heatmaps?website_id=' . $heatmap->website_id);

        http_response_code(200);
        die();

    }

    private function process_heatmap($heatmap) {

        return [
            'id' => (int) $heatmap->heatmap_id,
            'website_id' => (int) $heatmap->website_id,
            'user_id' => (int) $heatmap->user_id,
            'snapshot_id_desktop' => $heatmap->snapshot_id_desktop ? (int) $heatmap->snapshot_id_desktop : null,
            'desktop_size' => (int) $heatmap->desktop_size,
            'snapshot_id_tablet' => $heatmap->snapshot_id_tablet ? (int) $heatmap->snapshot_id_tablet : null,
            'tablet_size' => (int) $heatmap->tablet_size,
            'snapshot_id_mobile' => $heatmap->snapshot_id_mobile ? (int) $heatmap->snapshot_id_mobile : null,
            'mobile_size' => (int) $heatmap->mobile_size,
            'name' => $heatmap->name,
            'path' => $heatmap->path,
            'is_enabled' => (bool) $heatmap->is_enabled,
            'last_datetime' => $heatmap->last_datetime,
            'datetime' => $heatmap->datetime,
        ];

    }

}
