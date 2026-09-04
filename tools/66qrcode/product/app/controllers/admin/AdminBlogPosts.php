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
use Altum\Models\BlogPosts;

defined('ALTUMCODE') || die();

class AdminBlogPosts extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['blog_post_id', 'blog_posts_category_id', 'is_published'], ['title', 'description', 'keywords', 'url'], ['blog_post_id', 'datetime', 'last_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by('blog_post_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `blog_posts` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/blog-posts?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $blog_posts = [];
        $blog_posts_result = database()->query("
            SELECT
                *
            FROM
                `blog_posts`
            WHERE
                1 = 1
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $blog_posts_result->fetch_object()) {
            $blog_posts[] = $row;
        }

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Get all blog posts categories */
        $blog_posts_categories = [];
        $blog_posts_result = database()->query("SELECT `blog_posts_category_id`, `title` FROM `blog_posts_categories`");
        while($row = $blog_posts_result->fetch_object()) {
            $blog_posts_categories[$row->blog_posts_category_id] = $row;
        }

        /* Main View */
        $data = [
            'blog_posts' => $blog_posts,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'filters' => $filters,
            'blog_posts_categories' => $blog_posts_categories,
        ];

        $view = new \Altum\View('admin/blog-posts/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/blog-posts');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/blog-posts');
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
                        (new BlogPosts())->delete($id);
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/blog-posts');
    }

    public function duplicate() {

        if (empty($_POST)) {
            throw_404();
        }

        $blog_post_id = (int) $_POST['blog_post_id'];

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$blog_post = db()->where('blog_post_id', $blog_post_id)->getOne('blog_posts')) {
            redirect('admin/blog-posts');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Generate a new URL */
            $url_base = string_truncate(get_slug($blog_post->url . '-' . l('global.duplicated')), 240, '');
            $url = $url_base;
            $url_counter = 1;

            while(db()->where('url', $url)->where('language', $blog_post->language)->has('blog_posts')) {
                $url_counter++;
                $url = string_truncate($url_base, 256 - mb_strlen('-' . $url_counter), '') . '-' . $url_counter;
            }

            /* Copy the image */
            $image = $blog_post->image ? \Altum\Uploads::copy_uploaded_file($blog_post->image, \Altum\Uploads::get_path('blog'), \Altum\Uploads::get_path('blog')) : null;

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Insert to database */
                $blog_post_id = db()->insert('blog_posts', [
                    'blog_posts_category_id' => $blog_post->blog_posts_category_id,
                    'url' => $url,
                    'title' => string_truncate($blog_post->title . ' - ' . l('global.duplicated'), 256, null),
                    'description' => $blog_post->description,
                    'keywords' => $blog_post->keywords,
                    'image' => $image,
                    'image_description' => $blog_post->image_description,
                    'editor' => $blog_post->editor,
                    'content' => $blog_post->content,
                    'language' => $blog_post->language,
                    'is_published' => 0,
                    'datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($blog_post->title) . '</strong>'));

                /* Clear the cache */
                cache()->deleteItemsByTag('blog_posts');

                /* Redirect */
                redirect('admin/blog-post-update/' . $blog_post_id);
            }

        }

        redirect('admin/blog-posts');
    }

    public function delete() {

        $blog_post_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$blog_post = db()->where('blog_post_id', $blog_post_id)->getOne('blog_posts', ['blog_post_id', 'title'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the resource */
            (new BlogPosts())->delete($blog_post->blog_post_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $blog_post->title . '</strong>'));

        }

        redirect('admin/blog-posts');
    }

}
