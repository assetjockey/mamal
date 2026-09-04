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

defined('ALTUMCODE') || die();

class Sitemap extends Controller {

    public function index() {

        /* Set the header as xml so the browser can read it properly */
        header('Content-Type: text/xml');

        $view = new \Altum\View('sitemap/sitemap_index', (array) $this);

        echo $view->run();

    }

    public function main() {
        /* Set the header as xml so the browser can read it properly */
        header('Content-Type: text/xml');

        $sitemap_urls = [
            '',
            'login',
            'lost-password',
        ];

        if(settings()->users->email_confirmation) {
            $sitemap_urls[] = 'resend-activation';
        }

        if(settings()->users->register_is_enabled) {
            $sitemap_urls[] = 'register';
        }

        if(\Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled) {
            $sitemap_urls[] = 'affiliate';
        }

        if(settings()->main->api_is_enabled) {
            $sitemap_urls[] = 'api-documentation';
        }

        if(settings()->email_notifications->contact && !empty(settings()->email_notifications->emails)) {
            $sitemap_urls[] = 'contact';
        }

        if(settings()->payment->is_enabled) {
            $sitemap_urls[] = 'plan';
        }

        if(settings()->content->pages_is_enabled) {
            $sitemap_urls[] = 'pages';
        }

        if(settings()->content->blog_is_enabled) {
            $sitemap_urls[] = 'blog';
        }

        if(settings()->tools->is_enabled && settings()->tools->access == 'everyone') {
            foreach ((require APP_PATH . 'includes/tools/tools.php') as $key => $value) {
                if(settings()->tools->available_tools->{$key}) {
                    $sitemap_urls[] = 'tools/' . str_replace('_', '-', $key);
                }
            }
        }

        /* Multilingual */
        $new_sitemap_urls = [];

        foreach(\Altum\Language::$active_languages as $language_name => $language_code) {
            foreach($sitemap_urls as $url) {
                $new_sitemap_urls[] = settings()->main->default_language == $language_name ? SITE_URL . $url : SITE_URL . $language_code . '/' . $url;
            }
        }

        if(settings()->content->pages_is_enabled) {
            $pages = db()->where('type', 'internal')->where('is_published', 1)->get('pages', null, ['url', 'language']);
            $pages_categories = db()->get('pages_categories', null, ['url', 'language']);

            foreach ($pages as $page) {
                $new_sitemap_urls[] = SITE_URL . ($page->language ? \Altum\Language::$active_languages[$page->language] . '/' : '') . 'page/' . $page->url;
            }

            foreach ($pages_categories as $pages_category) {
                $new_sitemap_urls[] = SITE_URL . ($pages_category->language ? \Altum\Language::$active_languages[$pages_category->language] . '/' : '') . 'pages/' . $pages_category->url;
            }
        }

        if(settings()->content->blog_is_enabled) {
            $blog_posts = db()->where('is_published', 1)->get('blog_posts', null, ['url', 'language']);
            $blog_posts_categories = db()->get('blog_posts_categories', null, ['url', 'language']);

            foreach ($blog_posts as $blog_post) {
                $new_sitemap_urls[] = SITE_URL . ($blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . '/' : '') . 'blog/' . $blog_post->url;
            }

            foreach ($blog_posts_categories as $blog_posts_category) {
                $new_sitemap_urls[] = SITE_URL . ($blog_posts_category->language ? \Altum\Language::$active_languages[$blog_posts_category->language] . '/' : '') . 'blog/category/' . $blog_posts_category->url;
            }
        }


        /* Main View */
        $data = [
            'sitemap_urls' => $new_sitemap_urls,
        ];

        $view = new \Altum\View('sitemap/sitemap_main', (array) $this);

        echo $view->run($data);

    }

    public function status_pages() {
        /* Set the header as xml so the browser can read it properly */
        header('Content-Type: text/xml');

        /* How many per sitemap page */
        $pagination = 10000;

        $page = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Different answers for different parts */
        switch($page) {

            /* Sitemap index */
            case null:

                /* Get the total amount of links */
                $total_links = database()->query("
                    SELECT 
                        COUNT(`status_pages`.`status_page_id`) AS `total` 
                    FROM 
                        `status_pages`
                    LEFT JOIN
                        `users` ON `status_pages`.`user_id` = `users`.`user_id`
                    WHERE
                        `users`.`status` = 1
                        AND `status_pages`.`is_enabled` = 1
                        AND `status_pages`.`is_se_visible` = 1
                  ")->fetch_object()->total ?? 0;

                /* Calculate the needed sitemaps */
                $total_sitemaps = ceil((int) $total_links / $pagination);

                /* Prepare the urls */
                $sitemap_urls = [];

                foreach(range(1, $total_sitemaps) as $key) {
                    $sitemap_urls[] = SITE_URL . 'sitemap/status_pages/' . $key;
                }

                /* Main View */
                $data = [
                    'sitemap_urls' => $sitemap_urls,
                ];

                $view = new \Altum\View('sitemap/sitemap_status_pages', (array) $this);

                echo $view->run($data);

                break;

            /* Output only indexed external users */
            default:

                $limit_start = ($page - 1) * $pagination;

                /* Get the external users list */
                $result = database()->query("
                    SELECT
                        `status_pages`.`url`,
                        `status_pages`.`datetime`
                    FROM 
                        `status_pages`
                    LEFT JOIN
                        `users` ON `status_pages`.`user_id` = `users`.`user_id`
                    WHERE
                        `users`.`status` = 1
                        AND `status_pages`.`is_enabled` = 1
                        AND `status_pages`.`is_se_visible` = 1
                        AND `status_pages`.`domain_id` IS NULL
                    LIMIT 
                        {$limit_start}, {$pagination}
                ");

                /* Main View */
                $data = [
                    'result' => $result,
                ];

                $view = new \Altum\View('sitemap/sitemap_status_pages_list', (array) $this);

                echo $view->run($data);

                break;

        }

    }
}

