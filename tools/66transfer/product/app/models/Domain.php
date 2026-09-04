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

class Domain extends Model {

    public function get_available_domains_by_user($user) {
        if(!settings()->transfers->domains_is_enabled) return [];

        /* Get the domains */
        $domains = [];

        /* Try to check if the domain posts exists via the cache */
        $cache_instance = cache()->getItem('domains?user_id=' . $user->user_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            $user_id_where = !is_null($user->user_id) ? "`user_id` = {$user->user_id}" : "`user_id` IS NULL";

            /* Where */
            if(settings()->transfers->additional_domains_is_enabled) {
                $where = "({$user_id_where} OR `type` = 1)";
            } else {
                $where = $user_id_where;
            }

            $where .= " AND `is_enabled` = 1";

            /* Get data from the database */
            $domains_result = database()->query("SELECT * FROM `domains` WHERE {$where}");
            while($row = $domains_result->fetch_object()) {
                if($row->type == 1 && !in_array($row->domain_id, $user->plan_settings->additional_domains ?? [])) continue;

                /* Build the url */
                $row->url = $row->scheme . $row->host . '/';

                $domains[$row->domain_id] = $row;
            }

            /* Properly tag the cache */
            $cache_instance->set($domains)->expiresAfter(CACHE_DEFAULT_SECONDS);

            cache()->save($cache_instance);

        } else {

            /* Get cache */
            $domains = $cache_instance->get();

        }

        return $domains;

    }

    public function get_available_additional_domains() {
        if(!settings()->transfers->additional_domains_is_enabled) return [];

        /* Get the domains */
        $domains = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem('available_additional_domains');

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $domains_result = database()->query("SELECT * FROM `domains` WHERE `is_enabled` = 1 AND `type` = 1");
            while($row = $domains_result->fetch_object()) {

                /* Build the url */
                $row->url = $row->scheme . $row->host . '/';

                $domains[$row->domain_id] = $row;
            }

            cache()->save(
                $cache_instance->set($domains)->expiresAfter(CACHE_DEFAULT_SECONDS)
            );

        } else {

            /* Get cache */
            $domains = $cache_instance->get();

        }

        return $domains;
    }

    public function get_domain_by_host($host) {
        if(!settings()->transfers->domains_is_enabled) return null;

        /* Get the domain */
        $domain = null;

        /* Try to check if the domain posts exists via the cache */
        $cache_instance = cache()->getItem('domain?host=' . md5($host));

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $domain = db()->where('host', $host)->getOne('domains');

            if($domain) {
                /* Build the url */
                $domain->url = $domain->scheme . $domain->host . '/';

                cache()->save(
                    $cache_instance->set($domain)->expiresAfter(CACHE_DEFAULT_SECONDS)
                );
            }

        } else {

            /* Get cache */
            $domain = $cache_instance->get();

        }

        return $domain;

    }

    public function delete($domain_id) {
        $domain_id = (int) $domain_id;

        /* Get the resource */
        $domain = db()->where('domain_id', $domain_id)->getOne('domains');

        if(!$domain) {
            return;
        }

        /* Get all transfers related to the domain */
        $transfers_ids = db()->where('domain_id', $domain_id)->getValue('transfers', 'transfer_id', null);

        /* Delete all transfers related to the domain */
        if($transfers_ids) {
            (new \Altum\Models\Transfers())->bulk_delete($transfers_ids);
        }

        /* Get all transfer requests related to the domain */
        $transfer_requests_ids = db()->where('domain_id', $domain_id)->getValue('transfer_requests', 'transfer_id', null);

        /* Delete all transfer requests related to the domain */
        if($transfer_requests_ids) {
            (new \Altum\Models\TransferRequests())->bulk_delete($transfer_requests_ids);
        }

        /* Delete the resource */
        db()->where('domain_id', $domain_id)->delete('domains');

        /* Clear the cache */
        cache()->deleteItems(['domain?domain_id=' . $domain_id, 'domains?user_id=' . $domain->user_id, 'domains_total?user_id=' . $domain->user_id]);
        cache()->deleteItem('available_additional_domains');

    }

}
