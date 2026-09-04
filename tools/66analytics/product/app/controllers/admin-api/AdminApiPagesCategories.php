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

class AdminApiPagesCategories extends Controller {
    use Apiable;

    public function index() {

        $this->verify_request(true);

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
        $filters = (new \Altum\Filters(['pages_category_id'], ['title', 'url'], ['pages_category_id', 'datetime', 'last_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by('pages_category_id', isset($this->user->preferences->default_order_type) ? $this->user->preferences->default_order_type : settings()->main->default_order_type);
        $filters->set_default_results_per_page(isset($this->user->preferences->default_results_per_page) ? $this->user->preferences->default_results_per_page : settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows_result = database()->query("SELECT COUNT(*) AS `total` FROM `pages_categories` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object();
        $total_rows = $total_rows_result ? $total_rows_result->total : 0;
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $page, url('admin-api/pages-categories?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `pages_categories`
            WHERE
                1 = 1
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->pages_category_id,
                'url' => $row->url,
                'title' => $row->title,
                'description' => $row->description,
                'icon' => $row->icon,
                'order' => (int) $row->order,
                'language' => $row->language,
                'datetime' => $row->datetime,
                'last_datetime' => $row->last_datetime,
            ];

            $data[] = $row;
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

        $pages_category_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $pages_category = db()->where('pages_category_id', $pages_category_id)->getOne('pages_categories');

        /* We haven't found the resource */
        if(!$pages_category) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $pages_category->pages_category_id,
            'url' => $pages_category->url,
            'title' => $pages_category->title,
            'description' => $pages_category->description,
            'icon' => $pages_category->icon,
            'order' => (int) $pages_category->order,
            'language' => $pages_category->language,
            'datetime' => $pages_category->datetime,
            'last_datetime' => $pages_category->last_datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for any errors */
        $required_fields = ['title', 'url'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Filter some of the variables */
        $_POST['url'] = input_clean(get_slug($_POST['url']), 256);
        $_POST['title'] = input_clean($_POST['title'], 256);
        $_POST['description'] = input_clean(isset($_POST['description']) ? $_POST['description'] : '', 256);
        $_POST['icon'] = input_clean(isset($_POST['icon']) ? $_POST['icon'] : '');
        $_POST['language'] = !empty($_POST['language']) ? input_clean($_POST['language']) : null;
        $_POST['order'] = isset($_POST['order']) ? (int) $_POST['order'] : 0;

        if(db()->where('url', $_POST['url'])->where('language', $_POST['language'])->has('pages_categories')) {
            $this->response_error(l('admin_resources.error_message.url_exists'), 401);
        }

        /* Database query */
        $datetime = get_date();
        $pages_category_id = db()->insert('pages_categories', [
            'url' => $_POST['url'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'icon' => $_POST['icon'],
            'language' => $_POST['language'],
            'order' => $_POST['order'],
            'datetime' => $datetime,
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('pages_categories');

        /* Prepare the data */
        $data = [
            'id' => (int) $pages_category_id,
            'url' => $_POST['url'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'icon' => $_POST['icon'],
            'order' => $_POST['order'],
            'language' => $_POST['language'],
            'datetime' => $datetime,
            'last_datetime' => null,
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $pages_category_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $pages_category = db()->where('pages_category_id', $pages_category_id)->getOne('pages_categories');

        /* We haven't found the resource */
        if(!$pages_category) {
            $this->return_404();
        }

        /* Check for any errors */
        foreach(['title', 'url'] as $field) {
            if(isset($_POST[$field]) && trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Filter some of the variables */
        $_POST['url'] = isset($_POST['url']) ? input_clean(get_slug($_POST['url']), 256) : $pages_category->url;
        $_POST['title'] = isset($_POST['title']) ? input_clean($_POST['title'], 256) : $pages_category->title;
        $_POST['description'] = isset($_POST['description']) ? input_clean($_POST['description'], 256) : $pages_category->description;
        $_POST['icon'] = isset($_POST['icon']) ? input_clean($_POST['icon']) : $pages_category->icon;
        $_POST['language'] = isset($_POST['language']) ? (!empty($_POST['language']) ? input_clean($_POST['language']) : null) : $pages_category->language;
        $_POST['order'] = isset($_POST['order']) ? (int) $_POST['order'] : $pages_category->order;

        if(db()->where('pages_category_id', $pages_category->pages_category_id, '<>')->where('url', $_POST['url'])->where('language', $_POST['language'])->has('pages_categories')) {
            $this->response_error(l('admin_resources.error_message.url_exists'), 401);
        }

        /* Database query */
        $last_datetime = get_date();
        db()->where('pages_category_id', $pages_category_id)->update('pages_categories', [
            'url' => $_POST['url'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'icon' => $_POST['icon'],
            'language' => $_POST['language'],
            'order' => $_POST['order'],
            'last_datetime' => $last_datetime,
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('pages_categories');

        /* Prepare the data */
        $data = [
            'id' => (int) $pages_category->pages_category_id,
            'url' => $_POST['url'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'icon' => $_POST['icon'],
            'order' => (int) $_POST['order'],
            'language' => $_POST['language'],
            'datetime' => $pages_category->datetime,
            'last_datetime' => $last_datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $pages_category_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $pages_category = db()->where('pages_category_id', $pages_category_id)->getOne('pages_categories', ['pages_category_id']);

        /* We haven't found the resource */
        if(!$pages_category) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('pages_category_id', $pages_category_id)->delete('pages_categories');

        /* Clear the cache */
        cache()->deleteItemsByTag('pages_categories');

        http_response_code(200);
        die();

    }

}
