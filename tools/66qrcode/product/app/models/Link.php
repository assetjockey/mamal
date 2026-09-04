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

use Altum\Uploads;

defined('ALTUMCODE') || die();

class Link extends Model {

    public function get_link_full_url($link, $user, $domains = null) {

        /* Detect the URL of the link */
        if($link->domain_id) {

            /* Get available custom domains */
            if(!$domains) {
                $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($user);
            }

            if(isset($domains[$link->domain_id])) {
                $link->full_url = $domains[$link->domain_id]->scheme . $domains[$link->domain_id]->host . '/' . $link->url . '/';
            }

        } else {

            $link->full_url = SITE_URL . $link->url . '/';

        }

        return $link->full_url;
    }

    public function get_full_links_by_user_id($user_id) {

        /* Get the user links */
        $links = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem('links?user_id=' . $user_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $links_result = database()->query("SELECT `links`.*, `domains`.`scheme`, `domains`.`host` FROM `links` LEFT JOIN `domains` ON `links`.`domain_id` = `domains`.`domain_id` WHERE `links`.`user_id` = {$user_id}");
            while($row = $links_result->fetch_object()) {
                $row->full_url = $row->domain_id ? $row->scheme . $row->host . '/' . $row->url : SITE_URL . $row->url;
                $links[$row->link_id] = $row;
            }

            cache()->save(
                $cache_instance->set($links)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $user_id)
            );

        } else {

            /* Get cache */
            $links = $cache_instance->get();

        }

        return $links;

    }

    public function delete($link_id) {
        $link_id = (int) $link_id;

        $link = db()->where('link_id', $link_id)->getOne('links', ['link_id', 'user_id', 'type', 'file']);

        if(!$link) return;

        if($link->type == 'file') {
            $qr_codes = db()->where('link_id', $link_id)->get('qr_codes', null, ['qr_code', 'qr_code_logo', 'qr_code_background', 'qr_code_foreground']);

            foreach($qr_codes as $qr_code) {
                Uploads::delete_uploaded_file($qr_code->qr_code ?? '', 'qr_codes/logo');
                Uploads::delete_uploaded_file($qr_code->qr_code_logo ?? '', 'qr_codes/logo');
                Uploads::delete_uploaded_file($qr_code->qr_code_background ?? '', 'qr_code_background');
                Uploads::delete_uploaded_file($qr_code->qr_code_foreground ?? '', 'qr_code_foreground');
            }

            Uploads::delete_uploaded_file($link->file, 'qr_code_files');
        }

        /* Delete the link */
        db()->where('link_id', $link_id)->delete('links');

        /* Clear the cache */
        cache()->deleteItemsByTag('link_id=' . $link_id);
        cache()->deleteItem('links?user_id=' . $link->user_id);
        cache()->deleteItem('links_total?user_id=' . $link->user_id);
        cache()->deleteItem('links_dashboard?user_id=' . $link->user_id);

        if($link->type == 'file') {
            cache()->deleteItem('qr_codes_total?user_id=' . $link->user_id);
            cache()->deleteItem('qr_codes_dashboard?user_id=' . $link->user_id);
        }

    }

    public function bulk_delete($links_ids, $user_id = null) {
        $links_ids = array_filter(array_unique(array_map('intval', $links_ids)));
        $user_id = $user_id ? (int) $user_id : null;

        if(!$links_ids) {
            return;
        }

        $database = db()->where('link_id', $links_ids, 'IN');

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $links = $database->get('links', null, ['link_id', 'user_id', 'type', 'file']);
        $links_ids = array_map('intval', array_column($links, 'link_id'));

        if(!$links_ids) {
            return;
        }

        $users_ids = $user_id ? [$user_id] : array_column($links, 'user_id');
        $users_ids = array_filter(array_unique(array_map('intval', $users_ids)));

        foreach($links as $link) {
            if($link->type != 'file') continue;

            $qr_codes = db()->where('link_id', $link->link_id)->get('qr_codes', null, ['qr_code', 'qr_code_logo', 'qr_code_background', 'qr_code_foreground']);

            foreach($qr_codes as $qr_code) {
                Uploads::delete_uploaded_file($qr_code->qr_code ?? '', 'qr_codes/logo');
                Uploads::delete_uploaded_file($qr_code->qr_code_logo ?? '', 'qr_codes/logo');
                Uploads::delete_uploaded_file($qr_code->qr_code_background ?? '', 'qr_code_background');
                Uploads::delete_uploaded_file($qr_code->qr_code_foreground ?? '', 'qr_code_foreground');
            }

            Uploads::delete_uploaded_file($link->file, 'qr_code_files');
        }

        /* Delete the links */
        db()->where('link_id', $links_ids, 'IN')->delete('links');

        /* Clear the links cache */
        foreach($links_ids as $link_id) {
            cache()->deleteItemsByTag('link_id=' . $link_id);
        }

        /* Clear the users cache */
        foreach($users_ids as $user_id) {
            cache()->deleteItem('links?user_id=' . $user_id);
            cache()->deleteItem('links_total?user_id=' . $user_id);
            cache()->deleteItem('links_dashboard?user_id=' . $user_id);
            cache()->deleteItem('qr_codes_total?user_id=' . $user_id);
            cache()->deleteItem('qr_codes_dashboard?user_id=' . $user_id);
        }
    }

}
