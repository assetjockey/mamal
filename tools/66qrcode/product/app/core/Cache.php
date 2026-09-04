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

namespace Altum;

/* Simple wrapper for phpFastCache */

defined('ALTUMCODE') || die();

class Cache {
    public static $adapter;
    public static $driver = 'Devnull';

    public static function initialize($force_enable = false) {

        $driver = self::$driver;
        if($force_enable || CACHE) {
            $driver = defined('REDIS_IS_ENABLED') && REDIS_IS_ENABLED ? 'redis' : 'files';

            if(defined('CACHE_DRIVER') && in_array(CACHE_DRIVER, ['files', 'redis', 'apcu'])) {
                $driver = CACHE_DRIVER;
            }
        }

        /* Prepare cache adapter configuration for phpFastCache based on the need */

        /* Local files */
        if($driver == 'files') {
            $config = new \Phpfastcache\Drivers\Files\Config([
                'securityKey' => PRODUCT_KEY,
                'path' => UPLOADS_PATH . 'cache',
                'preventCacheSlams' => true,
                'cacheSlamsTimeout' => 20,
                'secureFileManipulation' => true
            ]);
        }

        /* Redis server */
        elseif($driver == 'redis') {
            $redis_config = [
                'database' => REDIS_DATABASE,
                'timeout'  => REDIS_TIMEOUT,
                'password' => REDIS_PASSWORD,
            ];

            if(defined('REDIS_SOCKET_PATH') && is_string(REDIS_SOCKET_PATH)) {
                $redis_config = $redis_config + [
                        'path' => REDIS_SOCKET_PATH,
                    ];
            } else {
                $redis_config = $redis_config + [
                        'host' => REDIS_HOST,
                        'port' => REDIS_PORT,
                    ];
            }

            if(defined('REDIS_PREFIX') && is_string(REDIS_PREFIX)) {
                $redis_config = $redis_config + [
                        'optPrefix' => REDIS_PREFIX,
                    ];
            }

            $config = new \Phpfastcache\Drivers\Redis\Config($redis_config);
        }

        elseif($driver == 'apcu') {
            $config = new \Phpfastcache\Drivers\Apcu\Config([
                'optPrefix' => md5(SITE_URL) . ':',
            ]);
        }

        /* Cache disabled */
        elseif($driver == 'Devnull') {
            $config = new \Phpfastcache\Config\ConfigurationOption([
                'path' => UPLOADS_PATH . 'cache',
            ]);
        }

        self::$adapter = \Phpfastcache\CacheManager::getInstance($driver, $config);
        self::$driver = $driver;
    }

    public static function cache_function_result($key, $tag, $function_to_cache, $cached_seconds = CACHE_DEFAULT_SECONDS) {
        if(!$cached_seconds) return $function_to_cache();

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem($key);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            $result = $function_to_cache();

            $cache_item = $cache_instance->set($result)->expiresAfter($cached_seconds);

            if($tag) {
                if(is_array($tag)) {
                    foreach($tag as $tag_key) $cache_item->addTag($tag_key);
                } else {
                    $cache_item->addTag($tag);
                }
            }

            cache()->save($cache_item);

        } else {

            /* Get cache */
            $result = $cache_instance->get();

        }

        return $result;
    }

}
