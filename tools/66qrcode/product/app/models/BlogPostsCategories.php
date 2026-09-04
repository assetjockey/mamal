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

class BlogPostsCategories extends Model {

    public function get_blog_posts_categories_by_language($language) {

        /* Get the resources */
        $blog_posts_categories = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem('blog_posts_categories?language=' . $language);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $blog_posts_categories_result = database()->query("
                SELECT * 
                FROM `blog_posts_categories`
                WHERE `language` = '{$language}' OR `language` IS NULL
                ORDER BY `order` ASC
            ");
            while($row = $blog_posts_categories_result->fetch_object()) $blog_posts_categories[$row->blog_posts_category_id] = $row;

            cache()->save(
                $cache_instance->set($blog_posts_categories)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('blog_posts_categories')
            );

        } else {

            /* Get cache */
            $blog_posts_categories = $cache_instance->get();

        }

        return $blog_posts_categories;

    }

}
