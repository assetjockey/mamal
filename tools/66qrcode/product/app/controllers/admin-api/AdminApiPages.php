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

class AdminApiPages extends Controller {
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
        $filters = (new \Altum\Filters(['page_id', 'is_published', 'pages_category_id'], ['title', 'url', 'description', 'keywords'], ['page_id', 'datetime', 'last_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by('page_id', isset($this->user->preferences->default_order_type) ? $this->user->preferences->default_order_type : settings()->main->default_order_type);
        $filters->set_default_results_per_page(isset($this->user->preferences->default_results_per_page) ? $this->user->preferences->default_results_per_page : settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows_result = database()->query("SELECT COUNT(*) AS `total` FROM `pages` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object();
        $total_rows = $total_rows_result ? $total_rows_result->total : 0;
        $page_number = isset($_GET['page']) ? $_GET['page'] : 1;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $page_number, url('admin-api/pages?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `pages`
            WHERE
                1 = 1
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->page_id,
                'pages_category_id' => $row->pages_category_id ? (int) $row->pages_category_id : null,
                'footer_category_id' => $row->footer_category_id,
                'plans_ids' => $row->plans_ids ? json_decode($row->plans_ids) : null,
                'url' => $row->url,
                'title' => $row->title,
                'description' => $row->description,
                'icon' => $row->icon,
                'keywords' => $row->keywords,
                'editor' => $row->editor,
                'content' => $row->content,
                'type' => $row->type,
                'position' => $row->position,
                'language' => $row->language,
                'open_in_new_tab' => (bool) $row->open_in_new_tab,
                'order' => (int) $row->order,
                'total_views' => (int) $row->total_views,
                'is_published' => (bool) $row->is_published,
                'datetime' => $row->datetime,
                'last_datetime' => $row->last_datetime,
            ];

            $data[] = $row;
        }

        /* Prepare the data */
        $meta = [
            'page' => $page_number,
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
            'self' => $paginator->getPageUrl($page_number)
        ]];

        Response::jsonapi_success($data, $meta, 200, $others);
    }

    private function get() {

        $page_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $page = db()->where('page_id', $page_id)->getOne('pages');

        /* We haven't found the resource */
        if(!$page) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $page->page_id,
            'pages_category_id' => $page->pages_category_id ? (int) $page->pages_category_id : null,
            'footer_category_id' => $page->footer_category_id,
            'plans_ids' => $page->plans_ids ? json_decode($page->plans_ids) : null,
            'url' => $page->url,
            'title' => $page->title,
            'description' => $page->description,
            'icon' => $page->icon,
            'keywords' => $page->keywords,
            'editor' => $page->editor,
            'content' => $page->content,
            'type' => $page->type,
            'position' => $page->position,
            'language' => $page->language,
            'open_in_new_tab' => (bool) $page->open_in_new_tab,
            'order' => (int) $page->order,
            'total_views' => (int) $page->total_views,
            'is_published' => (bool) $page->is_published,
            'datetime' => $page->datetime,
            'last_datetime' => $page->last_datetime,
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

        /* Get all plans */
        $plans = (new \Altum\Models\Plan())->get_plans();

        /* Filter some of the variables */
        $_POST['title'] = input_clean($_POST['title'], 256);
        $_POST['description'] = input_clean(isset($_POST['description']) ? $_POST['description'] : '', 256);
        $_POST['icon'] = input_clean(isset($_POST['icon']) ? $_POST['icon'] : '');
        $_POST['keywords'] = input_clean(isset($_POST['keywords']) ? $_POST['keywords'] : '', 256);
        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['internal', 'external']) ? input_clean($_POST['type']) : 'internal';
        $_POST['editor'] = isset($_POST['editor']) ? input_clean($_POST['editor']) : 'raw';
        $_POST['position'] = isset($_POST['position']) && in_array($_POST['position'], ['hidden', 'top', 'bottom']) ? $_POST['position'] : 'top';
        $_POST['pages_category_id'] = empty($_POST['pages_category_id']) ? null : (int) $_POST['pages_category_id'];
        $_POST['footer_category_id'] = !empty($_POST['footer_category_id']) ? input_clean($_POST['footer_category_id'], 64) : null;
        $_POST['language'] = !empty($_POST['language']) ? input_clean($_POST['language']) : null;
        $_POST['order'] = isset($_POST['order']) ? (int) $_POST['order'] : 0;
        $_POST['open_in_new_tab'] = isset($_POST['open_in_new_tab']) ? (int) (bool) $_POST['open_in_new_tab'] : 1;
        $_POST['is_published'] = isset($_POST['is_published']) ? (int) (bool) $_POST['is_published'] : 1;
        $_POST['content'] = isset($_POST['content']) ? $_POST['content'] : '';

        if($_POST['editor'] != 'raw') {
            $this->response_error(sprintf(l('api_documentation.allowed_values'), 'raw'), 401);
        }

        if($_POST['pages_category_id'] && !db()->where('pages_category_id', $_POST['pages_category_id'])->has('pages_categories')) {
            $this->return_404();
        }

        if(isset($_POST['plans_ids'])) {
            $_POST['plans_ids'] = is_array($_POST['plans_ids']) ? $_POST['plans_ids'] : explode(',', $_POST['plans_ids']);
            $_POST['plans_ids'] = array_map(
                'intval',
                array_filter($_POST['plans_ids'], function($plan_id) use($plans) {
                    return array_key_exists($plan_id, $plans);
                })
            );
            $_POST['plans_ids'] = empty($_POST['plans_ids']) ? null : json_encode($_POST['plans_ids']);
        } else {
            $_POST['plans_ids'] = null;
        }

        switch($_POST['type']) {
            case 'internal':
                $_POST['url'] = input_clean(get_slug($_POST['url']), 256);
                break;

            case 'external':
                $_POST['url'] = input_clean($_POST['url'], 256);
                break;
        }

        if($_POST['type'] == 'internal' && db()->where('url', $_POST['url'])->where('language', $_POST['language'])->has('pages')) {
            $this->response_error(l('admin_resources.error_message.url_exists'), 401);
        }

        /* Database query */
        $datetime = get_date();
        $page_id = db()->insert('pages', [
            'pages_category_id' => $_POST['pages_category_id'],
            'footer_category_id' => $_POST['footer_category_id'],
            'plans_ids' => $_POST['plans_ids'],
            'url' => $_POST['url'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'icon' => $_POST['icon'],
            'keywords' => $_POST['keywords'],
            'editor' => $_POST['editor'],
            'content' => $_POST['content'],
            'type' => $_POST['type'],
            'position' => $_POST['position'],
            'language' => $_POST['language'],
            'open_in_new_tab' => $_POST['open_in_new_tab'],
            'order' => $_POST['order'],
            'is_published' => $_POST['is_published'],
            'datetime' => $datetime,
            'last_datetime' => $datetime,
        ]);

        /* Clear the cache */
        cache()->deleteItems(['pages_hidden', 'pages_top', 'pages_bottom']);
        cache()->deleteItemsByTag('pages');

        /* Prepare the data */
        $data = [
            'id' => (int) $page_id,
            'pages_category_id' => $_POST['pages_category_id'],
            'footer_category_id' => $_POST['footer_category_id'],
            'plans_ids' => $_POST['plans_ids'] ? json_decode($_POST['plans_ids']) : null,
            'url' => $_POST['url'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'icon' => $_POST['icon'],
            'keywords' => $_POST['keywords'],
            'editor' => $_POST['editor'],
            'content' => $_POST['content'],
            'type' => $_POST['type'],
            'position' => $_POST['position'],
            'language' => $_POST['language'],
            'open_in_new_tab' => (bool) $_POST['open_in_new_tab'],
            'order' => (int) $_POST['order'],
            'total_views' => 0,
            'is_published' => (bool) $_POST['is_published'],
            'datetime' => $datetime,
            'last_datetime' => $datetime,
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $page_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $page = db()->where('page_id', $page_id)->getOne('pages');

        /* We haven't found the resource */
        if(!$page) {
            $this->return_404();
        }

        /* Check for any errors */
        foreach(['title', 'url'] as $field) {
            if(isset($_POST[$field]) && trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Get all plans */
        $plans = (new \Altum\Models\Plan())->get_plans();

        $editor_is_submitted = isset($_POST['editor']);
        $content_is_submitted = isset($_POST['content']);
        $is_feature_page = $page->type == 'feature';

        /* Filter some of the variables */
        $_POST['title'] = isset($_POST['title']) ? input_clean($_POST['title'], 256) : $page->title;
        $_POST['description'] = isset($_POST['description']) ? input_clean($_POST['description'], 256) : $page->description;
        $_POST['icon'] = isset($_POST['icon']) ? input_clean($_POST['icon']) : $page->icon;
        $_POST['keywords'] = isset($_POST['keywords']) ? input_clean($_POST['keywords'], 256) : $page->keywords;
        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['internal', 'external']) ? input_clean($_POST['type']) : $page->type;
        $_POST['editor'] = $editor_is_submitted ? input_clean($_POST['editor']) : $page->editor;
        $_POST['position'] = isset($_POST['position']) && in_array($_POST['position'], ['hidden', 'top', 'bottom']) ? $_POST['position'] : $page->position;
        $_POST['pages_category_id'] = isset($_POST['pages_category_id']) ? (empty($_POST['pages_category_id']) ? null : (int) $_POST['pages_category_id']) : $page->pages_category_id;
        $_POST['footer_category_id'] = isset($_POST['footer_category_id']) ? (!empty($_POST['footer_category_id']) ? input_clean($_POST['footer_category_id'], 64) : null) : $page->footer_category_id;
        $_POST['language'] = isset($_POST['language']) ? (!empty($_POST['language']) ? input_clean($_POST['language']) : null) : $page->language;
        $_POST['order'] = isset($_POST['order']) ? (int) $_POST['order'] : $page->order;
        $_POST['open_in_new_tab'] = isset($_POST['open_in_new_tab']) ? (int) (bool) $_POST['open_in_new_tab'] : $page->open_in_new_tab;
        $_POST['is_published'] = isset($_POST['is_published']) ? (int) (bool) $_POST['is_published'] : $page->is_published;
        $_POST['content'] = $content_is_submitted ? $_POST['content'] : $page->content;

        if($is_feature_page) {
            $_POST['type'] = 'feature';
            $_POST['pages_category_id'] = $page->pages_category_id;
            $_POST['plans_ids'] = $page->plans_ids;
            $_POST['url'] = $page->url;
            $_POST['title'] = $page->title;
            $_POST['description'] = $page->description;
            $_POST['keywords'] = $page->keywords;
            $_POST['editor'] = $page->editor;
            $_POST['content'] = $page->content;
            $_POST['language'] = $page->language;
            $editor_is_submitted = false;
            $content_is_submitted = false;
        }

        if($editor_is_submitted && $_POST['editor'] != 'raw') {
            $this->response_error(sprintf(l('api_documentation.allowed_values'), 'raw'), 401);
        }

        if($content_is_submitted) {
            $_POST['editor'] = 'raw';
        }

        if($_POST['pages_category_id'] && !db()->where('pages_category_id', $_POST['pages_category_id'])->has('pages_categories')) {
            $this->return_404();
        }

        if($is_feature_page) {
            $_POST['plans_ids'] = $page->plans_ids;
        } else if(isset($_POST['plans_ids'])) {
            $_POST['plans_ids'] = is_array($_POST['plans_ids']) ? $_POST['plans_ids'] : explode(',', $_POST['plans_ids']);
            $_POST['plans_ids'] = array_map(
                'intval',
                array_filter($_POST['plans_ids'], function($plan_id) use($plans) {
                    return array_key_exists($plan_id, $plans);
                })
            );
            $_POST['plans_ids'] = empty($_POST['plans_ids']) ? null : json_encode($_POST['plans_ids']);
        } else {
            $_POST['plans_ids'] = $page->plans_ids;
        }

        if($is_feature_page) {
            $_POST['url'] = $page->url;
        } else {
            switch($_POST['type']) {
                case 'internal':
                    $_POST['url'] = isset($_POST['url']) ? input_clean(get_slug($_POST['url']), 256) : $page->url;
                    break;

                case 'external':
                    $_POST['url'] = isset($_POST['url']) ? input_clean($_POST['url'], 256) : $page->url;
                    break;
            }
        }

        if($_POST['type'] == 'internal' && db()->where('page_id', $page->page_id, '<>')->where('url', $_POST['url'])->where('language', $_POST['language'])->has('pages')) {
            $this->response_error(l('admin_resources.error_message.url_exists'), 401);
        }

        /* Database query */
        $last_datetime = get_date();
        if($_POST['type'] == 'feature') {
            db()->where('page_id', $page->page_id)->update('pages', [
                'icon' => $_POST['icon'],
                'position' => $_POST['position'],
                'footer_category_id' => $_POST['footer_category_id'],
                'open_in_new_tab' => $_POST['open_in_new_tab'],
                'is_published' => $_POST['is_published'],
                'order' => $_POST['order'],
                'last_datetime' => $last_datetime,
            ]);
        } else {
            db()->where('page_id', $page->page_id)->update('pages', [
                'pages_category_id' => $_POST['pages_category_id'],
                'footer_category_id' => $_POST['footer_category_id'],
                'plans_ids' => $_POST['plans_ids'],
                'url' => $_POST['url'],
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'icon' => $_POST['icon'],
                'keywords' => $_POST['keywords'],
                'editor' => $_POST['editor'],
                'content' => $_POST['content'],
                'type' => $_POST['type'],
                'position' => $_POST['position'],
                'language' => $_POST['language'],
                'open_in_new_tab' => $_POST['open_in_new_tab'],
                'is_published' => $_POST['is_published'],
                'order' => $_POST['order'],
                'last_datetime' => $last_datetime,
            ]);
        }

        /* Clear the cache */
        cache()->deleteItems(['pages_hidden', 'pages_top', 'pages_bottom']);
        cache()->deleteItemsByTag('pages');

        /* Prepare the data */
        $data = [
            'id' => (int) $page->page_id,
            'pages_category_id' => $_POST['pages_category_id'] ? (int) $_POST['pages_category_id'] : null,
            'footer_category_id' => $_POST['footer_category_id'],
            'plans_ids' => $_POST['plans_ids'] ? json_decode($_POST['plans_ids']) : null,
            'url' => $_POST['url'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'icon' => $_POST['icon'],
            'keywords' => $_POST['keywords'],
            'editor' => $_POST['editor'],
            'content' => $_POST['content'],
            'type' => $_POST['type'],
            'position' => $_POST['position'],
            'language' => $_POST['language'],
            'open_in_new_tab' => (bool) $_POST['open_in_new_tab'],
            'order' => (int) $_POST['order'],
            'total_views' => (int) $page->total_views,
            'is_published' => (bool) $_POST['is_published'],
            'datetime' => $page->datetime,
            'last_datetime' => $last_datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $page_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $page = db()->where('page_id', $page_id)->getOne('pages', ['page_id']);

        /* We haven't found the resource */
        if(!$page) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('page_id', $page_id)->delete('pages');

        /* Clear the cache */
        cache()->deleteItems(['pages_hidden', 'pages_top', 'pages_bottom']);
        cache()->deleteItemsByTag('pages');

        http_response_code(200);
        die();

    }

}
