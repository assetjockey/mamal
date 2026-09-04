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

class AdminBlogPostCreate extends Controller {

    public function index() {

        /* Blog authors */
        $blog_authors = isset(settings()->content->blog_authors) ? (array) settings()->content->blog_authors : [];
        $blog_authors_is_enabled = isset(settings()->content->blog_authors_is_enabled) && settings()->content->blog_authors_is_enabled;

        if(!empty($_POST)) {
            /* Filter some of the variables */
            $_POST['url'] = input_clean(get_slug($_POST['url']), 256);
            $_POST['title'] = input_clean($_POST['title'], 256);
            $_POST['description'] = input_clean($_POST['description'], 256);
            $_POST['image_description'] = input_clean($_POST['image_description'], 256);
            $_POST['keywords'] = input_clean($_POST['keywords'], 256);
            $_POST['editor'] = in_array($_POST['editor'], ['wysiwyg', 'blocks', 'raw']) ? input_clean($_POST['editor']) : 'raw';
            $_POST['blog_posts_category_id'] = empty($_POST['blog_posts_category_id']) ? null : (int) $_POST['blog_posts_category_id'];
            $_POST['author_id'] = !empty($_POST['author_id']) ? input_clean(get_slug($_POST['author_id']), 64) : null;
            $_POST['language'] = !empty($_POST['language']) ? input_clean($_POST['language']) : null;
            $_POST['is_published'] = (int) isset($_POST['is_published']);
            $_POST['is_featured'] = (int) isset($_POST['is_featured']);
            $_POST['content'] = $_POST['editor'] == 'wysiwyg' ? quilljs_to_bootstrap($_POST['content']) : $_POST['content'];

            /* Validate blog author */
            if(!$blog_authors_is_enabled || !$_POST['author_id'] || !isset($blog_authors[$_POST['author_id']])) {
                $_POST['author_id'] = null;
            }

            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

            /* Check for any errors */
            $required_fields = ['title', 'url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(db()->where('url', $_POST['url'])->where('language', $_POST['language'])->has('blog_posts')) {
                Alerts::add_field_error('url', l('admin_blog.error_message.url_exists'));
            }

            $image_new_name = \Altum\Uploads::process_upload(null, 'blog', 'image', 'image_remove', null);

            /* If there are no errors, continue */
            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Database query */
                $blog_post_id = db()->insert('blog_posts', [
                    'blog_posts_category_id' => $_POST['blog_posts_category_id'],
                    'author_id' => $_POST['author_id'],
                    'url' => $_POST['url'],
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'keywords' => $_POST['keywords'],
                    'image' => $image_new_name ?? null,
                    'image_description' => $_POST['image_description'],
                    'editor' => $_POST['editor'],
                    'content' => $_POST['content'],
                    'language' => $_POST['language'],
                    'is_published' => $_POST['is_published'],
                    'is_featured' => $_POST['is_featured'],
                    'datetime' => get_date(),
                ]);

                /* Keep only one featured post */
                if($_POST['is_featured']) {
                    db()->where('blog_post_id', $blog_post_id, '<>')->update('blog_posts', ['is_featured' => 0]);
                }

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['title'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItemsByTag('blog_posts');

                redirect('admin/blog-posts');
            }

        }

        /* Get the blog posts categories available */
        $blog_posts_categories = db()->get('blog_posts_categories', null, ['blog_posts_category_id', 'title']);

        /* Set default values */
        $values = [
            'blog_posts_category_id' => $_POST['blog_posts_category_id'] ?? '',
            'author_id' => isset($_POST['author_id']) ? $_POST['author_id'] : '',
            'title' => $_POST['title'] ?? '',
            'url' => $_POST['url'] ?? '',
            'description' => $_POST['description'] ?? '',
            'image_description' => $_POST['image_description'] ?? '',
            'keywords' => $_POST['keywords'] ?? '',
            'editor' => $_POST['editor'] ?? 'blocks',
            'content' => $_POST['content'] ?? '',
            'language' => $_POST['language'] ?? '',
            'is_published' => $_POST['is_published'] ?? '',
            'is_featured' => isset($_POST['is_featured']) ? $_POST['is_featured'] : '',
        ];

        $data = [
            'values' => $values,
            'blog_posts_categories' => $blog_posts_categories,
            'blog_authors' => $blog_authors,
            'blog_authors_is_enabled' => $blog_authors_is_enabled,
        ];

        /* Main View */
        $view = new \Altum\View('admin/blog-post-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }

}
