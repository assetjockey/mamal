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

class WebsitesHeatmaps extends Model {

    public function get_website_heatmaps_by_website_id($website_id) {

        $cache_instance = cache()->getItem('website_heatmaps?website_id=' . $website_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            $result = database()->query("SELECT * FROM `websites_heatmaps` WHERE `website_id` = {$website_id}");
            $data = [];

            while($row = $result->fetch_object()) {

                $data[] = $row;

            }

            cache()->save(
                $cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('website_id=' . $website_id)
            );

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;
    }

}
