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

defined('ALTUMCODE') || die();

class AdminPages extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['page_id', 'is_published', 'pages_category_id'], ['title', 'url', 'description', 'keywords'], ['page_id', 'datetime', 'last_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by('page_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `pages` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/pages?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $pages = [];
        $pages_result = database()->query("
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
        while($row = $pages_result->fetch_object()) {
            $pages[] = $row;
        }

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Get all pages categories */
        $pages_categories = [];
        $pages_categories_result = database()->query("SELECT `pages_category_id`, `title` FROM `pages_categories`");
        while($row = $pages_categories_result->fetch_object()) {
            $pages_categories[$row->pages_category_id] = $row;
        }

        /* Main View */
        $data = [
            'pages' => $pages,
            'pages_categories' => $pages_categories,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('admin/pages/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/pages');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/pages');
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

                    foreach($_POST['selected'] as $id) {
                        db()->where('page_id', $id)->delete('pages');
                    }

                    break;
            }

            /* Clear the cache */
            cache()->deleteItems(['pages_top', 'pages_bottom', 'pages_hidden']);
            cache()->deleteItemsByTag('pages');

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/pages');
    }

    public function duplicate() {

        if (empty($_POST)) {
            throw_404();
        }

        $page_id = (int) $_POST['page_id'];

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$page = db()->where('page_id', $page_id)->getOne('pages')) {
            redirect('admin/pages');
        }

        if($page->type == 'feature') {
            redirect('admin/pages');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Generate a new URL */
            if($page->type == 'internal') {
                $url_base = string_truncate(get_slug($page->url . '-' . l('global.duplicated')), 240, '');
                $url = $url_base;
                $url_counter = 1;

                while(db()->where('url', $url)->where('language', $page->language)->has('pages')) {
                    $url_counter++;
                    $url = string_truncate($url_base, 256 - mb_strlen('-' . $url_counter), '') . '-' . $url_counter;
                }
            } else {
                $url = $page->url;
            }

            /* Insert to database */
            $page_id = db()->insert('pages', [
                'pages_category_id' => $page->pages_category_id,
                'footer_category_id' => $page->footer_category_id,
                'plans_ids' => $page->plans_ids,
                'url' => $url,
                'title' => string_truncate($page->title . ' - ' . l('global.duplicated'), 256, null),
                'description' => $page->description,
                'icon' => $page->icon,
                'keywords' => $page->keywords,
                'editor' => $page->editor,
                'content' => $page->content,
                'type' => $page->type,
                'position' => $page->position,
                'language' => $page->language,
                'open_in_new_tab' => $page->open_in_new_tab,
                'order' => $page->order + 1,
                'is_published' => 0,
                'datetime' => get_date(),
                'last_datetime' => get_date(),
            ]);

            /* Clear the cache */
            cache()->deleteItems(['pages_hidden', 'pages_top', 'pages_bottom']);
            cache()->deleteItemsByTag('pages');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($page->title) . '</strong>'));

            /* Redirect */
            redirect('admin/page-update/' . $page_id);

        }

        redirect('admin/pages');
    }

    public function delete() {

        $page_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$page = db()->where('page_id', $page_id)->getOne('pages', ['page_id', 'title'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the page */
            db()->where('page_id', $page_id)->delete('pages');

            /* Clear the cache */
            cache()->deleteItems(['pages_top', 'pages_bottom', 'pages_hidden']);
            cache()->deleteItemsByTag('pages');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $page->title . '</strong>'));

        }

        redirect('admin/pages');
    }

}
