<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
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

namespace Altum\Models;

defined('ALTUMCODE') || die();

class StatusPage extends Model {

    public function get_status_page_full_url($status_page, $user, $domains = null) {

        /* Detect the URL of the status_page */
        if($status_page->domain_id) {

            /* Get available custom domains */
            if(!$domains) {
                $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($user, false);
            }

            if(isset($domains[$status_page->domain_id])) {


                if($status_page->status_page_id == $domains[$status_page->domain_id]->status_page_id) {

                    $status_page->full_url = $domains[$status_page->domain_id]->scheme . $domains[$status_page->domain_id]->host . '/';

                } else {

                    $status_page->full_url = $domains[$status_page->domain_id]->scheme . $domains[$status_page->domain_id]->host . '/' . $status_page->url . '/';

                }

            }

        } else {

            $status_page->full_url = SITE_URL . $status_page->url . '/';

        }

        return $status_page->full_url;
    }

    public function get_status_page_by_url($status_page_url) {

        /* Get the status_page */
        $status_page = null;

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = cache()->getItem('s_status_page?url=' . $status_page_url);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $status_page = database()->query("SELECT * FROM `status_pages` WHERE `url` = '{$status_page_url}' AND `domain_id` IS NULL")->fetch_object() ?? null;

            if($status_page) {
                cache()->save(
                    $cache_instance->set($status_page)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('status_page_id=' . $status_page->status_page_id)
                );
            }

        } else {

            /* Get cache */
            $status_page = $cache_instance->get();

        }

        return $status_page;

    }

    public function get_status_page_by_url_and_domain_id($status_page_url, $domain_id) {

        /* Get the status_page */
        $status_page = null;

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = cache()->getItem('s_status_page?url=' . $status_page_url . '&domain_id=' . $domain_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $status_page = database()->query("SELECT * FROM `status_pages` WHERE `url` = '{$status_page_url}' AND `domain_id` = {$domain_id}")->fetch_object() ?? null;

            if($status_page) {
                cache()->save(
                    $cache_instance->set($status_page)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('status_page_id=' . $status_page->status_page_id)
                );
            }

        } else {

            /* Get cache */
            $status_page = $cache_instance->get();

        }

        return $status_page;

    }

    public function get_status_page_by_status_page_id($status_page_id) {

        /* Get the status_page */
        $status_page = null;

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = cache()->getItem('s_status_page?status_page_id=' . $status_page_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $status_page = database()->query("SELECT * FROM `status_pages` WHERE `status_page_id` = '{$status_page_id}'")->fetch_object() ?? null;

            if($status_page) {
                cache()->save(
                    $cache_instance->set($status_page)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('status_page_id=' . $status_page->status_page_id)
                );
            }

        } else {

            /* Get cache */
            $status_page = $cache_instance->get();

        }

        return $status_page;

    }

    public function delete($status_page_id) {

        $status_page = db()->where('status_page_id', $status_page_id)->getOne('status_pages', ['user_id', 'status_page_id', 'logo', 'favicon', 'opengraph', 'pwa_icon', 'settings']);

        if(!$status_page) return;

        $status_page->settings = json_decode($status_page->settings ?? '');

        \Altum\Uploads::delete_uploaded_file($status_page->logo, 'status_pages_logos');
        \Altum\Uploads::delete_uploaded_file($status_page->favicon, 'status_pages_favicons');
        \Altum\Uploads::delete_uploaded_file($status_page->opengraph, 'status_pages_opengraph');
        \Altum\Uploads::delete_uploaded_file($status_page->pwa_icon, 'status_pages_pwa_icon');

        /* Delete the status_page */
        db()->where('status_page_id', $status_page_id)->delete('status_pages');

        /* Clear the cache */
        cache()->deleteItemsByTag('status_page_id=' . $status_page_id);
        cache()->deleteItem('status_pages_dashboard?user_id=' . $status_page->user_id);

    }

}
