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
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiSegments extends Controller {
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
                } else {
                    $this->post();
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
        $filters = (new \Altum\Filters(['website_id', 'type'], ['name',], ['segment_id', 'name', 'datetime', 'last_datetime', 'total_subscribers'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->segments_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `segments` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/segments?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `segments`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");


        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->segment_id,
                'user_id' => (int) $row->user_id,
                'website_id' => (int) $row->website_id,
                'name' => $row->name,
                'settings' => json_decode($row->settings),
                'type' => $row->type,
                'total_subscribers' => $row->total_subscribers,
                'last_datetime' => $row->last_datetime,
                'datetime' => $row->datetime,
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

        $segment_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $segment = db()->where('segment_id', $segment_id)->where('user_id', $this->user->user_id)->getOne('segments');

        /* We haven't found the resource */
        if(!$segment) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $segment->segment_id,
            'user_id' => (int) $segment->user_id,
            'website_id' => (int) $segment->website_id,
            'name' => $segment->name,
            'settings' => json_decode($segment->settings),
            'type' => $segment->type,
            'total_subscribers' => $segment->total_subscribers,
            'last_datetime' => $segment->last_datetime,
            'datetime' => $segment->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for any errors */
        $required_fields = ['website_id', 'name',];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `segments` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->segments_limit != -1 && $total_rows >= $this->user->plan_settings->segments_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Get available websites */
        $websites = (new \Altum\Models\Website())->get_websites_by_user_id($this->user->user_id);

        /* Filter some of the variables */
        $_POST['name'] = input_clean($_POST['name'], 256);
        $_POST['website_id'] = array_key_exists($_POST['website_id'], $websites) ? (int) $_POST['website_id'] : array_key_first($websites);
        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['custom', 'filter']) ? $_POST['type'] : 'filter';

        /* Subscribers ids */
        $_POST['subscribers_ids'] = trim($_POST['subscribers_ids'] ?? '');
        $_POST['subscribers_ids'] = array_filter(array_map('intval', explode(',', $_POST['subscribers_ids'])));
        $_POST['subscribers_ids'] = array_values(array_unique($_POST['subscribers_ids']));
        $_POST['subscribers_ids'] = $_POST['subscribers_ids'] ?: [0];

        $settings = [];

        /* Get all the users needed */
        switch($_POST['type']) {

            case 'custom':
                $subscribers = db()->where('user_id', $this->user->user_id)->where('website_id', $_POST['website_id'])->where('subscriber_id', $_POST['subscribers_ids'], 'IN')->get('subscribers', null, ['subscriber_id']);
                break;

            case 'filter':

                $query = db()->where('user_id', $this->user->user_id)->where('website_id', $_POST['website_id']);

                $has_filters = false;

                /* Custom parameters */
                if(!isset($_POST['filters_custom_parameter_key'])) {
                    $_POST['filters_custom_parameter_key'] = [];
                    $_POST['filters_custom_parameter_condition'] = [];
                    $_POST['filters_custom_parameter_value'] = [];
                }

                $custom_parameters = [];

                foreach($_POST['filters_custom_parameter_key'] as $key => $value) {
                    if(empty(trim($value))) continue;
                    if($key >= 50) continue;

                    $custom_parameters[] = [
                        'key' => input_clean($value, 64),
                        'condition' => isset($_POST['filters_custom_parameter_condition'][$key]) && in_array($_POST['filters_custom_parameter_condition'][$key], ['exact', 'not_exact', 'contains', 'not_contains', 'starts_with', 'not_starts_with', 'ends_with', 'not_ends_with', 'bigger_than', 'lower_than']) ? $_POST['filters_custom_parameter_condition'][$key] : 'exact',
                        'value' => input_clean($_POST['filters_custom_parameter_value'][$key], 512)
                    ];
                }

                if(count($custom_parameters)) {
                    $has_filters = true;
                    $settings['filters_custom_parameters'] = $custom_parameters;

                    foreach($custom_parameters as $custom_parameter) {
                        $key = $custom_parameter['key'];
                        $condition = $custom_parameter['condition'];
                        $value = $custom_parameter['value'];

                        /* reference JSON value once; unquote JSON for string ops, cast for numeric ops */
                        $json_value_expression = 'JSON_UNQUOTE(JSON_EXTRACT(`custom_parameters`, \'$."'.$key.'"\'))';
                        $numeric_expression = 'CAST('.$json_value_expression.' AS DECIMAL(65,10))';

                        switch($condition) {
                            case 'exact':
                                $query->where($json_value_expression.' = \''.$value.'\'');
                                break;

                            case 'not_exact':
                                $query->where($json_value_expression.' != \''.$value.'\'');
                                break;

                            case 'contains':
                                $query->where($json_value_expression.' LIKE \'%'.$value.'%\'');
                                break;

                            case 'not_contains':
                                $query->where($json_value_expression.' NOT LIKE \'%'.$value.'%\'');
                                break;

                            case 'starts_with':
                                $query->where($json_value_expression.' LIKE \''.$value.'%\'');
                                break;

                            case 'not_starts_with':
                                $query->where($json_value_expression.' NOT LIKE \''.$value.'%\'');
                                break;

                            case 'ends_with':
                                $query->where($json_value_expression.' LIKE \'%'.$value.'\'');
                                break;

                            case 'not_ends_with':
                                $query->where($json_value_expression.' NOT LIKE \'%'.$value.'\'');
                                break;

                            case 'bigger_than':
                                $query->where($numeric_expression.' > '.(is_numeric($value) ? $value : '0'));
                                break;

                            case 'lower_than':
                                $query->where($numeric_expression.' < '.(is_numeric($value) ? $value : '0'));
                                break;
                        }
                    }
                }

                /* Datetime filters */
                $date_filters = [
                    'datetime',
                    'last_datetime',
                    'last_seen_online_datetime',
                    'last_sent_datetime',
                ];

                /* Process each datetime filter */
                foreach($date_filters as $filter) {
                    $condition_key = 'filters_' . $filter . '_condition';
                    $value_key = 'filters_' . $filter . '_value';

                    $_POST[$condition_key] = $_POST[$condition_key] ?? null;
                    $_POST[$value_key] = $_POST[$value_key] ?? null;

                    if(!in_array($_POST[$condition_key], ['within_last', 'older_than'])) {
                        continue;
                    }

                    $condition = $_POST[$condition_key];
                    $value = (int) $_POST[$value_key];

                    $has_filters = true;
                    $settings[$condition_key] = $condition;
                    $settings[$value_key] = $value;

                    switch($condition) {
                        case 'within_last':
                            if($value > 0) {
                                $query->where($filter . ' IS NOT NULL');
                                $query->where($filter . ' >= DATE_SUB(NOW(), INTERVAL ' . $value . ' DAY)');
                            }
                            break;

                        case 'older_than':
                            if($value > 0) {
                                $query->where($filter . ' IS NOT NULL');
                                $query->where($filter . ' < DATE_SUB(NOW(), INTERVAL ' . $value . ' DAY)');
                            }
                            break;

                    }
                }

                /* Subscribed on URL */
                if(!empty($_POST['filters_subscribed_on_url'])) {
                    $_POST['filters_subscribed_on_url'] = input_clean($_POST['filters_subscribed_on_url'], 2048);

                    $has_filters = true;
                    $query->where('subscribed_on_url', $_POST['filters_subscribed_on_url']);
                    $settings['filters_subscribed_on_url'] = $_POST['filters_subscribed_on_url'];
                }

                /* Cities */
                if(!empty($_POST['filters_cities'])) {
                    $_POST['filters_cities'] = is_array($_POST['filters_cities']) ? $_POST['filters_cities'] : explode(',', $_POST['filters_cities']);
                    $_POST['filters_cities'] = array_filter(array_unique($_POST['filters_cities']));

                    if(!empty($_POST['filters_cities'])) {
                        $_POST['filters_cities'] = array_map(function($city) {
                            return query_clean($city);
                        }, $_POST['filters_cities']);

                        $has_filters = true;
                        $query->where('city_name', $_POST['filters_cities'], 'IN');
                        $settings['filters_cities'] = $_POST['filters_cities'];
                    }
                }

                /* Countries */
                if(isset($_POST['filters_countries'])) {
                    $_POST['filters_countries'] = array_filter($_POST['filters_countries'] ?? [], function($country) {
                        return array_key_exists($country, get_countries_array());
                    });

                    $has_filters = true;
                    $query->where('country_code', $_POST['filters_countries'], 'IN');
                    $settings['filters_countries'] = $_POST['filters_countries'];
                }

                /* Continents */
                if(isset($_POST['filters_continents'])) {
                    $_POST['filters_continents'] = array_filter($_POST['filters_continents'] ?? [], function($country) {
                        return array_key_exists($country, get_continents_array());
                    });

                    $has_filters = true;
                    $query->where('continent_code', $_POST['filters_continents'], 'IN');
                    $settings['filters_continents'] = $_POST['filters_continents'];
                }

                /* Device type */
                if(isset($_POST['filters_device_type'])) {
                    $_POST['filters_device_type'] = array_filter($_POST['filters_device_type'] ?? [], function($device_type) {
                        return in_array($device_type, ['desktop', 'tablet', 'mobile']);
                    });

                    $has_filters = true;
                    $query->where('device_type', $_POST['filters_device_type'], 'IN');
                    $settings['filters_device_type'] = $_POST['filters_device_type'];
                }

                /* Languages */
                if(isset($_POST['filters_languages'])) {
                    $_POST['filters_languages'] = array_filter($_POST['filters_languages'], function($locale) {
                        return array_key_exists($locale, get_locale_languages_array());
                    });

                    $has_filters = true;
                    $query->where('browser_language', $_POST['filters_languages'], 'IN');
                    $settings['filters_languages'] = $_POST['filters_languages'];
                }

                /* Filters operating systems */
                if(isset($_POST['filters_operating_systems'])) {
                    $_POST['filters_operating_systems'] = array_filter($_POST['filters_operating_systems'], function($os_name) {
                        return in_array($os_name, ['iOS', 'Android', 'Windows', 'OS X', 'Linux', 'Ubuntu', 'Chrome OS']);
                    });

                    $has_filters = true;
                    $query->where('os_name', $_POST['filters_operating_systems'], 'IN');
                    $settings['filters_operating_systems'] = $_POST['filters_operating_systems'];
                }

                /* Filters browsers */
                if(isset($_POST['filters_browsers'])) {
                    $_POST['filters_browsers'] = array_filter($_POST['filters_browsers'], function($browser_name) {
                        return in_array($browser_name, ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Samsung Internet']);
                    });

                    $has_filters = true;
                    $query->where('browser_name', $_POST['filters_browsers'], 'IN');
                    $settings['filters_browsers'] = $_POST['filters_browsers'];
                }

                $subscribers = $has_filters ? $query->get('subscribers', null, ['subscriber_id']) : [];

                db()->reset();

                break;
        }

        $subscribers_ids = array_column($subscribers, 'subscriber_id');

        /* Free memory */
        unset($subscribers);

        $settings['subscribers_ids'] = $_POST['type'] == 'custom' ? $subscribers_ids : [];

        /* Database query */
        $segment_id = db()->insert('segments', [
            'website_id' => $_POST['website_id'],
            'user_id' => $this->user->user_id,
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'settings' => json_encode($settings),
            'total_subscribers' => count($subscribers_ids),
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('segments?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $segment_id,
            'user_id' => (int) $this->user->user_id,
            'website_id' => (int) $_POST['website_id'],
            'name' => $_POST['name'],
            'settings' => $settings,
            'type' => $_POST['type'],
            'total_subscribers' => count($subscribers_ids),
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('segments', 'count(`segment_id`)');

        if($this->user->plan_settings->segments_limit != -1 && $total_rows > $this->user->plan_settings->segments_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->segments_limit, mb_strtolower(l('segments.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $segment_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $segment = db()->where('segment_id', $segment_id)->where('user_id', $this->user->user_id)->getOne('segments');

        /* We haven't found the resource */
        if(!$segment) {
            $this->return_404();
        }

        $segment->settings = json_decode($segment->settings ?? '');

        /* Check for any errors */
        $required_fields = [];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Get available websites */
        $websites = (new \Altum\Models\Website())->get_websites_by_user_id($this->user->user_id);

        /* Filter some of the variables */
        $_POST['name'] = input_clean($_POST['name'] ?? $segment->name, 256);
        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['custom', 'filter']) ? $_POST['type'] : $segment->type;
        $_POST['website_id'] = isset($_POST['website_id']) && array_key_exists($_POST['website_id'], $websites) ? (int) $_POST['website_id'] : $segment->website_id;

        /* Subscribers ids */
        if(array_key_exists('subscribers_ids', $_POST)) {
            $_POST['subscribers_ids'] = trim($_POST['subscribers_ids']);
            $_POST['subscribers_ids'] = array_filter(array_map('intval', explode(',', $_POST['subscribers_ids'])));
            $_POST['subscribers_ids'] = array_values(array_unique($_POST['subscribers_ids']));
            $_POST['subscribers_ids'] = $_POST['subscribers_ids'] ?: [0];
        } else {
            $_POST['subscribers_ids'] = $segment->subscribers_ids;
        }

        $existing_settings = (array) ($segment->settings ?? []);
        $settings = $existing_settings;

        /* Get all the users needed */
        switch($_POST['type']) {

            case 'custom':
                $subscribers = db()->where('user_id', $this->user->user_id)->where('website_id', $_POST['website_id'])->where('subscriber_id', $_POST['subscribers_ids'], 'IN')->get('subscribers', null, ['subscriber_id']);
                break;

            case 'filter':

                $query = db()->where('user_id', $this->user->user_id)->where('website_id', $_POST['website_id']);

                $has_filters = false;

                /* Preserve existing filters only if not sent at all */
                $patch_filter_keys = [
                    'filters_subscribed_on_url',
                    'filters_cities',
                    'filters_countries',
                    'filters_continents',
                    'filters_device_type',
                    'filters_languages',
                    'filters_operating_systems',
                    'filters_browsers',
                    'filters_datetime_condition',
                    'filters_datetime_value',
                    'filters_last_datetime_condition',
                    'filters_last_datetime_value',
                    'filters_last_seen_online_datetime_condition',
                    'filters_last_seen_online_datetime_value',
                    'filters_last_sent_datetime_condition',
                    'filters_last_sent_datetime_value',
                ];

                foreach($patch_filter_keys as $key) {
                    if(!array_key_exists($key, $_POST) && array_key_exists($key, $existing_settings)) {
                        $_POST[$key] = $existing_settings[$key];
                    }
                }

                /* Preserve existing custom parameters only if not sent at all */
                if(
                    !array_key_exists('filters_custom_parameter_key', $_POST) &&
                    !array_key_exists('filters_custom_parameter_condition', $_POST) &&
                    !array_key_exists('filters_custom_parameter_value', $_POST) &&
                    isset($existing_settings['filters_custom_parameters'])
                ) {
                    foreach($existing_settings['filters_custom_parameters'] as $key => $custom_parameter) {
                        $_POST['filters_custom_parameter_key'][$key] = $custom_parameter['key'];
                        $_POST['filters_custom_parameter_condition'][$key] = $custom_parameter['condition'];
                        $_POST['filters_custom_parameter_value'][$key] = $custom_parameter['value'];
                    }
                }

                /* Custom parameters */
                if(!isset($_POST['filters_custom_parameter_key'])) {
                    $_POST['filters_custom_parameter_key'] = [];
                    $_POST['filters_custom_parameter_condition'] = [];
                    $_POST['filters_custom_parameter_value'] = [];
                }

                $custom_parameters = [];

                foreach($_POST['filters_custom_parameter_key'] as $key => $value) {
                    if(empty(trim($value))) continue;
                    if($key >= 50) continue;

                    $custom_parameters[] = [
                        'key' => input_clean($value, 64),
                        'condition' => isset($_POST['filters_custom_parameter_condition'][$key]) && in_array($_POST['filters_custom_parameter_condition'][$key], ['exact', 'not_exact', 'contains', 'not_contains', 'starts_with', 'not_starts_with', 'ends_with', 'not_ends_with', 'bigger_than', 'lower_than']) ? $_POST['filters_custom_parameter_condition'][$key] : 'exact',
                        'value' => input_clean($_POST['filters_custom_parameter_value'][$key], 512)
                    ];
                }

                if(count($custom_parameters)) {
                    $has_filters = true;
                    $settings['filters_custom_parameters'] = $custom_parameters;

                    foreach($custom_parameters as $custom_parameter) {
                        $key = $custom_parameter['key'];
                        $condition = $custom_parameter['condition'];
                        $value = $custom_parameter['value'];

                        /* reference JSON value once; unquote JSON for string ops, cast for numeric ops */
                        $json_value_expression = 'JSON_UNQUOTE(JSON_EXTRACT(`custom_parameters`, \'$."'.$key.'"\'))';
                        $numeric_expression = 'CAST('.$json_value_expression.' AS DECIMAL(65,10))';

                        switch($condition) {
                            case 'exact':
                                $query->where($json_value_expression.' = \''.$value.'\'');
                                break;

                            case 'not_exact':
                                $query->where($json_value_expression.' != \''.$value.'\'');
                                break;

                            case 'contains':
                                $query->where($json_value_expression.' LIKE \'%'.$value.'%\'');
                                break;

                            case 'not_contains':
                                $query->where($json_value_expression.' NOT LIKE \'%'.$value.'%\'');
                                break;

                            case 'starts_with':
                                $query->where($json_value_expression.' LIKE \''.$value.'%\'');
                                break;

                            case 'not_starts_with':
                                $query->where($json_value_expression.' NOT LIKE \''.$value.'%\'');
                                break;

                            case 'ends_with':
                                $query->where($json_value_expression.' LIKE \'%'.$value.'\'');
                                break;

                            case 'not_ends_with':
                                $query->where($json_value_expression.' NOT LIKE \'%'.$value.'\'');
                                break;

                            case 'bigger_than':
                                $query->where($numeric_expression.' > '.(is_numeric($value) ? $value : '0'));
                                break;

                            case 'lower_than':
                                $query->where($numeric_expression.' < '.(is_numeric($value) ? $value : '0'));
                                break;
                        }
                    }
                } else {
                    unset($settings['filters_custom_parameters']);
                }

                /* Datetime filters */
                $date_filters = [
                    'datetime',
                    'last_datetime',
                    'last_seen_online_datetime',
                    'last_sent_datetime',
                ];

                /* Process each datetime filter */
                foreach($date_filters as $filter) {
                    $condition_key = 'filters_' . $filter . '_condition';
                    $value_key = 'filters_' . $filter . '_value';

                    $_POST[$condition_key] = $_POST[$condition_key] ?? null;
                    $_POST[$value_key] = $_POST[$value_key] ?? null;

                    if(!in_array($_POST[$condition_key], ['within_last', 'older_than'])) {
                        unset($settings[$condition_key], $settings[$value_key]);
                        continue;
                    }

                    $condition = $_POST[$condition_key];
                    $value = (int) $_POST[$value_key];

                    if($value <= 0) {
                        unset($settings[$condition_key], $settings[$value_key]);
                        continue;
                    }

                    $has_filters = true;
                    $settings[$condition_key] = $condition;
                    $settings[$value_key] = $value;

                    switch($condition) {
                        case 'within_last':
                            $query->where($filter . ' IS NOT NULL');
                            $query->where($filter . ' >= DATE_SUB(NOW(), INTERVAL ' . $value . ' DAY)');
                            break;

                        case 'older_than':
                            $query->where($filter . ' IS NOT NULL');
                            $query->where($filter . ' < DATE_SUB(NOW(), INTERVAL ' . $value . ' DAY)');
                            break;
                    }
                }

                /* Subscribed on URL */
                if(array_key_exists('filters_subscribed_on_url', $_POST)) {
                    $_POST['filters_subscribed_on_url'] = input_clean($_POST['filters_subscribed_on_url'], 2048);

                    if($_POST['filters_subscribed_on_url'] !== '') {
                        $has_filters = true;
                        $query->where('subscribed_on_url', $_POST['filters_subscribed_on_url']);
                        $settings['filters_subscribed_on_url'] = $_POST['filters_subscribed_on_url'];
                    } else {
                        unset($settings['filters_subscribed_on_url']);
                    }
                }

                /* Cities */
                if(array_key_exists('filters_cities', $_POST)) {
                    $_POST['filters_cities'] = is_array($_POST['filters_cities']) ? $_POST['filters_cities'] : explode(',', $_POST['filters_cities']);
                    $_POST['filters_cities'] = array_filter(array_unique($_POST['filters_cities']));

                    if(!empty($_POST['filters_cities'])) {
                        $_POST['filters_cities'] = array_map(function($city) {
                            return query_clean($city);
                        }, $_POST['filters_cities']);

                        $has_filters = true;
                        $query->where('city_name', $_POST['filters_cities'], 'IN');
                        $settings['filters_cities'] = $_POST['filters_cities'];
                    } else {
                        unset($settings['filters_cities']);
                    }
                }

                /* Countries */
                if(array_key_exists('filters_countries', $_POST)) {
                    $_POST['filters_countries'] = array_filter($_POST['filters_countries'] ?? [], function($country) {
                        return array_key_exists($country, get_countries_array());
                    });

                    if(!empty($_POST['filters_countries'])) {
                        $has_filters = true;
                        $query->where('country_code', $_POST['filters_countries'], 'IN');
                        $settings['filters_countries'] = $_POST['filters_countries'];
                    } else {
                        unset($settings['filters_countries']);
                    }
                }

                /* Continents */
                if(array_key_exists('filters_continents', $_POST)) {
                    $_POST['filters_continents'] = array_filter($_POST['filters_continents'] ?? [], function($country) {
                        return array_key_exists($country, get_continents_array());
                    });

                    if(!empty($_POST['filters_continents'])) {
                        $has_filters = true;
                        $query->where('continent_code', $_POST['filters_continents'], 'IN');
                        $settings['filters_continents'] = $_POST['filters_continents'];
                    } else {
                        unset($settings['filters_continents']);
                    }
                }

                /* Device type */
                if(array_key_exists('filters_device_type', $_POST)) {
                    $_POST['filters_device_type'] = array_filter($_POST['filters_device_type'] ?? [], function($device_type) {
                        return in_array($device_type, ['desktop', 'tablet', 'mobile']);
                    });

                    if(!empty($_POST['filters_device_type'])) {
                        $has_filters = true;
                        $query->where('device_type', $_POST['filters_device_type'], 'IN');
                        $settings['filters_device_type'] = $_POST['filters_device_type'];
                    } else {
                        unset($settings['filters_device_type']);
                    }
                }

                /* Languages */
                if(array_key_exists('filters_languages', $_POST)) {
                    $_POST['filters_languages'] = array_filter($_POST['filters_languages'] ?? [], function($locale) {
                        return array_key_exists($locale, get_locale_languages_array());
                    });

                    if(!empty($_POST['filters_languages'])) {
                        $has_filters = true;
                        $query->where('browser_language', $_POST['filters_languages'], 'IN');
                        $settings['filters_languages'] = $_POST['filters_languages'];
                    } else {
                        unset($settings['filters_languages']);
                    }
                }

                /* Filters operating systems */
                if(array_key_exists('filters_operating_systems', $_POST)) {
                    $_POST['filters_operating_systems'] = array_filter($_POST['filters_operating_systems'] ?? [], function($os_name) {
                        return in_array($os_name, ['iOS', 'Android', 'Windows', 'OS X', 'Linux', 'Ubuntu', 'Chrome OS']);
                    });

                    if(!empty($_POST['filters_operating_systems'])) {
                        $has_filters = true;
                        $query->where('os_name', $_POST['filters_operating_systems'], 'IN');
                        $settings['filters_operating_systems'] = $_POST['filters_operating_systems'];
                    } else {
                        unset($settings['filters_operating_systems']);
                    }
                }

                /* Filters browsers */
                if(array_key_exists('filters_browsers', $_POST)) {
                    $_POST['filters_browsers'] = array_filter($_POST['filters_browsers'] ?? [], function($browser_name) {
                        return in_array($browser_name, ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Samsung Internet']);
                    });

                    if(!empty($_POST['filters_browsers'])) {
                        $has_filters = true;
                        $query->where('browser_name', $_POST['filters_browsers'], 'IN');
                        $settings['filters_browsers'] = $_POST['filters_browsers'];
                    } else {
                        unset($settings['filters_browsers']);
                    }
                }

                $subscribers = $has_filters ? $query->get('subscribers', null, ['subscriber_id']) : [];

                db()->reset();

                break;
        }

        $subscribers_ids = array_column($subscribers, 'subscriber_id');

        /* Free memory */
        unset($subscribers);

        $settings['subscribers_ids'] = $_POST['type'] == 'custom' ? $subscribers_ids : [];

        /* Database query */
        db()->where('segment_id', $segment->segment_id)->update('segments', [
            'website_id' => $_POST['website_id'],
            'user_id' => $this->user->user_id,
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'settings' => json_encode($settings),
            'total_subscribers' => count($subscribers_ids),
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('segment?segment_id=' . $segment->segment_id);

        /* Prepare the data */
        $data = [
            'id' => $segment->segment_id,
            'user_id' => (int) $this->user->user_id,
            'website_id' => (int) $_POST['website_id'],
            'name' => $_POST['name'],
            'settings' => $settings,
            'type' => $_POST['type'],
            'total_subscribers' => count($subscribers_ids),
            'last_datetime' => get_date(),
            'datetime' => $segment->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $segment_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $segment = db()->where('segment_id', $segment_id)->where('user_id', $this->user->user_id)->getOne('segments');

        /* We haven't found the resource */
        if(!$segment) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('segment_id', $segment_id)->delete('segments');

        http_response_code(200);
        die();

    }
}
