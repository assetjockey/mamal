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

use Altum\Models\BlogPosts;
use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class AdminApiBlogPosts extends Controller {
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
        $filters = (new \Altum\Filters(['blog_post_id', 'blog_posts_category_id', 'is_published'], ['title', 'description', 'keywords', 'url'], ['blog_post_id', 'datetime', 'last_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by('blog_post_id', isset($this->user->preferences->default_order_type) ? $this->user->preferences->default_order_type : settings()->main->default_order_type);
        $filters->set_default_results_per_page(isset($this->user->preferences->default_results_per_page) ? $this->user->preferences->default_results_per_page : settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows_result = database()->query("SELECT COUNT(*) AS `total` FROM `blog_posts` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object();
        $total_rows = $total_rows_result ? $total_rows_result->total : 0;
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $page, url('admin-api/blog-posts?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
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
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->blog_post_id,
                'blog_posts_category_id' => $row->blog_posts_category_id ? (int) $row->blog_posts_category_id : null,
                'url' => $row->url,
                'title' => $row->title,
                'description' => $row->description,
                'keywords' => $row->keywords,
                'image' => $row->image,
                'image_url' => $row->image ? \Altum\Uploads::get_full_url('blog') . $row->image : null,
                'image_description' => $row->image_description,
                'editor' => $row->editor,
                'content' => $row->content,
                'language' => $row->language,
                'total_views' => (int) $row->total_views,
                'average_rating' => (float) $row->average_rating,
                'total_ratings' => (int) $row->total_ratings,
                'is_published' => (bool) $row->is_published,
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

        $blog_post_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $blog_post = db()->where('blog_post_id', $blog_post_id)->getOne('blog_posts');

        /* We haven't found the resource */
        if(!$blog_post) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $blog_post->blog_post_id,
            'blog_posts_category_id' => $blog_post->blog_posts_category_id ? (int) $blog_post->blog_posts_category_id : null,
            'url' => $blog_post->url,
            'title' => $blog_post->title,
            'description' => $blog_post->description,
            'keywords' => $blog_post->keywords,
            'image' => $blog_post->image,
            'image_url' => $blog_post->image ? \Altum\Uploads::get_full_url('blog') . $blog_post->image : null,
            'image_description' => $blog_post->image_description,
            'editor' => $blog_post->editor,
            'content' => $blog_post->content,
            'language' => $blog_post->language,
            'total_views' => (int) $blog_post->total_views,
            'average_rating' => (float) $blog_post->average_rating,
            'total_ratings' => (int) $blog_post->total_ratings,
            'is_published' => (bool) $blog_post->is_published,
            'datetime' => $blog_post->datetime,
            'last_datetime' => $blog_post->last_datetime,
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
        $_POST['image_description'] = input_clean(isset($_POST['image_description']) ? $_POST['image_description'] : '', 256);
        $_POST['keywords'] = input_clean(isset($_POST['keywords']) ? $_POST['keywords'] : '', 256);
        $_POST['editor'] = isset($_POST['editor']) ? input_clean($_POST['editor']) : 'raw';
        $_POST['blog_posts_category_id'] = empty($_POST['blog_posts_category_id']) ? null : (int) $_POST['blog_posts_category_id'];
        $_POST['language'] = !empty($_POST['language']) ? input_clean($_POST['language']) : null;
        $_POST['is_published'] = isset($_POST['is_published']) ? (int) (bool) $_POST['is_published'] : 0;
        $_POST['content'] = isset($_POST['content']) ? $_POST['content'] : '';

        if($_POST['editor'] != 'raw') {
            $this->response_error(sprintf(l('api_documentation.allowed_values'), 'raw'), 401);
        }

        if($_POST['blog_posts_category_id'] && !db()->where('blog_posts_category_id', $_POST['blog_posts_category_id'])->has('blog_posts_categories')) {
            $this->return_404();
        }

        if(db()->where('url', $_POST['url'])->where('language', $_POST['language'])->has('blog_posts')) {
            $this->response_error(l('admin_blog.error_message.url_exists'), 401);
        }

        $image_new_name = \Altum\Uploads::process_upload(null, 'blog', 'image', 'image_remove', null, 'json_error');

        /* Database query */
        $datetime = get_date();
        $blog_post_id = db()->insert('blog_posts', [
            'blog_posts_category_id' => $_POST['blog_posts_category_id'],
            'url' => $_POST['url'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'keywords' => $_POST['keywords'],
            'image' => $image_new_name,
            'image_description' => $_POST['image_description'],
            'editor' => $_POST['editor'],
            'content' => $_POST['content'],
            'language' => $_POST['language'],
            'is_published' => $_POST['is_published'],
            'datetime' => $datetime,
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('blog_posts');

        /* Prepare the data */
        $data = [
            'id' => (int) $blog_post_id,
            'blog_posts_category_id' => $_POST['blog_posts_category_id'],
            'url' => $_POST['url'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'keywords' => $_POST['keywords'],
            'image' => $image_new_name,
            'image_url' => $image_new_name ? \Altum\Uploads::get_full_url('blog') . $image_new_name : null,
            'image_description' => $_POST['image_description'],
            'editor' => $_POST['editor'],
            'content' => $_POST['content'],
            'language' => $_POST['language'],
            'total_views' => 0,
            'average_rating' => 0,
            'total_ratings' => 0,
            'is_published' => (bool) $_POST['is_published'],
            'datetime' => $datetime,
            'last_datetime' => null,
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $blog_post_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $blog_post = db()->where('blog_post_id', $blog_post_id)->getOne('blog_posts');

        /* We haven't found the resource */
        if(!$blog_post) {
            $this->return_404();
        }

        /* Check for any errors */
        foreach(['title', 'url'] as $field) {
            if(isset($_POST[$field]) && trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $editor_is_submitted = isset($_POST['editor']);
        $content_is_submitted = isset($_POST['content']);

        /* Filter some of the variables */
        $_POST['url'] = isset($_POST['url']) ? input_clean(get_slug($_POST['url']), 256) : $blog_post->url;
        $_POST['title'] = isset($_POST['title']) ? input_clean($_POST['title'], 256) : $blog_post->title;
        $_POST['description'] = isset($_POST['description']) ? input_clean($_POST['description'], 256) : $blog_post->description;
        $_POST['image_description'] = isset($_POST['image_description']) ? input_clean($_POST['image_description'], 256) : $blog_post->image_description;
        $_POST['keywords'] = isset($_POST['keywords']) ? input_clean($_POST['keywords'], 256) : $blog_post->keywords;
        $_POST['editor'] = $editor_is_submitted ? input_clean($_POST['editor']) : $blog_post->editor;
        $_POST['blog_posts_category_id'] = isset($_POST['blog_posts_category_id']) ? (empty($_POST['blog_posts_category_id']) ? null : (int) $_POST['blog_posts_category_id']) : $blog_post->blog_posts_category_id;
        $_POST['language'] = isset($_POST['language']) ? (!empty($_POST['language']) ? input_clean($_POST['language']) : null) : $blog_post->language;
        $_POST['is_published'] = isset($_POST['is_published']) ? (int) (bool) $_POST['is_published'] : $blog_post->is_published;
        $_POST['content'] = $content_is_submitted ? $_POST['content'] : $blog_post->content;

        if($editor_is_submitted && $_POST['editor'] != 'raw') {
            $this->response_error(sprintf(l('api_documentation.allowed_values'), 'raw'), 401);
        }

        if($content_is_submitted) {
            $_POST['editor'] = 'raw';
        }

        if($_POST['blog_posts_category_id'] && !db()->where('blog_posts_category_id', $_POST['blog_posts_category_id'])->has('blog_posts_categories')) {
            $this->return_404();
        }

        if(db()->where('blog_post_id', $blog_post->blog_post_id, '<>')->where('url', $_POST['url'])->where('language', $_POST['language'])->has('blog_posts')) {
            $this->response_error(l('admin_blog.error_message.url_exists'), 401);
        }

        $blog_post->image = \Altum\Uploads::process_upload($blog_post->image, 'blog', 'image', 'image_remove', null, 'json_error');

        /* Database query */
        $last_datetime = get_date();
        db()->where('blog_post_id', $blog_post->blog_post_id)->update('blog_posts', [
            'blog_posts_category_id' => $_POST['blog_posts_category_id'],
            'url' => $_POST['url'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'keywords' => $_POST['keywords'],
            'image' => $blog_post->image,
            'image_description' => $_POST['image_description'],
            'editor' => $_POST['editor'],
            'content' => $_POST['content'],
            'language' => $_POST['language'],
            'is_published' => $_POST['is_published'],
            'last_datetime' => $last_datetime,
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('blog_posts');

        /* Prepare the data */
        $data = [
            'id' => (int) $blog_post->blog_post_id,
            'blog_posts_category_id' => $_POST['blog_posts_category_id'] ? (int) $_POST['blog_posts_category_id'] : null,
            'url' => $_POST['url'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'keywords' => $_POST['keywords'],
            'image' => $blog_post->image,
            'image_url' => $blog_post->image ? \Altum\Uploads::get_full_url('blog') . $blog_post->image : null,
            'image_description' => $_POST['image_description'],
            'editor' => $_POST['editor'],
            'content' => $_POST['content'],
            'language' => $_POST['language'],
            'total_views' => (int) $blog_post->total_views,
            'average_rating' => (float) $blog_post->average_rating,
            'total_ratings' => (int) $blog_post->total_ratings,
            'is_published' => (bool) $_POST['is_published'],
            'datetime' => $blog_post->datetime,
            'last_datetime' => $last_datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $blog_post_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $blog_post = db()->where('blog_post_id', $blog_post_id)->getOne('blog_posts', ['blog_post_id']);

        /* We haven't found the resource */
        if(!$blog_post) {
            $this->return_404();
        }

        /* Delete the resource */
        (new BlogPosts())->delete($blog_post->blog_post_id);

        http_response_code(200);
        die();

    }

}
