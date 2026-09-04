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
use Altum\Models\BlogPosts;
use Altum\Models\BlogPostsCategories;
use Altum\Title;

defined('ALTUMCODE') || die();

class AdminBlogPostPreview extends Controller {

    public function index() {

        $blog_post_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Check if resource exists */
        if(!$blog_post = db()->where('blog_post_id', $blog_post_id)->getOne('blog_posts')) {
            redirect('admin/blog-posts');
        }

        $language = $blog_post->language ? $blog_post->language : Language::$name;

        /* Transform content if needed */
        $blog_post->content = json_decode($blog_post->content) ? convert_editorjs_json_to_html($blog_post->content) : output_blog_post_content($blog_post->content);
        $blog_post->table_of_contents = [];

        /* Generate table of contents */
        if(isset(settings()->content->blog_table_of_contents_is_enabled) && settings()->content->blog_table_of_contents_is_enabled) {
            $table_of_contents = get_blog_post_table_of_contents($blog_post->content);
            $blog_post->content = $table_of_contents->content;
            $blog_post->table_of_contents = $table_of_contents->items;
        }

        /* Get the blog post category */
        $blog_posts_category = $blog_post->blog_posts_category_id ? db()->where('blog_posts_category_id', $blog_post->blog_posts_category_id)->getOne('blog_posts_categories') : null;

        /* Get the blog post author */
        $blog_authors = isset(settings()->content->blog_authors) ? (array) settings()->content->blog_authors : [];
        $blog_post->author = null;

        if(isset(settings()->content->blog_authors_is_enabled) && settings()->content->blog_authors_is_enabled && isset($blog_post->author_id) && $blog_post->author_id && isset($blog_authors[$blog_post->author_id])) {
            $blog_post->author = $blog_authors[$blog_post->author_id];
        }

        /* Get all the categories */
        $blog_posts_categories = settings()->content->blog_categories_widget_is_enabled ? (new BlogPostsCategories())->get_blog_posts_categories_by_language($language) : [];

        /* Get popular posts */
        $blog_posts_popular = settings()->content->blog_popular_widget_is_enabled ? (new BlogPosts())->get_popular_blog_posts_by_language($language, settings()->content->blog_popular_widget_posts_limit) : [];

        /* Get latest posts */
        $blog_posts_latest = settings()->content->blog_latest_widget_is_enabled ? (new BlogPosts())->get_latest_blog_posts_by_language($language, settings()->content->blog_latest_widget_posts_limit) : [];

        /* Get related posts */
        $blog_posts_related = settings()->content->blog_related_posts_is_enabled ? (new BlogPosts())->get_related_blog_posts_by_language($language, $blog_post->blog_post_id, $blog_post->blog_posts_category_id, settings()->content->blog_related_posts_limit) : [];

        /* Prepare the view */
        $data = [
            'blog_post' => $blog_post,
            'blog_posts_category' => $blog_posts_category,
            'blog_posts_categories' => $blog_posts_categories,
            'blog_posts_popular' => $blog_posts_popular,
            'blog_posts_latest' => $blog_posts_latest,
            'blog_posts_related' => $blog_posts_related,
        ];

        $view = new \Altum\View('blog/blog_post', (array) $this);

        $this->add_view_content('content', $view->run($data));

        /* Set a custom title */
        Title::set(sprintf(l('blog.blog_post.title'), $blog_post->title));

        /* Meta */
        Meta::set_description($blog_post->description);
        Meta::set_keywords($blog_post->keywords);
        Meta::set_robots('noindex');
        Meta::set_link_alternate(false);

        if($blog_post->image) {
            Meta::set_social_image(\Altum\Uploads::get_full_url('blog') . $blog_post->image);
        }

        /* Use the public wrapper */
        \Altum\Router::$path = '';
    }

}
