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

class Domain extends Model {

	public function get_available_domains_by_user($user) {
		if(!settings()->analytics->domains_is_enabled) return [];

		/* Get the domains */
		$domains = [];

		/* Try to check if the domain posts exists via the cache */
		$cache_instance = cache()->getItem('domains?user_id=' . $user->user_id);

		/* Set cache if not existing */
		if(!$cache_instance->isHit()) {

			/* Where */
			if(settings()->analytics->additional_domains_is_enabled) {
				$where = "(`user_id` = {$user->user_id} OR `type` = 1)";
			} else {
				$where = "`user_id` = {$user->user_id}";
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

    public function get_available_domains_by_user_id($user_id) {
        if(!settings()->analytics->domains_is_enabled) return [];

        /* Get the domains */
        $domains = [];

        /* Try to check if the domain posts exists via the cache */
        $cache_instance = cache()->getItem('domains?user_id=' . $user_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $domains_result = database()->query("
                SELECT 
                    *
                FROM 
                    `domains` 
                WHERE 
                    `user_id` = {$user_id}
                    AND `is_enabled` = 1
            ");
            while($row = $domains_result->fetch_object()) {
                $row->url = $row->scheme . $row->host . '/';
                $domains[$row->domain_id] = $row;
            }

            /* Properly tag the cache */
            $cache_instance->set($domains)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('domains?user_id=' . $user_id);

            cache()->save($cache_instance);

        } else {

            /* Get cache */
            $domains = $cache_instance->get();

        }

        return $domains;

    }

	public function get_available_additional_domains() {
		if(!settings()->analytics->additional_domains_is_enabled) return [];

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

    public function get_domain_by_domain_id($domain_id) {
        if(!settings()->analytics->domains_is_enabled) return null;

        /* Get the domain */
        $domain = null;

        /* Try to check if the domain posts exists via the cache */
        $cache_instance = cache()->getItem('domain?domain_id=' . $domain_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $domain = db()->where('domain_id', $domain_id)->getOne('domains');

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

    public function get_domain_by_host($host) {
        if(!settings()->analytics->domains_is_enabled) return null;

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

        /* Get the resource */
        $domain = db()->where('domain_id', $domain_id)->getOne('domains');

        /* Delete the resource */
        db()->where('domain_id', $domain_id)->delete('domains');

        /* Clear the cache */
        cache()->deleteItems(['domain?domain_id=' . $domain_id, 'domains?user_id=' . $domain->user_id, 'domains_total?user_id=' . $domain->user_id]);

    }
}
