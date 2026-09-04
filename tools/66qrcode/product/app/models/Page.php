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

use Altum\Language;

defined('ALTUMCODE') || die();

class Page extends Model {

    public function get_pages($position) {

        $pages_data = [];

        $cache_instance = cache()->getItem('pages_all');

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {
            $result = database()->query('SELECT `url`, `title`, `type`, `open_in_new_tab`, `language`, `icon`, `position`, `plans_ids`, `footer_category_id` FROM `pages` WHERE `is_published` = 1 ORDER BY `order`');

            while($row = $result->fetch_object()) {
                $row->plans_ids = json_decode($row->plans_ids ?? '');

                $pages_data[] = $row;
            }

            cache()->save($cache_instance->set($pages_data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('pages'));

        } else {

            /* Get cache */
            $pages_data = $cache_instance->get();

        }

        $filtered_pages = [];

        foreach($pages_data as $page) {

            /* Only keep pages that match the requested position */
            if($page->position != $position) {
                continue;
            }

            /* Make sure the language of the page still exists */
            if($page->language && !isset(\Altum\Language::$active_languages[$page->language])) {
                continue;
            }

            if($page->type == 'internal') {
                $page->url = SITE_URL . ($page->language ? \Altum\Language::$active_languages[$page->language] . '/' : null) . 'page/' . $page->url;
            }

            $page->target = $page->open_in_new_tab ? '_blank' : '_self';

            /* Check language */
            if($page->language && $page->language != Language::$name) {
                continue;
            }

            /* Filter by plan if needed */
            if(!empty($page->plans_ids)) {
                if(!is_logged_in()) continue;

                if(!in_array(user()->plan_id, $page->plans_ids)) {
                    continue;
                }
            }

            $filtered_pages[] = $page;
        }

        return $filtered_pages;
    }

}
