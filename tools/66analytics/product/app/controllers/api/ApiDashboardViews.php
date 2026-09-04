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

class ApiDashboardViews extends Controller {
    use Apiable;

    public function index() {

        $this->verify_request();

        if(!settings()->analytics->dashboard_views_is_enabled) {
            $this->return_404();
        }

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
        $filters = (new \Altum\Filters(['website_id', 'user_id'], ['name'], ['dashboard_view_id', 'website_id', 'user_id', 'name', 'last_datetime', 'datetime'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by('dashboard_view_id', isset($this->user->preferences->default_order_type) ? $this->user->preferences->default_order_type : settings()->main->default_order_type);
        $filters->set_default_results_per_page(isset($this->user->preferences->default_results_per_page) ? $this->user->preferences->default_results_per_page : settings()->main->default_results_per_page);
        $filters->process();

        $page = isset($_GET['page']) ? $_GET['page'] : 1;

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `dashboard_views` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $page, url('api/dashboard-views?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `dashboard_views`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {
            $data[] = $this->process_dashboard_view($row);
        }

        /* Prepare the data */
        $meta = [
            'page' => $page,
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
            'self' => $paginator->getPageUrl($page)
        ]];

        Response::jsonapi_success($data, $meta, 200, $others);
    }

    private function get() {

        $dashboard_view_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $dashboard_view = db()->where('dashboard_view_id', $dashboard_view_id)->where('user_id', $this->user->user_id)->getOne('dashboard_views');

        /* We haven't found the resource */
        if(!$dashboard_view) {
            $this->return_404();
        }

        Response::jsonapi_success($this->process_dashboard_view($dashboard_view));

    }

    private function post() {

        /* Check for any errors */
        $required_fields = ['website_id', 'name', 'filters'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['website_id'] = (int) $_POST['website_id'];
        $_POST['name'] = input_clean($_POST['name'], 64);

        if(empty($_POST['name'])) {
            $this->response_error(l('global.error_message.empty_fields'), 401);
        }

        $website = db()->where('website_id', $_POST['website_id'])->where('user_id', $this->user->user_id)->getOne('websites', ['website_id', 'tracking_type']);

        if(!$website) {
            $this->return_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('website_id', $website->website_id)->where('user_id', $this->user->user_id)->getValue('dashboard_views', 'count(*)');

        if($this->user->plan_settings->dashboard_views_limit != -1 && $total_rows >= $this->user->plan_settings->dashboard_views_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        $_POST['filters'] = $this->process_filters($_POST['filters'], $website->tracking_type);

        /* Database query */
        $dashboard_view_id = db()->insert('dashboard_views', [
            'user_id' => $this->user->user_id,
            'website_id' => $website->website_id,
            'name' => $_POST['name'],
            'filters' => $_POST['filters'],
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('dashboard_views?website_id=' . $website->website_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $dashboard_view_id,
            'website_id' => (int) $website->website_id,
            'user_id' => (int) $this->user->user_id,
            'name' => $_POST['name'],
            'filters' => json_decode($_POST['filters']),
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $dashboard_view_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $dashboard_view = db()->where('dashboard_view_id', $dashboard_view_id)->where('user_id', $this->user->user_id)->getOne('dashboard_views');

        /* We haven't found the resource */
        if(!$dashboard_view) {
            $this->return_404();
        }

        /* Check website ownership */
        if(isset($_POST['website_id'])) {
            $_POST['website_id'] = (int) $_POST['website_id'];

            $website = db()->where('website_id', $_POST['website_id'])->where('user_id', $this->user->user_id)->getOne('websites', ['website_id', 'tracking_type']);

            if(!$website) {
                $this->return_404();
            }
        } else {
            $website = db()->where('website_id', $dashboard_view->website_id)->where('user_id', $this->user->user_id)->getOne('websites', ['website_id', 'tracking_type']);
        }

        if(!$website) {
            $this->return_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('website_id', $website->website_id)->where('user_id', $this->user->user_id)->getValue('dashboard_views', 'count(*)');

        if($this->user->plan_settings->dashboard_views_limit != -1 && $website->website_id != $dashboard_view->website_id && $total_rows >= $this->user->plan_settings->dashboard_views_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        if($this->user->plan_settings->dashboard_views_limit != -1 && $website->website_id == $dashboard_view->website_id && $total_rows > $this->user->plan_settings->dashboard_views_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->dashboard_views_limit, mb_strtolower(l('dashboard_views.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $_POST['name'] = isset($_POST['name']) ? input_clean($_POST['name'], 64) : $dashboard_view->name;

        if(empty($_POST['name'])) {
            $this->response_error(l('global.error_message.empty_fields'), 401);
        }

        $has_new_filters = isset($_POST['filters']);
        $filters = $dashboard_view->filters;

        if($has_new_filters) {
            $filters = $this->process_filters($_POST['filters'], $website->tracking_type);
        }

        if($website->website_id != $dashboard_view->website_id && !$has_new_filters) {
            $filters = $this->process_filters($dashboard_view->filters, $website->tracking_type);
        }

        $_POST['filters'] = $filters;

        /* Database query */
        db()->where('dashboard_view_id', $dashboard_view->dashboard_view_id)->where('user_id', $this->user->user_id)->update('dashboard_views', [
            'website_id' => $website->website_id,
            'name' => $_POST['name'],
            'filters' => $_POST['filters'],
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('dashboard_views?website_id=' . $dashboard_view->website_id);
        if($website->website_id != $dashboard_view->website_id) {
            cache()->deleteItemsByTag('dashboard_views?website_id=' . $website->website_id);
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $dashboard_view->dashboard_view_id,
            'website_id' => (int) $website->website_id,
            'user_id' => (int) $this->user->user_id,
            'name' => $_POST['name'],
            'filters' => json_decode($_POST['filters']),
            'last_datetime' => get_date(),
            'datetime' => $dashboard_view->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $dashboard_view_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $dashboard_view = db()->where('dashboard_view_id', $dashboard_view_id)->where('user_id', $this->user->user_id)->getOne('dashboard_views');

        /* We haven't found the resource */
        if(!$dashboard_view) {
            $this->return_404();
        }

        /* Database query */
        db()->where('dashboard_view_id', $dashboard_view->dashboard_view_id)->where('user_id', $this->user->user_id)->delete('dashboard_views');

        /* Clear the cache */
        cache()->deleteItemsByTag('dashboard_views?website_id=' . $dashboard_view->website_id);

        http_response_code(200);
        die();

    }

    private function process_dashboard_view($dashboard_view) {

        /* Prepare the data */
        return [
            'id' => (int) $dashboard_view->dashboard_view_id,
            'website_id' => (int) $dashboard_view->website_id,
            'user_id' => (int) $dashboard_view->user_id,
            'name' => $dashboard_view->name,
            'filters' => json_decode($dashboard_view->filters),
            'last_datetime' => $dashboard_view->last_datetime,
            'datetime' => $dashboard_view->datetime,
        ];

    }

    private function process_filters($filters, $tracking_type) {
        $filters = json_decode($filters);

        if(!$filters || !is_array($filters)) {
            $this->response_error(l('dashboard_views.error_message.filters'), 401);
        }

        /* Get available filters */
        $available_filters = $tracking_type == 'lightweight' ? \Altum\AnalyticsFilters::$lightweight_events : array_merge(\Altum\AnalyticsFilters::$websites_visitors, \Altum\AnalyticsFilters::$sessions_events);
        $processed_filters = [];

        foreach($filters as $filter) {
            if(!isset($filter->by, $filter->rule, $filter->value)) {
                continue;
            }

            $filter->by = query_clean($filter->by);
            $filter->rule = query_clean($filter->rule);
            $filter->value = query_clean((string) $filter->value);

            if(!in_array($filter->by, $available_filters)) {
                continue;
            }

            if(!in_array($filter->rule, [
                'is',
                'is_not',
                'contains',
                'starts_with',
                'ends_with'
            ])) {
                continue;
            }

            if(trim($filter->value) === '') {
                continue;
            }

            $processed_filters[] = [
                'by' => $filter->by,
                'rule' => $filter->rule,
                'value' => $filter->value,
            ];
        }

        if(!count($processed_filters)) {
            $this->response_error(l('dashboard_views.error_message.filters'), 401);
        }

        return json_encode($processed_filters);
    }

}
