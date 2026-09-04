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

defined('ALTUMCODE') || die();

class StatusPageCreate extends Controller {

    public function index() {

        if(!settings()->status_pages->status_pages_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.status_pages')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('status-pages');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `status_pages` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->status_pages_limit != -1 && $total_rows >= $this->user->plan_settings->status_pages_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('status-pages');
        }

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        /* Get all the available monitors */
        $monitors = (new \Altum\Models\Monitors())->get_monitors_by_user_id($this->user->user_id);

        /* Get all the available heartbeats */
        $heartbeats = (new \Altum\Models\Heartbeats())->get_heartbeats_by_user_id($this->user->user_id);

        if(!empty($_POST)) {
            $_POST['url'] = !empty($_POST['url']) && $this->user->plan_settings->custom_url_is_enabled ? get_slug(query_clean($_POST['url'])) : false;
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
            if($_POST['url']) {

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

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $timezone = $this->user->timezone;
                $theme = 'new-york';
                $monitors_ids = json_encode($_POST['monitors_ids']);
                $heartbeats_ids = json_encode($_POST['heartbeats_ids']);
                $socials = [];
                foreach(require APP_PATH . 'includes/s/socials.php' as $key => $value) {
                    $socials[$key] = '';
                }
                $socials = json_encode($socials);
                $settings = json_encode([
                    'title' => null,
                    'meta_description' => null,
                    'meta_keywords' => null,
                    'font_family' => 'default',
                    'font_size' => 16,
                    'display_share_buttons' => true,
                    'display_header_text' => true,
                    'auto_refresh' => 0,

                    'pwa_file_name' => null,
                    'pwa_is_enabled' => false,
                    'pwa_display_install_bar' => false,
                    'pwa_display_install_bar_delay' => 3,
                    'pwa_theme_color' => '#000000',
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
                $status_page_id = db()->insert('status_pages', [
                    'user_id' => $this->user->user_id,
                    'domain_id' => $_POST['domain_id'],
                    'monitors_ids' => $monitors_ids,
                    'heartbeats_ids' => $heartbeats_ids,
                    'url' => $_POST['url'],
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'settings' => $settings,
                    'timezone' => $timezone,
                    'socials' => $socials,
                    'theme' => $theme,
                    'is_se_visible' => (int) $this->user->plan_settings->search_engine_visibility_is_enabled,
                    'datetime' => get_date(),
                ]);

                /* Update custom domain if needed */
                if($_POST['is_main_status_page']) {
                    /* Database query */
                    db()->where('domain_id', $_POST['domain_id'])->update('domains', ['status_page_id' => $status_page_id, 'last_datetime' => get_date()]);

                    /* Clear the cache */
                    cache()->deleteItems([
                        'domains?user_id=' . $this->user->user_id,
                        'domain?domain_id=' . $_POST['domain_id'],
                        'domain?host=' . md5($domains[$_POST['domain_id']]->host ?? ''),
                    ]);
                    cache()->deleteItemsByTag('domains?user_id=' . $this->user->user_id);
                }

                /* Clear the cache */
                cache()->deleteItem('status_pages_dashboard?user_id=' . $this->user->user_id);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('status-page-update/' . $status_page_id);
            }

        }

        /* Set default values */
        $values = [
            'url' => $_POST['url'] ?? '',
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'domain_id' => $_POST['domain_id'] ?? '',
            'is_main_status_page' => $_POST['is_main_status_page'] ?? '',
            'monitors_ids' => $_POST['monitors_ids'] ?? [],
            'heartbeats_ids' => $_POST['heartbeats_ids'] ?? [],
        ];

        /* Prepare the view */
        $data = [
            'monitors' => $monitors,
            'heartbeats' => $heartbeats,
            'domains' => $domains,
            'values' => $values
        ];

        $view = new \Altum\View('status-page-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
