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

class ApiAnnotations extends Controller {
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
        $filters = (new \Altum\Filters(['website_id'], ['name'], ['annotation_id', 'website_id', 'last_datetime', 'datetime', 'name', 'chart_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'chart_datetime']));
        $filters->set_default_order_by('annotation_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `annotations` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/annotations?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `annotations`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->annotation_id,
                'website_id' => (int) $row->website_id,
                'user_id' => (int) $row->user_id,
                'name' => $row->name,
                'chart_datetime' => $row->chart_datetime,
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

        $annotation_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $annotation = db()->where('annotation_id', $annotation_id)->where('user_id', $this->user->user_id)->getOne('annotations');

        /* We haven't found the resource */
        if(!$annotation) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $annotation->annotation_id,
            'website_id' => (int) $annotation->website_id,
            'user_id' => (int) $annotation->user_id,
            'name' => $annotation->name,
            'chart_datetime' => $annotation->chart_datetime,
            'last_datetime' => $annotation->last_datetime,
            'datetime' => $annotation->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for any errors */
        $required_fields = ['website_id', 'name', 'chart_datetime'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['website_id'] = (int) $_POST['website_id'];
        $_POST['name'] = input_clean($_POST['name'], 64);
        $_POST['chart_datetime'] = isset($_POST['chart_datetime']) && \Altum\Date::validate($_POST['chart_datetime'], 'Y-m-d H:i:s') ? $_POST['chart_datetime'] : get_date();

        if(!$website_id = db()->where('website_id', $_POST['website_id'])->where('user_id', $this->user->user_id)->getValue('websites', 'website_id')) {
            $this->return_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->getValue('annotations', 'count(*)');

        if($this->user->plan_settings->annotations_limit != -1 && $total_rows >= $this->user->plan_settings->annotations_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Database query */
        $annotation_id = db()->insert('annotations', [
			'user_id' => $this->user->user_id,
			'website_id' => $_POST['website_id'],
            'name' => $_POST['name'],
            'chart_datetime' => $_POST['chart_datetime'],
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('annotations?website_id=' . $_POST['website_id']);

        /* Prepare the data */
        $data = [
            'id' => (int) $annotation_id,
            'website_id' => (int) $_POST['website_id'],
            'user_id' => (int) $this->user->user_id,
            'name' => $_POST['name'],
            'chart_datetime' => $_POST['chart_datetime'],
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $annotation_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $annotation = db()->where('annotation_id', $annotation_id)->where('user_id', $this->user->user_id)->getOne('annotations');

        /* We haven't found the resource */
        if(!$annotation) {
            $this->return_404();
        }

        /* Check website ownership */
        if(isset($_POST['website_id'])) {
            $_POST['website_id'] = (int) $_POST['website_id'];

            if(!db()->where('website_id', $_POST['website_id'])->where('user_id', $this->user->user_id)->getValue('websites', 'website_id')) {
                $this->return_404();
            }
        } else {
            $_POST['website_id'] = $annotation->website_id;
        }

        /* Check for the plan limit */
        $total_rows = db()->where('website_id', $_POST['website_id'])->where('user_id', $this->user->user_id)->getValue('annotations', 'count(*)');

        if($this->user->plan_settings->annotations_limit != -1 && $_POST['website_id'] != $annotation->website_id && $total_rows >= $this->user->plan_settings->annotations_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        if($this->user->plan_settings->annotations_limit != -1 && $_POST['website_id'] == $annotation->website_id && $total_rows > $this->user->plan_settings->annotations_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->annotations_limit, mb_strtolower(l('annotations.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $_POST['name'] = isset($_POST['name']) ? input_clean($_POST['name'], 64) : $annotation->name;
        $_POST['chart_datetime'] = isset($_POST['chart_datetime']) && \Altum\Date::validate($_POST['chart_datetime'], 'Y-m-d H:i:s') ? $_POST['chart_datetime'] : $annotation->chart_datetime;

        /* Database query */
        db()->where('annotation_id', $annotation_id)->where('user_id', $this->user->user_id)->update('annotations', [
			'website_id' => $_POST['website_id'],
			'name' => $_POST['name'],
            'chart_datetime' => $_POST['chart_datetime'],
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('annotations?website_id=' . $annotation->website_id);
        if($_POST['website_id'] != $annotation->website_id) {
            cache()->deleteItemsByTag('annotations?website_id=' . $_POST['website_id']);
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $annotation->annotation_id,
            'website_id' => (int) $_POST['website_id'],
            'user_id' => (int) $this->user->user_id,
            'name' => $_POST['name'],
            'chart_datetime' => $_POST['chart_datetime'],
            'last_datetime' => get_date(),
            'datetime' => $annotation->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $annotation_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $annotation = db()->where('annotation_id', $annotation_id)->where('user_id', $this->user->user_id)->getOne('annotations');

        /* We haven't found the resource */
        if(!$annotation) {
            $this->return_404();
        }

        /* Database query */
        db()->where('annotation_id', $annotation_id)->where('user_id', $this->user->user_id)->delete('annotations');

        /* Clear the cache */
        cache()->deleteItemsByTag('annotations?website_id=' . $annotation->website_id);

        http_response_code(200);
        die();

    }

}
