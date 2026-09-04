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

class ApiGoals extends Controller {
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
        $filters = (new \Altum\Filters(['website_id', 'user_id', 'type'], ['name', 'path', 'key'], ['goal_id', 'last_datetime', 'datetime', 'name', 'path', 'key', 'scroll_percentage'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by('goal_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `websites_goals` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/goals?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `websites_goals`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->goal_id,
                'website_id' => (int) $row->website_id,
                'user_id' => (int) $row->user_id,
                'key' => $row->key,
                'type' => $row->type,
                'path' => $row->path,
                'scroll_percentage' => $row->scroll_percentage !== null ? (int) $row->scroll_percentage : null,
                'name' => $row->name,
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

        $goal_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $goal = db()->where('goal_id', $goal_id)->where('user_id', $this->user->user_id)->getOne('websites_goals');

        /* We haven't found the resource */
        if(!$goal) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $goal->goal_id,
            'website_id' => (int) $goal->website_id,
            'user_id' => (int) $goal->user_id,
            'key' => $goal->key,
            'type' => $goal->type,
            'path' => $goal->path,
            'scroll_percentage' => $goal->scroll_percentage !== null ? (int) $goal->scroll_percentage : null,
            'name' => $goal->name,
            'last_datetime' => $goal->last_datetime,
            'datetime' => $goal->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for any errors */
        $required_fields = ['website_id', 'name', 'type'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['website_id'] = (int) $_POST['website_id'];
        $_POST['type'] = in_array($_POST['type'], ['pageview', 'custom', 'scroll']) ? query_clean($_POST['type']) : 'pageview';
        $_POST['name'] = input_clean($_POST['name'], 32);

        switch($_POST['type']) {
            case 'pageview':
                /* Clean pageview goal */
                $_POST['path'] = '/' . trim(query_clean($_POST['path']));
                $_POST['key'] = string_generate(16);
                $_POST['scroll_percentage'] = null;
                break;

            case 'custom':
                /* Clean custom goal */
                $custom_key = trim(get_slug(query_clean($_POST['key'])));
                $_POST['key'] = empty($custom_key) ? string_generate(16) : $custom_key;
                $_POST['path'] = null;
                $_POST['scroll_percentage'] = null;
                break;

            case 'scroll':
                /* Clean scroll goal */
                $_POST['path'] = '/' . trim(query_clean($_POST['path']));
                $_POST['key'] = string_generate(16);
                $_POST['scroll_percentage'] = isset($_POST['scroll_percentage']) ? (int) $_POST['scroll_percentage'] : 0;
                break;
        }

        /* Validate scroll percentage */
        if($_POST['type'] == 'scroll' && ($_POST['scroll_percentage'] < 1 || $_POST['scroll_percentage'] > 100)) {
            $this->response_error(l('goal_create_modal.scroll_percentage_error'), 401);
        }

        if(!$website_id = db()->where('website_id', $_POST['website_id'])->where('user_id', $this->user->user_id)->getValue('websites', 'website_id')) {
            $this->return_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('website_id', $website_id)->getValue('websites_goals', 'count(*)');

        if($this->user->plan_settings->websites_goals_limit != -1 && $total_rows >= $this->user->plan_settings->websites_goals_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Database query */
        $goal_id = db()->insert('websites_goals', [
			'user_id' => $this->user->user_id,
			'website_id' => $_POST['website_id'],
            'key' => $_POST['key'],
            'type' => $_POST['type'],
            'path' => $_POST['path'],
            'scroll_percentage' => $_POST['scroll_percentage'],
            'name' => $_POST['name'],
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('website_goals?website_id=' . $_POST['website_id']);

        /* Prepare the data */
        $data = [
            'id' => (int) $goal_id,
            'website_id' => (int) $_POST['website_id'],
            'user_id' => (int) $this->user->user_id,
            'key' => $_POST['key'],
            'type' => $_POST['type'],
            'path' => $_POST['path'],
            'scroll_percentage' => $_POST['scroll_percentage'] !== null ? (int) $_POST['scroll_percentage'] : null,
            'name' => $_POST['name'],
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $goal_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $goal = db()->where('goal_id', $goal_id)->where('user_id', $this->user->user_id)->getOne('websites_goals');

        /* We haven't found the resource */
        if(!$goal) {
            $this->return_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('website_id', $goal->website_id)->getValue('websites_goals', 'count(*)');

        if($this->user->plan_settings->websites_goals_limit != -1 && $total_rows > $this->user->plan_settings->websites_goals_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->websites_goals_limit, mb_strtolower(l('goals.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $_POST['website_id'] = isset($_POST['website_id']) ? (int) $_POST['website_id'] : $goal->website_id;
        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['pageview', 'custom', 'scroll']) ? query_clean($_POST['type']) : $goal->type;
        $_POST['name'] = input_clean(isset($_POST['name']) ? $_POST['name'] : $goal->name, 32);

        switch($_POST['type']) {
            case 'pageview':
                /* Clean pageview goal */
                $clean_path = trim(query_clean(isset($_POST['path']) ? $_POST['path'] : $goal->path), '/');
                $_POST['path'] = '/' . $clean_path;
                $_POST['key'] = $goal->key ? $goal->key : string_generate(16);
                $_POST['scroll_percentage'] = null;
                break;

            case 'custom':
                /* Clean custom goal */
                $custom_key = trim(get_slug(query_clean(isset($_POST['key']) ? $_POST['key'] : $goal->key)));
                $_POST['key'] = empty($custom_key) ? ($goal->key ? $goal->key : string_generate(16)) : $custom_key;
                $_POST['path'] = null;
                $_POST['scroll_percentage'] = null;
                break;

            case 'scroll':
                /* Clean scroll goal */
                $clean_path = trim(query_clean(isset($_POST['path']) ? $_POST['path'] : $goal->path), '/');
                $_POST['path'] = '/' . $clean_path;
                $_POST['key'] = $goal->key ? $goal->key : string_generate(16);
                $_POST['scroll_percentage'] = isset($_POST['scroll_percentage']) ? (int) $_POST['scroll_percentage'] : (int) $goal->scroll_percentage;
                break;
        }

        /* Validate scroll percentage */
        if($_POST['type'] == 'scroll' && ($_POST['scroll_percentage'] < 1 || $_POST['scroll_percentage'] > 100)) {
            $this->response_error(l('goal_create_modal.scroll_percentage_error'), 401);
        }

        /* Database query */
        db()->where('goal_id', $goal_id)->where('user_id', $this->user->user_id)->update('websites_goals', [
            'website_id' => $_POST['website_id'],
            'key' => $_POST['key'],
            'type' => $_POST['type'],
            'path' => $_POST['path'],
            'scroll_percentage' => $_POST['scroll_percentage'],
            'name' => $_POST['name'],
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('website_goals?website_id=' . $goal->website_id);
        if($goal->website_id != $_POST['website_id']) {
            cache()->deleteItem('website_goals?website_id=' . $_POST['website_id']);
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $goal->goal_id,
            'website_id' => (int) $_POST['website_id'],
            'user_id' => (int) $this->user->user_id,
            'key' => $_POST['key'],
            'type' => $_POST['type'],
            'path' => $_POST['path'],
            'scroll_percentage' => $_POST['scroll_percentage'] !== null ? (int) $_POST['scroll_percentage'] : null,
            'name' => $_POST['name'],
            'last_datetime' => get_date(),
            'datetime' => $goal->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $goal_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $goal = db()->where('goal_id', $goal_id)->where('user_id', $this->user->user_id)->getOne('websites_goals');

        /* We haven't found the resource */
        if(!$goal) {
            $this->return_404();
        }

        /* Database query */
        db()->where('goal_id', $goal_id)->delete('websites_goals');

        /* Clear the cache */
        cache()->deleteItem('website_goals?website_id=' . $goal->website_id);

        http_response_code(200);
        die();

    }

}
