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

use Altum\Captcha;

defined('ALTUMCODE') || die();

class Index extends Controller {

    public function index() {

        /* Custom index redirect if set */
        if(!empty(settings()->main->index_url)) {
            header('Location: ' . settings()->main->index_url); die();
        }

        /* Opengraph image */
        if(settings()->main->opengraph) {
            \Altum\Meta::set_social_image(\Altum\Uploads::get_full_url('opengraph') . settings()->main->opengraph);
        }

        /* Canonical */
        \Altum\Meta::set_canonical_url(url());

        /* Fix on /index to / */
        \Altum\Router::$original_request = '';

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        if(is_logged_in()) {
            /* Get available projects */
            $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

            /* Get available pixels */
            $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);

            /* Get available notification handlers */
            $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);
        }

        /* Plans View */
        $view = new \Altum\View('partials/plans', (array) $this);
        $this->add_view_content('plans', $view->run());

        /* Initiate captcha */
        $captcha = new Captcha();

        /* Check if the cache exists */
        $cache_instance = cache()->getItem('index_stats');

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            $total_files = database()->query("SELECT MAX(`file_id`) AS `total` FROM `files`")->fetch_object()->total ?? 0;
            $total_transfers = database()->query("SELECT MAX(`transfer_id`) AS `total` FROM `transfers`")->fetch_object()->total ?? 0;

            $stats = [
                'total_files' => $total_files,
                'total_transfers' => $total_transfers,
            ];

            /* Save to cache */
            cache()->save($cache_instance->set($stats)->expiresAfter(3600));

        } else {

            /* Get cache */
            $stats = $cache_instance->get();
            extract($stats);

        }

        if(settings()->main->display_index_latest_blog_posts) {
            $language = \Altum\Language::$name;

            /* Blog posts query */
            $blog_posts_result_query = "
                SELECT * 
                FROM `blog_posts`
                WHERE (`language` = '{$language}' OR `language` IS NULL) AND `is_published` = 1 
                ORDER BY `blog_post_id` DESC
                LIMIT 3
            ";

            $blog_posts = \Altum\Cache::cache_function_result('blog_posts?hash=' . md5($blog_posts_result_query), 'blog_posts', function() use ($blog_posts_result_query) {
                $blog_posts_result = database()->query($blog_posts_result_query);

                /* Iterate over the blog posts */
                $blog_posts = [];

                while($row = $blog_posts_result->fetch_object()) {
                    /* Transform content if needed */
                    $row->content = json_decode($row->content) ? convert_editorjs_json_to_html($row->content) : output_blog_post_content($row->content);

                    $blog_posts[] = $row;
                }

                return $blog_posts;
            });
        }

        /* Main View */
        $data = [
            'domains' => $domains,
            'projects' => $projects ?? [],
            'pixels' => $pixels ?? [],
            'notification_handlers' => $notification_handlers ?? [],
            'captcha' => $captcha,
            'total_files' => $total_files,
            'total_transfers' => $total_transfers,
            'blog_posts' => $blog_posts ?? [],
        ];

        $view = new \Altum\View('index/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
