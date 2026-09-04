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
namespace Altum\Models;

defined('ALTUMCODE') || die();

class BlogPosts extends Model {

    public function get_featured_blog_post_by_language($language) {

        /* Try to check if the featured post exists via the cache */
        $cache_instance = cache()->getItem('blog_posts?type=featured&language=' . $language);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $blog_post = database()->query("
                SELECT *
                FROM `blog_posts`
                WHERE (`language` = '{$language}' OR `language` IS NULL) AND `is_published` = 1 AND `is_featured` = 1
                ORDER BY `language` DESC
                LIMIT 1
            ")->fetch_object();

            cache()->save(
                $cache_instance->set($blog_post)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('blog_posts')
            );

        } else {

            /* Get cache */
            $blog_post = $cache_instance->get();

        }

        return $blog_post;

    }

    public function get_popular_blog_posts_by_language($language, $limit) {

        /* Prepare the limit */
        $limit = (int) $limit;

        /* Get the resources */
        $blog_posts = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem('blog_posts?type=popular&language=' . $language . '&limit=' . $limit);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $blog_posts_result = database()->query("
                SELECT *
                FROM `blog_posts`
                WHERE (`language` = '{$language}' OR `language` IS NULL) AND `is_published` = 1
                ORDER BY `total_views` DESC
                LIMIT {$limit}
            ");
            while($row = $blog_posts_result->fetch_object()) $blog_posts[$row->blog_post_id] = $row;

            cache()->save(
                $cache_instance->set($blog_posts)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('blog_posts')
            );

        } else {

            /* Get cache */
            $blog_posts = $cache_instance->get();

        }

        return $blog_posts;

    }

    public function get_latest_blog_posts_by_language($language, $limit) {

        /* Prepare the limit */
        $limit = (int) $limit;

        /* Get the resources */
        $blog_posts = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem('blog_posts?type=latest&language=' . $language . '&limit=' . $limit);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $blog_posts_result = database()->query("
                SELECT *
                FROM `blog_posts`
                WHERE (`language` = '{$language}' OR `language` IS NULL) AND `is_published` = 1
                ORDER BY `datetime` DESC, `blog_post_id` DESC
                LIMIT {$limit}
            ");
            while($row = $blog_posts_result->fetch_object()) $blog_posts[$row->blog_post_id] = $row;

            cache()->save(
                $cache_instance->set($blog_posts)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('blog_posts')
            );

        } else {

            /* Get cache */
            $blog_posts = $cache_instance->get();

        }

        return $blog_posts;

    }

    public function get_related_blog_posts_by_language($language, $blog_post_id, $blog_posts_category_id, $limit) {

        /* Prepare the values */
        $blog_post_id = (int) $blog_post_id;
        $blog_posts_category_id = (int) $blog_posts_category_id;
        $limit = (int) $limit;

        /* Get the resources */
        $blog_posts = [];

        if(!$blog_posts_category_id) return $blog_posts;

        /* Try to check if the related posts exist via the cache */
        $cache_instance = cache()->getItem('blog_posts?type=related&language=' . $language . '&blog_post_id=' . $blog_post_id . '&blog_posts_category_id=' . $blog_posts_category_id . '&limit=' . $limit);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $blog_posts_result = database()->query("
                SELECT *
                FROM `blog_posts`
                WHERE `blog_post_id` <> {$blog_post_id} AND `blog_posts_category_id` = {$blog_posts_category_id} AND (`language` = '{$language}' OR `language` IS NULL) AND `is_published` = 1
                ORDER BY `datetime` DESC, `blog_post_id` DESC
                LIMIT {$limit}
            ");
            while($row = $blog_posts_result->fetch_object()) $blog_posts[$row->blog_post_id] = $row;

            cache()->save(
                $cache_instance->set($blog_posts)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('blog_posts')
            );

        } else {

            /* Get cache */
            $blog_posts = $cache_instance->get();

        }

        return $blog_posts;

    }

    public function delete($blog_post_id) {

        $blog_post = db()->where('blog_post_id', $blog_post_id)->getOne('blog_posts', ['blog_post_id', 'image']);

        if(!$blog_post) return;

        \Altum\Uploads::delete_uploaded_file($blog_post->image, 'blog');

        /* Delete the resource */
        db()->where('blog_post_id', $blog_post_id)->delete('blog_posts');

        /* Clear the cache */
        cache()->deleteItemsByTag('blog_posts');

    }

}
