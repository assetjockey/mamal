<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
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

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Title;

defined('ALTUMCODE') || die();

class StatusPageUpdate extends Controller {

    public function index() {

        if(!settings()->status_pages->status_pages_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.status_pages')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('status-pages');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `status_pages` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->status_pages_limit != -1 && $total_rows > $this->user->plan_settings->status_pages_limit) {
            redirect('status-pages');
        }

        $status_page_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$status_page = db()->where('status_page_id', $status_page_id)->where('user_id', $this->user->user_id)->getOne('status_pages')) {
            redirect('status-pages');
        }

        /* Generate the status_page full URL base */
        $status_page->full_url = (new \Altum\Models\StatusPage())->get_status_page_full_url($status_page, $this->user);

        $status_page->socials = json_decode($status_page->socials ?? '');
        $status_page->settings = json_decode($status_page->settings ?? '');
        $status_page->monitors_ids = json_decode($status_page->monitors_ids ?? '[]');
        $status_page->heartbeats_ids = json_decode($status_page->heartbeats_ids ?? '[]');

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user, true, $status_page->status_page_id);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get all the available monitors */
        $monitors = (new \Altum\Models\Monitors())->get_monitors_by_user_id($this->user->user_id);

        /* Get all the available heartbeats */
        $heartbeats = (new \Altum\Models\Heartbeats())->get_heartbeats_by_user_id($this->user->user_id);

        if(!empty($_POST)) {
            $_POST['url'] = !empty($_POST['url']) ? get_slug(query_clean($_POST['url'])) : false;
            $_POST['name'] = mb_substr(trim(query_clean($_POST['name'])), 0, 256);
            $_POST['description'] = mb_substr(trim(query_clean($_POST['description'])), 0, 256);
            $_POST['domain_id'] = isset($_POST['domain_id']) && isset($domains[$_POST['domain_id']]) ? (!empty($_POST['domain_id']) ? (int) $_POST['domain_id'] : null) : null;
            $_POST['is_main_status_page'] = isset($_POST['is_main_status_page']) && isset($domains[$_POST['domain_id']]) && $domains[$_POST['domain_id']]->type == 0;

            $_POST['monitors_ids'] = empty($_POST['monitors_ids']) ? [] : array_map(
                'intval',
                array_filter($_POST['monitors_ids'], function($monitor_id) use($monitors) {
                    return array_key_exists($monitor_id, $monitors);
                })
            );

            $_POST['heartbeats_ids'] = empty($_POST['heartbeats_ids']) ? [] : array_map(
                'intval',
                array_filter($_POST['heartbeats_ids'], function($heartbeat_id) use($heartbeats) {
                    return array_key_exists($heartbeat_id, $heartbeats);
                })
            );

            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
            $_POST['timezone']  = in_array($_POST['timezone'], \DateTimeZone::listIdentifiers()) ? query_clean($_POST['timezone']) : settings()->main->default_timezone;
            $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
            $_POST['password'] = !empty($_POST['password']) ?
                ($_POST['password'] != $status_page->password ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $status_page->password)
                : null;
            $_POST['is_se_visible'] = $this->user->plan_settings->search_engine_visibility_is_enabled ? (int) isset($_POST['is_se_visible']) : 0;
            $_POST['is_removed_branding'] = (int) isset($_POST['is_removed_branding']);
            $_POST['title'] = query_clean($_POST['title'], 70);
            $_POST['meta_description'] = query_clean($_POST['meta_description'], 160);
            $_POST['meta_keywords'] = query_clean($_POST['meta_keywords'], 160);
            $_POST['language_code'] = array_key_exists($_POST['language_code'], get_locale_languages_array()) ? $_POST['language_code'] : \Altum\Language::$default_code;
            $_POST['custom_css'] = mb_substr(trim($_POST['custom_css']), 0, 10000);
            $_POST['custom_js'] = mb_substr(trim($_POST['custom_js']), 0, 10000);
            $fonts = require APP_PATH . 'includes/s/fonts.php';
            $_POST['font_family'] = array_key_exists($_POST['font_family'], $fonts) ? query_clean($_POST['font_family']) : false;
            $_POST['font_size'] = (int) $_POST['font_size'] < 14 || (int) $_POST['font_size'] > 22 ? 16 : (int) $_POST['font_size'];
            $_POST['display_share_buttons'] = (int) isset($_POST['display_share_buttons']);
            $_POST['display_header_text'] = (int) isset($_POST['display_header_text']);
            $_POST['auto_refresh'] = (int) $_POST['auto_refresh'] < 0 || (int) $_POST['auto_refresh'] > 60 ? 0 : (int) $_POST['auto_refresh'];
            $_POST['is_enabled'] = (int) isset($_POST['is_enabled']);

            $themes = require APP_PATH . 'includes/s/themes.php';
            $_POST['theme'] = array_key_exists($_POST['theme'], $themes) ? query_clean($_POST['theme']) : 'new-york';

            /* Make sure the socials sent are proper */
            $socials = require APP_PATH . 'includes/s/socials.php';

            foreach($_POST['socials'] as $key => $value) {
                if(!array_key_exists($key, $socials)) {
                    unset($_POST['socials'][$key]);
                } else {
                    $_POST['socials'][$key] = mb_substr(query_clean($_POST['socials'][$key]), 0, $socials[$key]['max_length']);
                }
            }

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!empty($_POST['url']) && in_array($_POST['url'], settings()->status_pages->blacklisted_keywords)) {
                Alerts::add_field_error('url', l('status_page.error_message.blacklisted_keyword'));
            }

            /* Check for duplicate url if needed */
            if(
                ($_POST['url'] && $this->user->plan_settings->custom_url_is_enabled && $_POST['url'] != $status_page->url)
                || ($status_page->domain_id != $_POST['domain_id'])
            ) {

                if(array_key_exists($_POST['url'], \Altum\Router::$routes['']) || in_array($_POST['url'], \Altum\Language::$active_languages) || file_exists(ROOT_PATH . $_POST['url'])) {
                    Alerts::add_field_error('url', l('status_page.error_message.blacklisted_url'));
                }

                $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
                $is_existing_status_page = database()->query("SELECT `status_page_id` FROM `status_pages` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;

                if($is_existing_status_page) {
                    Alerts::add_field_error('url', l('status_page.error_message.url_exists'));
                }

                if(!$_POST['is_main_status_page']) {
                    /* Make sure the custom url meets the requirements */
                    if(mb_strlen($_POST['url']) < ($this->user->plan_settings->url_minimum_characters ?? 1)) {
                        Alerts::add_field_error('url', sprintf(l('status_page.error_message.url_minimum_characters'), ($this->user->plan_settings->url_minimum_characters ?? 1)));
                    }

                    if(mb_strlen($_POST['url']) > ($this->user->plan_settings->url_maximum_characters ?? 64)) {
                        Alerts::add_field_error('url', sprintf(l('status_page.error_message.url_maximum_characters'), ($this->user->plan_settings->url_maximum_characters ?? 64)));
                    }
                }
            }

            /* Image uploads */
            $logo = \Altum\Uploads::process_upload($status_page->logo, 'status_pages_logos', 'logo', 'logo_remove', settings()->status_pages->logo_size_limit);
            $favicon = \Altum\Uploads::process_upload($status_page->favicon, 'status_pages_favicons', 'favicon', 'favicon_remove', settings()->status_pages->favicon_size_limit);
            $opengraph = \Altum\Uploads::process_upload($status_page->opengraph, 'status_pages_opengraph', 'opengraph', 'opengraph_remove', settings()->status_pages->opengraph_size_limit);
            $pwa_icon = \Altum\Uploads::process_upload($status_page->settings->pwa_icon, 'status_pages_pwa_icon', 'pwa_icon', 'pwa_icon_remove', settings()->status_pages->pwa_icon_size_limit);

            /* PWA generation */
            $_POST['pwa_is_enabled'] = (int) isset($_POST['pwa_is_enabled']);
            $_POST['pwa_display_install_bar'] = (int) isset($_POST['pwa_display_install_bar']);
            $_POST['pwa_display_install_bar_delay'] = max(1, (int) $_POST['pwa_display_install_bar_delay'] ?? 3);
            $_POST['pwa_theme_color'] = isset($_POST['pwa_theme_color']) && verify_hex_color($_POST['pwa_theme_color']) ? $_POST['pwa_theme_color'] : '#000000';

            if(\Altum\Plugin::is_active('pwa') && settings()->pwa->is_enabled && $this->user->plan_settings->custom_pwa_is_enabled && $_POST['pwa_is_enabled']) {
                $pwa_file_name = $status_page->settings->pwa_file_name ?? 'status-pages-' . md5(uniqid('', true) . random_bytes(16));

                $start_url = $_POST['domain_id'] ? $domains[$_POST['domain_id']]->scheme . $domains[$_POST['domain_id']]->host . '/' . ($_POST['is_main_status_page'] ? null : $_POST['url']) : SITE_URL . $_POST['url'];
                $scope_url = $start_url;

                /* Add UTM tracking params */
                $start_url = $start_url . '?' . http_build_query([
                        'utm_source' => 'pwa',
                        'utm_medium' => 'web-app',
                        'utm_campaign' => 'install-or-pwa-launch',
                    ]);

                /* Generate the manifest file */
                $manifest = pwa_generate_manifest([
                    'name' => $_POST['title'] ?: $_POST['url'] . ' - ' . settings()->main->title,
                    'short_name' => $_POST['url'],
                    'description' => $_POST['description'] ?: $_POST['meta_description'],
                    'theme_color' => $_POST['pwa_theme_color'],
                    'app_icon_url' => $pwa_icon ? \Altum\Uploads::get_full_url('status_pages_pwa_icon') . $pwa_icon : (settings()->pwa->app_icon ? \Altum\Uploads::get_full_url('app_icon') . settings()->pwa->app_icon : null),
                    'app_icon_maskable_url' => $pwa_icon ? \Altum\Uploads::get_full_url('status_pages_pwa_icon') . $pwa_icon : (settings()->pwa->app_icon_maskable ? \Altum\Uploads::get_full_url('app_icon') . settings()->pwa->app_icon_maskable : null),
                    'start_url' => $start_url,
                    'scope' => $scope_url,
                    'mobile_screenshots' => [],
                    'desktop_screenshots' => [],
                    'shortcuts' => [],
                ]);
                pwa_save_manifest($manifest, $pwa_file_name);
            }


            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $monitors_ids = json_encode($_POST['monitors_ids']);
                $heartbeats_ids = json_encode($_POST['heartbeats_ids']);
                $socials = json_encode($_POST['socials']);
                $settings = json_encode([
                    'title' => $_POST['title'],
                    'meta_description' => $_POST['meta_description'],
                    'meta_keywords' => $_POST['meta_keywords'],
                    'language_code' => $_POST['language_code'],
                    'font_family' => $_POST['font_family'],
                    'font_size' => $_POST['font_size'],
                    'display_share_buttons' => $_POST['display_share_buttons'],
                    'display_header_text' => $_POST['display_header_text'],
                    'auto_refresh' => $_POST['auto_refresh'],

                    'pwa_file_name' => $pwa_file_name ?? null,
                    'pwa_is_enabled' => $_POST['pwa_is_enabled'],
                    'pwa_icon' => $pwa_icon,
                    'pwa_display_install_bar' => $_POST['pwa_display_install_bar'],
                    'pwa_display_install_bar_delay' => $_POST['pwa_display_install_bar_delay'],
                    'pwa_theme_color' => $_POST['pwa_theme_color'],
                ]);

                if(!$_POST['url']) {
                    $is_existing_status_page = true;

                    /* Generate random url if not specified */
                    while($is_existing_status_page) {
                        $_POST['url'] = mb_strtolower(string_generate(settings()->status_pages->random_url_length ?? 7));

                        $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
                        $is_existing_status_page = database()->query("SELECT `status_page_id` FROM `status_pages` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;
                    }

                }

                /* Database query */
                db()->where('status_page_id', $status_page->status_page_id)->update('status_pages', [
                    'domain_id' => $_POST['domain_id'],
                    'monitors_ids' => $monitors_ids,
                    'heartbeats_ids' => $heartbeats_ids,
                    'url' => $_POST['url'],
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'settings' => $settings,
                    'project_id' => $_POST['project_id'],
                    'timezone' => $_POST['timezone'],
                    'password' => $_POST['password'],
                    'is_se_visible' => $_POST['is_se_visible'],
                    'is_removed_branding' => $_POST['is_removed_branding'],
                    'socials' => $socials,
                    'custom_css' => $_POST['custom_css'],
                    'custom_js' => $_POST['custom_js'],
                    'theme' => $_POST['theme'],
                    'logo' => $logo,
                    'favicon' => $favicon,
                    'opengraph' => $opengraph,
                    'is_enabled' => $_POST['is_enabled'],
                    'last_datetime' => get_date(),
                ]);

                /* Update custom domain if needed */
                if($_POST['is_main_status_page']) {

                    /* If the main status page of a particular domain is changing, update the old domain as well to "free" it */
                    if($_POST['domain_id'] != $status_page->domain_id) {
                        /* Database query */
                        db()->where('domain_id', $status_page->domain_id)->update('domains', [
                            'status_page_id' => null,
                            'last_datetime' => get_date(),
                        ]);
                    }

                    /* Database query */
                    db()->where('domain_id', $_POST['domain_id'])->update('domains', [
                        'status_page_id' => $status_page_id,
                        'last_datetime' => get_date(),
                    ]);

                    /* Clear the cache */
                    cache()->deleteItems([
                        'domains?user_id=' . $this->user->user_id,
                        'domain?domain_id=' . $status_page->domain_id,
                        'domain?domain_id=' . $_POST['domain_id'],
                        'domain?host=' . md5($domains[$status_page->domain_id]->host ?? ''),
                        'domain?host=' . md5($domains[$_POST['domain_id']]->host ?? ''),
                    ]);
                    cache()->deleteItemsByTag('domains?user_id=' . $this->user->user_id);
                }

                /* Update old main custom domain if needed */
                if(!$_POST['is_main_status_page'] && $status_page->domain_id && $domains[$status_page->domain_id]->status_page_id == $status_page->status_page_id) {
                    /* Database query */
                    db()->where('domain_id', $status_page->domain_id)->update('domains', [
                        'status_page_id' => null,
                        'last_datetime' => get_date(),
                    ]);

                    /* Clear the cache */
                    cache()->deleteItems([
                        'domains?user_id=' . $this->user->user_id,
                        'domain?domain_id=' . $status_page->domain_id,
                        'domain?domain_id=' . $_POST['domain_id'],
                        'domain?host=' . md5($domains[$status_page->domain_id]->host),
                        'domain?host=' . md5($domains[$_POST['domain_id']]->host),
                    ]);
                    cache()->deleteItemsByTag('domains?user_id=' . $this->user->user_id);
                }

                /* Clear the cache */
                cache()->deleteItemsByTag('status_page_id=' . $status_page_id);
                cache()->deleteItemsByTag('user_id=' . $this->user->user_id);
                cache()->deleteItem('status_pages_dashboard?user_id=' . $this->user->user_id);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('status-page-update/' . $status_page->status_page_id);
            }

        }

        /* Set a custom title */
        Title::set(sprintf(l('status_page_update.title'), $status_page->name));

        /* Prepare the view */
        $data = [
            'monitors' => $monitors,
            'heartbeats' => $heartbeats,
            'domains' => $domains,
            'projects' => $projects,
            'status_page' => $status_page
        ];

        $view = new \Altum\View('status-page-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
