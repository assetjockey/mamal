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

defined('ALTUMCODE') || die();

class Plugin {
    public static $plugins = [];

    public static function initialize() {

        /* Load all plugins from cache or scan them */
        self::$plugins = self::get_plugins_registry();

        /* Load the init file for active plugins */
        foreach(self::$plugins as $plugin) {
            if(($plugin->status == 1 || $plugin->status == 'active') && file_exists($plugin->path . 'init.php')) {
                require_once $plugin->path . 'init.php';
            }
        }

    }

    public static function get_plugins_registry() {
        $cache_path = UPLOADS_PATH . 'cache/plugins.php';

        /* Load the cached plugins registry */
        if(ALTUMCODE == 66 && is_file($cache_path)) {
            $plugins = require $cache_path;

            foreach($plugins as $plugin_id => $plugin) {
                $plugins[$plugin_id] = (object) $plugin;

                if(isset($plugins[$plugin_id]->settings) && is_array($plugins[$plugin_id]->settings)) {
                    $plugins[$plugin_id]->settings = (object) $plugins[$plugin_id]->settings;
                }
            }

            return $plugins;
        }

        $plugins = [];

        /* Check for plugin availability */
        foreach(require APP_PATH . 'includes/plugins.php' as $plugin) {
            $plugin_directory = PLUGINS_PATH . $plugin . '/';
            $config_exists = false;

            if(!file_exists($plugin_directory)) {
                continue;
            }

            /* Make sure the plugin has a config.php file */
            if(file_exists($plugin_directory . 'config.php')) {
                $config_exists = true;

                /* Parse the config.php file */
                $config = include $plugin_directory . 'config.php';
            }

            if(!$config_exists && file_exists($plugin_directory . 'config.json')) {
                $config_exists = true;

                /* Parse the config.json file */
                $config_json = file_get_contents($plugin_directory . 'config.json');

                if($config_json === false) {
                    continue;
                }

                $config = json_decode($config_json);

                /* Make sure the json has been parsed properly */
                if(json_last_error() !== JSON_ERROR_NONE) {
                    continue;
                }
            }

            if(!$config_exists) {
                continue;
            }

            /* Make sure the config file has the required props */
            if(!isset($config->plugin_id, $config->name, $config->description, $config->version, $config->url, $config->author, $config->author_url, $config->status, $config->avatar_style, $config->icon)) {
                continue;
            }

            if(!isset($config->actions)) {
                $config->actions = true;
            }

            /* Get the plugin status */
            if(file_exists($plugin_directory . 'settings.json')) {
                $settings_json = file_get_contents($plugin_directory . 'settings.json');

                if($settings_json !== false) {
                    $settings = json_decode($settings_json);

                    if(json_last_error() === JSON_ERROR_NONE && isset($settings->status)) {
                        $config->settings = $settings;
                        $config->status = $settings->status;
                    }
                }
            }

            /* Save the route to the plugin */
            $config->path = $plugin_directory;

            /* Save the plugin */
            $plugins[$config->plugin_id] = $config;
        }

        /* Cache the plugins registry */
        if(ALTUMCODE == 66 && is_writable(UPLOADS_PATH . 'cache/')) {
            file_put_contents($cache_path, '<?php return ' . var_export(json_decode(json_encode($plugins), true), true) . ';', LOCK_EX);
        }

        return $plugins;
    }

    public static function clear_cache() {
        $cache_path = UPLOADS_PATH . 'cache/plugins.php';

        if(is_file($cache_path)) {
            unlink($cache_path);
        }
    }

    public static function get($plugin_id) {
        return self::$plugins[$plugin_id] ?? null;
    }

    /* Plugin status = 1 */
    public static function is_active($plugin_id) {
        $plugin = self::get($plugin_id);

        return $plugin && ($plugin->status === 1 || $plugin->status == 'active');
    }

    /* Plugin status = 0 */
    public static function is_installed($plugin_id) {
        $plugin = self::get($plugin_id);

        return $plugin && ($plugin->status === 0 || $plugin->status == 'installed');
    }

    /* Plugin status = -1 */
    public static function is_uninstalled($plugin_id) {
        $plugin = self::get($plugin_id);

        return $plugin && ($plugin->status === -1 || $plugin->status == 'uninstalled');
    }

    /* Plugin status = -2 */
    public static function is_inexistent($plugin_id) {
        $plugin = self::get($plugin_id);

        return $plugin && ($plugin->status === -2 || $plugin->status == 'inexistent');
    }

    public static function save_status($plugin_id, $new_status) {
        $plugin = self::get($plugin_id);

        if(!$plugin) {
            return false;
        }

        /* Enable the plugin from the config file */
        $new_settings = isset($plugin->settings) ? clone $plugin->settings : (object) [];
        $new_settings->status = $new_status;

        /* Save the new config file */
        $settings_path = $plugin->path . 'settings.json';
        $settings_saved = file_put_contents($settings_path, json_encode($new_settings));

        if($settings_saved !== false) {
            chmod($settings_path, 0777);

            /* Clear the plugins cache */
            self::clear_cache();
        }

        return $settings_saved !== false;
    }

}
