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

use Altum\Language;
use Altum\Meta;
use Altum\Title;

defined('ALTUMCODE') || die();

class Page extends Controller {

    public function index() {

        if(!settings()->content->pages_is_enabled) {
            throw_404();
        }

        $url = isset($this->params[0]) ? query_clean($this->params[0]) : null;
        $language = Language::$name;

        /* If the custom page url is set then try to get data from the database */
        $page_query = "
                SELECT *
            FROM `pages`
            WHERE
                ((`url` = '{$url}' AND `language` = '{$language}') OR (`url` = '{$url}' AND `language` IS NULL))
                AND `is_published` = 1
            ORDER BY `language` DESC
            ";
        $page = $url ? \Altum\Cache::cache_function_result('page?hash=' . md5($page_query), 'pages', function() use ($page_query) {
            return database()->query($page_query)->fetch_object() ?? null;
        }) : null;

        /* Redirect if the page does not exist */
        if(!$page) {
            throw_404();
        }

        $page->plans_ids = json_decode($page->plans_ids ?? '');

        if(!empty($page->plans_ids)) {
            if(!is_logged_in()) {
                throw_404();
            };

            if(!in_array(user()->plan_id, $page->plans_ids)) {
                throw_404();
            }
        }

        /* Get the page category */
        $pages_category = $page->pages_category_id ? \Altum\Cache::cache_function_result('pages_category?hash=' . md5($page->pages_category_id), 'pages_categories', function() use ($page) {
            return db()->where('pages_category_id', $page->pages_category_id)->getOne('pages_categories');
        }) : null;

        /* Add a new view to the page */
        $cookie_name = 'page_view_' . $page->page_id;
        if(!isset($_COOKIE[$cookie_name])) {
            db()->where('page_id', $page->page_id)->update('pages', ['total_views' => db()->inc()]);
            setcookie($cookie_name, (int) true, time()+60*60*24*1);
        }

        /* Transform content if needed */
        $page->content = json_decode($page->content) ? convert_editorjs_json_to_html($page->content) : output_blog_post_content($page->content);
        $page->table_of_contents = [];

        /* Generate table of contents */
        if(settings()->content->pages_table_of_contents_is_enabled) {
            $table_of_contents = get_blog_post_table_of_contents($page->content);
            $page->content = $table_of_contents->content;
            $page->table_of_contents = $table_of_contents->items;
        }

        /* Prepare the view */
        $data = [
            'page'  => $page,
            'pages_category' => $pages_category,
        ];

        $view = new \Altum\View('page/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

        /* Set a custom title */
        Title::set($page->title);

        /* Meta */

        Meta::set_description($page->description);
        Meta::set_keywords($page->keywords);

        /* Disable automated link language alternate */
        Meta::set_link_alternate(false);
    }

}

