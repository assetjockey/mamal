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

class ApiVisitors extends Controller {
    use Apiable;

    public function index() {

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                /* Detect if we only need an object, or the whole list */
                if(isset($this->params[0])) {
                    $this->get();
                } else {
                    $this->get_all();
                }

                break;

            case 'POST':

                /* Detect what method to use */
                if(isset($this->params[0])) {
                    $this->patch();
                }

                break;

            case 'DELETE':
                $this->delete();
                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters([], [], []));
        $filters->set_default_order_by('visitor_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `websites_visitors` LEFT JOIN `websites` ON `websites`.`website_id` = `websites_visitors`.`website_id` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/visitors?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                `websites_visitors`.*
            FROM
                `websites_visitors`
            LEFT JOIN `websites` ON `websites`.`website_id` = `websites_visitors`.`website_id`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");


        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->visitor_id,
                'visitor_uuid' => bin2hex($row->visitor_uuid_binary),
                'website_id' => (int) $row->website_id,
                'goals_conversions_ids' => json_decode($row->goals_conversions_ids ?? ''),
                'ip' => $row->ip,
                'custom_parameters' => json_decode($row->custom_parameters ?? ''),
                'continent_code' => $row->continent_code,
                'country_code' => $row->country_code,
                'region_name' => $row->region_name,
                'city_name' => $row->city_name,
                'os_name' => $row->os_name,
                'os_version' => $row->os_version,
                'browser_name' => $row->browser_name,
                'browser_version' => $row->browser_version,
                'browser_language' => $row->browser_language,
                'browser_timezone' => $row->browser_timezone,
                'device_type' => $row->device_type,
                'theme' => $row->theme,
                'total_sessions' => (int) $row->total_sessions,
                'last_event_id' => (int) $row->last_event_id,
                'last_date' => $row->last_date,
                'date' => $row->date,
            ];

            $data[] = $row;
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

        $visitor_id = isset($this->params[0]) ? $this->params[0] : null;
        if(ctype_digit($visitor_id)) {
            $visitor_id = (int) $visitor_id;
            $type = 'visitor_id';
        } else {
            $visitor_id = hex2bin(input_clean($visitor_id, 32));
            $type = 'visitor_uuid_binary';
        }

        /* Try to get details about the resource id */
        $visitor = db()->where($type, $visitor_id)->getOne('websites_visitors');

        /* We haven't found the resource */
        if(!$visitor) {
            $this->return_404();
        }

        /* Access check */
        if(!db()->where('user_id', $this->user->user_id)->where('website_id', $visitor->website_id)->has('websites')) {
            $this->return_404();
        }

        $visitor->custom_parameters = json_decode($visitor->custom_parameters ?? '');
        $visitor->goals_conversions_ids = json_decode($visitor->goals_conversions_ids ?? '');

        /* Prepare the data */
        $data = [
            'id' => (int) $visitor->visitor_id,
            'visitor_uuid' => bin2hex($visitor->visitor_uuid_binary),
            'website_id' => (int) $visitor->website_id,
            'goals_conversions_ids' => $visitor->goals_conversions_ids,
            'ip' => $visitor->ip,
            'custom_parameters' => $visitor->custom_parameters,
            'continent_code' => $visitor->continent_code,
            'country_code' => $visitor->country_code,
            'region_name' => $visitor->region_name,
            'city_name' => $visitor->city_name,
            'os_name' => $visitor->os_name,
            'os_version' => $visitor->os_version,
            'browser_name' => $visitor->browser_name,
            'browser_version' => $visitor->browser_version,
            'browser_language' => $visitor->browser_language,
            'browser_timezone' => $visitor->browser_timezone,
            'device_type' => $visitor->device_type,
            'theme' => $visitor->theme,
            'total_sessions' => (int) $visitor->total_sessions,
            'last_event_id' => (int) $visitor->last_event_id,
            'last_date' => $visitor->last_date,
            'date' => $visitor->date,
        ];

        Response::jsonapi_success($data);

    }

    private function patch() {

        $visitor_id = isset($this->params[0]) ? $this->params[0] : null;
        if(ctype_digit($visitor_id)) {
            $visitor_id = (int) $visitor_id;
            $type = 'visitor_id';
        } else {
            $visitor_id = hex2bin(input_clean($visitor_id, 32));
            $type = 'visitor_uuid_binary';
        }

        /* Try to get details about the resource id */
        $visitor = db()->where($type, $visitor_id)->getOne('websites_visitors');

        /* We haven't found the resource */
        if(!$visitor) {
            $this->return_404();
        }

        /* Access check */
        if(!db()->where('user_id', $this->user->user_id)->where('website_id', $visitor->website_id)->has('websites')) {
            $this->return_404();
        }

        $visitor->custom_parameters = json_decode($visitor->custom_parameters ?? '');

        $custom_parameters = [];

        /* Filter some of the variables */
        if(!isset($_POST['custom_parameter_key'])) {
            $_POST['custom_parameter_key'] = [];
            $_POST['custom_parameter_value'] = [];
            $custom_parameters = $visitor->custom_parameters;
        }

        $i = 0;
        foreach($_POST['custom_parameter_key'] as $key => $value) {
            if(empty(trim($value))) continue;

            $custom_parameter_key = input_clean($value, 64);
            $custom_parameter_value = input_clean($_POST['custom_parameter_value'][$key], 512);

            $custom_parameters[$custom_parameter_key] = $custom_parameter_value;

            if($i++ >= 20) {
                break;
            }
        }

        /* Database query */
        db()->where('visitor_id', $visitor->visitor_id)->update('websites_visitors', [
            'custom_parameters' => json_encode($custom_parameters),
            'last_date' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('visitor?visitor_uuid=' . md5($visitor->visitor_uuid_binary));

        /* Prepare the data */
        $data = [
            'id' => (int) $visitor->visitor_id,
            'visitor_uuid' => bin2hex($visitor->visitor_uuid_binary),
            'website_id' => (int) $visitor->website_id,
            'goals_conversions_ids' => json_decode($visitor->goals_conversions_ids ?? ''),
            'ip' => $visitor->ip,
            'custom_parameters' => $custom_parameters,
            'continent_code' => $visitor->continent_code,
            'country_code' => $visitor->country_code,
            'region_name' => $visitor->region_name,
            'city_name' => $visitor->city_name,
            'os_name' => $visitor->os_name,
            'os_version' => $visitor->os_version,
            'browser_name' => $visitor->browser_name,
            'browser_version' => $visitor->browser_version,
            'browser_language' => $visitor->browser_language,
            'browser_timezone' => $visitor->browser_timezone,
            'device_type' => $visitor->device_type,
            'theme' => $visitor->theme,
            'total_sessions' => (int) $visitor->total_sessions,
            'last_event_id' => (int) $visitor->last_event_id,
            'last_date' => $visitor->last_date,
            'date' => $visitor->date,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $visitor_id = isset($this->params[0]) ? $this->params[0] : null;
        if(ctype_digit($visitor_id)) {
            $visitor_id = (int) $visitor_id;
            $type = 'visitor_id';
        } else {
            $visitor_id = hex2bin(input_clean($visitor_id, 32));
            $type = 'visitor_uuid_binary';
        }

        /* Try to get details about the resource id */
        $visitor = db()->where($type, $visitor_id)->getOne('websites_visitors');

        /* We haven't found the resource */
        if(!$visitor) {
            $this->return_404();
        }

        /* Access check */
        if(!db()->where('user_id', $this->user->user_id)->where('website_id', $visitor->website_id)->has('websites')) {
            $this->return_404();
        }

        /* Database query */
        db()->where('visitor_id', $visitor->visitor_id)->delete('websites_visitors');

        /* Clear the cache */
        cache()->deleteItem('visitor?visitor_uuid=' . md5($visitor->visitor_uuid_binary));

        http_response_code(200);
        die();

    }
}
