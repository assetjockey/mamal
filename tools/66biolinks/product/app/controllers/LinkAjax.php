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
use Altum\Date;
use Altum\Models\BiolinksThemes;
use Altum\Models\Domain;
use Altum\Response;


defined('ALTUMCODE') || die();

class LinkAjax extends Controller {
    public $links_types = null;

    public function index() {
        \Altum\Authentication::guard();

        if(!empty($_POST) && (\Altum\Csrf::check('token') || \Altum\Csrf::check('global_token')) && isset($_POST['request_type'])) {

            $this->links_types = require APP_PATH . 'includes/links_types.php';

            switch($_POST['request_type']) {

                /* Status toggle */
                case 'is_enabled_toggle': $this->is_enabled_toggle(); break;

                /* Create */
                case 'create': $this->create(); break;

                /* Update */
                case 'update': $this->update(); break;

                /* Delete */
                case 'delete': $this->delete(); break;

                /* Duplicate */
                case 'duplicate': $this->duplicate(); break;

                /* Static AI improve */
                case 'static_ai_improve': $this->static_ai_improve(); break;

                /* Static AI restore */
                case 'static_ai_restore': $this->static_ai_restore(); break;

            }

        }

        die();
    }

    private function is_enabled_toggle() {
        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.links')) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        $_POST['link_id'] = (int) $_POST['link_id'];

        /* Get the current status */
        $link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links', ['link_id', 'domain_id', 'is_enabled', 'url', 'location_url', 'type']);

        if($link) {
            $new_is_enabled = (int) !$link->is_enabled;

            db()->where('link_id', $link->link_id)->update('links', ['is_enabled' => $new_is_enabled]);

            /* Get domain */
            $domain = (new Domain())->get_domain_by_domain_id($link->domain_id);

            /* Clear the cache */
            cache()->deleteItem('link?link_id=' . $_POST['link_id']);
            cache()->deleteItem('biolink_blocks?link_id=' . $_POST['link_id']);
            cache()->deleteItemsByTag('link_id=' . $_POST['link_id']);

            /* Send webhook notification if needed */
            if(settings()->webhooks->link_update) {
                fire_and_forget('post', settings()->webhooks->link_update, [
                    'user_id' => $this->user->user_id,
                    'link_id' => $link->link_id,
                    'domain_id' => $link->domain_id,
                    'url' => $link->url,
                    'location_url' => $link->location_url,
                    'full_url' => $link->domain_id ? $domain->scheme . $domain->host . '/' . ($domain->link_id == $link->link_id ? null : $link->url) : SITE_URL . $link->url,
                    'type' => 'link',
                    'datetime' => get_date(),
                ], signature: true);
            }

            Response::json(l('global.success_message.create2'), 'success');
        }
    }

    private function create() {
        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.links')) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        $_POST['type'] = trim(query_clean($_POST['type']));

        /* Check for possible errors */
        if(!array_key_exists($_POST['type'], $this->links_types)) {
            die();
        }

        $this->{'create_' . $_POST['type']}();

    }

    private function create_link() {
        if(!settings()->links->shortener_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        $_POST['location_url'] = get_url($_POST['location_url']);
        $_POST['url'] = !empty($_POST['url']) && $this->user->plan_settings->custom_url ? get_slug($_POST['url'], '-', false) : null;
        $_POST['sensitive_content'] = (int) isset($_POST['sensitive_content']);
        $type = 'link';

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Authentication::is_admin()) {
            Response::json(l('create_link_modal.error_message.main_domain_is_disabled'), 'error');
        }

        /* Check if custom domain is set */
        $domain_id = 0;
        $domains = [];

        if(isset($_POST['domain_id'])) {
            /* Get domains */
            $domains = (new Domain())->get_available_domains_by_user($this->user);

            if(isset($domains[$_POST['domain_id']])) {
                $domain_id = $domains[$_POST['domain_id']]->domain_id;
            }
        }

        if(empty($_POST['location_url'])) {
            Response::json(l('global.error_message.empty_fields'), 'error');
        }

        $this->check_url($_POST['url']);

        $this->check_location_url($_POST['location_url']);

        /* Check for the plan limit */
        $user_total_links = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} AND `type` = 'link'")->fetch_object()->total;
        if($this->user->plan_settings->links_limit != -1 && $user_total_links >= $this->user->plan_settings->links_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Check for duplicate url if needed */
        if($_POST['url']) {
            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(l('link.error_message.url_exists'), 'error');
            }

            $url = $_POST['url'];
        } else {
            $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));

            /* Generate random url if not specified */
            while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
            }
        }

        $app_linking = [
            'ios_location_url' => null,
            'android_location_url' => null,
            'app' => null,
        ];

        $supported_apps = require APP_PATH . 'includes/app_linking.php';
        $app_linking_location_url = $_POST['location_url'];

        foreach($supported_apps as $app_key => $app) {
            foreach($app['formats'] as $format => $targets) {

                if(preg_match('~' . $targets['regex'] . '~', $app_linking_location_url, $match)) {

                    /* Extract and normalize hostnames */
                    $user_host = parse_url($app_linking_location_url, PHP_URL_HOST);
                    $format_host = parse_url('https://' . str_replace('%s', 'placeholder', $format), PHP_URL_HOST);

                    /* Remove www. and m. prefixes for more flexible comparison */
                    $user_host = preg_replace('/^(www\.|m\.)/', '', $user_host);
                    $format_host = preg_replace('/^(www\.|m\.)/', '', $format_host);

                    /* Compare the normalized hosts */
                    if($user_host === $format_host) {

                        if(count($match) > 1) {
                            array_shift($match);
                            $app_linking['ios_location_url'] = vsprintf($targets['iOS'], $match);
                            $app_linking['android_location_url'] = vsprintf($targets['Android'], $match);
                            $app_linking['app'] = $app_key;
                        }

                        break 2;
                    }
                }

            }
        }

        $settings = json_encode([
            'http_status_code' => 301,
            'clicks_limit' => null,
            'expiration_url' => null,
            'password' => null,
            'sensitive_content' => false,
            'targeting_type' => null,
            'app_linking_is_enabled' => $this->user->plan_settings->app_linking_is_enabled,
            'app_linking' => $app_linking,
            'cloaking_is_enabled' => false,
            'cloaking_title' => null,
            'cloaking_meta_description' => null,
            'cloaking_custom_js' => null,
            'cloaking_favicon' => null,
            'cloaking_opengraph' => null,
            'forward_query_parameters_is_enabled' => false,
            'utm' => [
                'source' => null,
                'medium' => null,
                'campaign' => null,
            ],
            'seo' => [
                'block' => !$this->user->plan_settings->seo,
            ],
        ]);

        /* Unique access token */
        $token = bin2hex(random_bytes(16));

        /* Insert to database */
        $link_id = db()->insert('links', [
            'user_id' => $this->user->user_id,
            'domain_id' => $domain_id,
            'token' => $token,
            'type' => $type,
            'url' => $url,
            'location_url' => $_POST['location_url'],
            'settings' => $settings,
            'datetime' => get_date(),
            'email_reports_last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem($type . '_links_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('links_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->link_new) {
            fire_and_forget('post', settings()->webhooks->link_new, [
                'user_id' => $this->user->user_id,
                'link_id' => $link_id,
                'domain_id' => $domain_id,
                'url' => $url,
                'location_url' => $_POST['location_url'],
                'full_url' => $domain_id ? $domains[$domain_id]->url . $url : SITE_URL . $url,
                'type' => $type,
                'datetime' => get_date(),
            ], signature: true);
        }

        Response::json(l('global.success_message.create2'), 'success', ['url' => url('link/' . $link_id . ($this->user->preferences->links_auto_copy_link ? '?auto_copy_link=true' : ''))]);
    }

    private function create_biolink() {
        if(!settings()->links->biolinks_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        $_POST['url'] = !empty($_POST['url']) && $this->user->plan_settings->custom_url ? get_slug($_POST['url'], '-', false) : null;
        $_POST['biolink_template_id'] = isset($_POST['biolink_template_id']) && in_array($_POST['biolink_template_id'], $this->user->plan_settings->biolinks_templates ?? []) ? (int) $_POST['biolink_template_id'] : null;

        /* Check for a default template id */
        if(!$_POST['biolink_template_id'] && settings()->links->default_biolink_template_id) {
            $_POST['biolink_template_id'] = settings()->links->default_biolink_template_id;
        }

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Authentication::is_admin()) {
            Response::json(l('create_link_modal.error_message.main_domain_is_disabled'), 'error');
        }

        /* Check if custom domain is set */
        $domain_id = 0;
        $domains = [];

        if(isset($_POST['domain_id'])) {

            /* Get domains */
            $domains = (new Domain())->get_available_domains_by_user($this->user);

            if(isset($domains[$_POST['domain_id']])) {
                $domain_id = $domains[$_POST['domain_id']]->domain_id;
            }
        }

        /* Check for the plan limit */
        $user_total_biolinks = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} AND `type` = 'biolink'")->fetch_object()->total;
        if($this->user->plan_settings->biolinks_limit != -1 && $user_total_biolinks >= $this->user->plan_settings->biolinks_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Check for duplicate url if needed */
        if($_POST['url']) {
            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(l('link.error_message.url_exists'), 'error');
            }
        }

        /* Start the creation process */
        $url = $_POST['url'] ? $_POST['url'] : mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
        $type = 'biolink';
        $settings = [
            'service_worker' => null,

            'pwa_file_name' => null,
            'pwa_is_enabled' => false,
            'pwa_display_install_bar' => false,
            'pwa_display_install_bar_delay' => 3,
            'pwa_theme_color' => '#000000',
            'pwa_icon' => null,

            'branded_button_is_enabled' => false,
            'branded_button_icon' => null,
            'branded_button_title' => null,
            'branded_button_content' => null,

            'verified_location' => 'top',
            'favicon' => null,
            'background_type' => 'preset',
            'background' => 'zero',
            'background_attachment' => 'scroll',
            'background_blur' => 0,
            'background_brightness' => 100,
            'text_color' => '#ffffff',
            'display_branding' => true,
            'branding' => [
                'url' => '',
                'name' => ''
            ],
            'seo' => [
                'block' => !$this->user->plan_settings->seo,
                'title' => '',
                'meta_description' => '',
                'meta_keywords' => '',
                'image' => '',
            ],
            'utm' => [
                'medium' => '',
                'source' => '',
            ],
            'font' => 'default',
            'font_size' => 16,
            'width' => 8,
            'block_spacing' => 2,
            'hover_animation' => 'smooth',
            'password' => null,
            'sensitive_content' => false,
            'leap_link' => null,
            'custom_css' => null,
            'custom_js' => null,
            'share_is_enabled' => true,
            'scroll_buttons_is_enabled' => true,
            'language_code' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : \Altum\Language::$default_code,
        ];

        /* Generate random url if not specified */
        while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
            $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
        }

        $this->check_url($_POST['url']);

        $additional = null;
        $biolink_theme_id = null;

        /* Check for biolink templates */
        if($_POST['biolink_template_id']) {
            $biolinks_templates = (new \Altum\Models\BiolinksTemplates())->get_biolinks_templates();

            if(array_key_exists($_POST['biolink_template_id'], $biolinks_templates)) {
                $biolink_template = $biolinks_templates[$_POST['biolink_template_id']];

                /* Get the details of the biolink page */
                $biolink = db()->where('link_id', $biolink_template->link_id)->getOne('links');

                if($biolink) {
                    /* Get all the biolink blocks as well */
                    $biolink->settings = json_decode($biolink->settings ?? '');
					$biolink->settings->seo->image = \Altum\Uploads::copy_uploaded_file($biolink->settings->seo->image, 'block_images/', 'block_images/', 'json_error');
					$biolink->settings->password_seo_image = \Altum\Uploads::copy_uploaded_file($biolink->settings->password_seo_image, 'block_images/', 'block_images/', 'json_error');
                    $biolink->settings->favicon = \Altum\Uploads::copy_uploaded_file($biolink->settings->favicon, 'favicons/', 'favicons/', 'json_error');
                    if($biolink->settings->background_type == 'image') $biolink->settings->background = \Altum\Uploads::copy_uploaded_file($biolink->settings->background, 'backgrounds/', 'backgrounds/', 'json_error');
                    $biolink->settings->pwa_is_enabled = false;
                    $biolink->settings->pwa_icon = null;
                    $biolink->settings->branded_button_icon = null;
                    $biolink->settings->service_worker = null;
                    $additional = $biolink->additional;
                    $biolink_theme_id = $biolink->biolink_theme_id;

                    /* Overwrite default settings with the settings of the template */
                    $settings = $biolink->settings;

                    /* Database query */
                    db()->where('biolink_template_id', $biolink_template->biolink_template_id)->update('biolinks_templates', [
                        'total_usage' => db()->inc()
                    ]);

                }
            }
        }

        /* Check for a default theme id */
        if(!$_POST['biolink_template_id'] && settings()->links->default_biolink_theme_id) {
            $biolink_theme_id = settings()->links->default_biolink_theme_id;

            /* Get available themes */
            $biolinks_themes = (new BiolinksThemes())->get_biolinks_themes();
            $biolink_theme_id = isset($biolink_theme_id) && array_key_exists($biolink_theme_id, $biolinks_themes) ? $biolink_theme_id : null;

            if($biolink_theme_id) {
                $biolink_theme = $biolinks_themes[$biolink_theme_id];

                /* Save settings for biolink page */
                $settings = array_merge($settings, (array) $biolink_theme->settings->biolink);

                /* Save the additional settings */
                $additional = json_encode($biolink_theme->settings->additional);
            }
        }

        $settings = json_encode($settings);

        /* Unique access token */
        $token = bin2hex(random_bytes(16));

        /* Insert to database */
        $link_id = db()->insert('links', [
            'user_id' => $this->user->user_id,
            'domain_id' => $domain_id,
            'token' => $token,
            'biolink_theme_id' => $biolink_theme_id ?? null,
            'type' => $type,
            'url' => $url,
            'settings' => $settings,
            'additional' => $additional,
            'datetime' => get_date(),
        ]);

        /* Check for a template usage */
        if(isset($biolink_template)) {
            /* Get all biolink blocks if needed */
            $biolink_blocks = db()->where('link_id', $biolink_template->link_id)->get('biolinks_blocks');

            foreach($biolink_blocks as $biolink_block) {
                $biolink_block->settings = json_decode($biolink_block->settings ?? '');

                if(is_array($biolink_block->settings)) {
                    $biolink_block->settings = (object) $biolink_block->settings;
                }

                /* Duplication of resources */
                switch($biolink_block->type) {
                    case 'file':
                    case 'audio':
                    case 'video':
                    case 'pdf_document':
                    case 'powerpoint_presentation':
                    case 'excel_spreadsheet':
                        $biolink_block->settings->file = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->file, \Altum\Uploads::get_path('files'), \Altum\Uploads::get_path('files'), 'json_error');
                        if(in_array($biolink_block->type, ['file', 'pdf_document', 'powerpoint_presentation', 'excel_spreadsheet'])) {
                            $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image ?? null, \Altum\Uploads::get_path('block_thumbnail_images'), \Altum\Uploads::get_path('block_thumbnail_images'), 'json_error');
                        }
                        break;

                    case 'review':
                    case 'featured_link':
                        $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image, \Altum\Uploads::get_path('block_images'), \Altum\Uploads::get_path('block_images'), 'json_error');
                        break;

                    case 'avatar':
                        $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image, 'avatars/', 'avatars/', 'json_error');
                        break;

                    case 'header':
                        $biolink_block->settings->avatar = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->avatar, 'avatars/', 'avatars/', 'json_error');
                        $biolink_block->settings->background = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->background, 'backgrounds/', 'backgrounds/', 'json_error');
                        $biolink_block->settings->video_file = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->video_file, 'files/', 'files/', 'json_error');
                        break;

                    case 'vcard':
                        $biolink_block->settings->vcard_avatar = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->vcard_avatar, 'avatars/', 'avatars/', 'json_error');
                        $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image, 'block_thumbnail_images/', 'block_thumbnail_images/', 'json_error');
                        break;

                    case 'product':
                        $biolink_block->settings->file = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->file, \Altum\Uploads::get_path('products_files'), \Altum\Uploads::get_path('products_files'), 'json_error');
                        $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image, 'block_thumbnail_images/', 'block_thumbnail_images/', 'json_error');
                        break;

                    case 'image':
                    case 'image_grid':
                        $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image, 'block_images/', 'block_images/', 'json_error');
                        break;

                    case 'image_comparison':
                        $biolink_block->settings->before_image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->before_image, 'block_images/', 'block_images/', 'json_error');
                        $biolink_block->settings->after_image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->after_image, 'block_images/', 'block_images/', 'json_error');
                        break;

                    case 'heading':
                        $biolink_block->settings->verified_location = '';
                        break;

                    case 'image_slider':

                        $biolink_block->settings->items = (array) $biolink_block->settings->items;

                        foreach($biolink_block->settings->items as $key => $item) {
                            $biolink_block->settings->items[$key]->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->items[$key]->image, 'block_images/', 'block_images/', 'json_error');
                        }

                        break;

                    default:
                        $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image, 'block_thumbnail_images/', 'block_thumbnail_images/', 'json_error');
                        break;
                }

                /* Database query */
                db()->insert('biolinks_blocks', [
                    'user_id' => $this->user->user_id,
                    'link_id' => $link_id,
                    'type' => $biolink_block->type,
                    'location_url' => $biolink_block->location_url,
                    'settings' => json_encode($biolink_block->settings),
                    'order' => $biolink_block->order,
                    'start_date' => $biolink_block->start_date,
                    'end_date' => $biolink_block->end_date,
                    'is_enabled' => $biolink_block->is_enabled,
                    'datetime' => get_date(),
                ]);
            }
        }

        /* Clear the cache */
        cache()->deleteItem($type . '_links_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('links_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->link_new) {
            fire_and_forget('post', settings()->webhooks->link_new, [
                'user_id' => $this->user->user_id,
                'link_id' => $link_id,
                'domain_id' => $domain_id,
                'url' => $url,
                'full_url' => $domain_id ? $domains[$domain_id]->url . $url : SITE_URL . $url,
                'type' => $type,
                'datetime' => get_date(),
            ], signature: true);
        }

        Response::json(l('global.success_message.create2'), 'success', ['url' => url('link/' . $link_id . ($this->user->preferences->links_auto_copy_link ? '?auto_copy_link=true' : ''))]);
    }

    private function create_file() {
        if(!settings()->links->files_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        $_POST['url'] = !empty($_POST['url']) && $this->user->plan_settings->custom_url ? get_slug($_POST['url'], '-', false) : null;

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Authentication::is_admin()) {
            Response::json(l('create_link_modal.error_message.main_domain_is_disabled'), 'error');
        }

        /* Check for the plan limit */
        $user_total_files = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} AND `type` = 'file'")->fetch_object()->total;
        if($this->user->plan_settings->files_limit != -1 && $user_total_files >= $this->user->plan_settings->files_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Check if custom domain is set */
        $domain_id = 0;
        $domains = [];

        if(isset($_POST['domain_id'])) {
            /* Get domains */
            $domains = (new Domain())->get_available_domains_by_user($this->user);

            if(isset($domains[$_POST['domain_id']])) {
                $domain_id = $domains[$_POST['domain_id']]->domain_id;
            }
        }

        /* File upload */
        $db_file = \Altum\Uploads::process_upload(null, 'files', 'file', 'file_remove', settings()->links->file_size_limit, 'json_error');

        /* Check for duplicate url if needed */
        if($_POST['url']) {
            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(l('link.error_message.url_exists'), 'error');
            }
        }

        /* Start the creation process */
        $url = $_POST['url'] ?? mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
        $type = 'file';
        $settings = json_encode([
            'file' => $db_file,
            'force_download_is_enabled' => false,
            'password' => null,
            'sensitive_content' => false,
            'clicks_limit' => null,
            'expiration_url' => null,
            'seo' => [
                'block' => !$this->user->plan_settings->seo,
            ],
        ]);

        /* Generate random url if not specified */
        while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
            $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
        }

        $this->check_url($_POST['url']);

        /* Unique access token */
        $token = bin2hex(random_bytes(16));

        /* Insert to database */
        $link_id = db()->insert('links', [
            'user_id' => $this->user->user_id,
            'domain_id' => $domain_id,
            'token' => $token,
            'type' => $type,
            'url' => $url,
            'settings' => $settings,
            'datetime' => get_date(),
            'email_reports_last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem($type . '_links_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('links_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->link_new) {
            fire_and_forget('post', settings()->webhooks->link_new, [
                'user_id' => $this->user->user_id,
                'link_id' => $link_id,
                'domain_id' => $domain_id,
                'url' => $url,
                'full_url' => $domain_id ? $domains[$domain_id]->url . $url : SITE_URL . $url,
                'type' => $type,
                'datetime' => get_date(),
            ], signature: true);
        }

        Response::json(l('global.success_message.create2'), 'success', ['url' => url('link/' . $link_id . ($this->user->preferences->links_auto_copy_link ? '?auto_copy_link=true' : ''))]);
    }

    private function create_vcard() {
        if(!settings()->links->vcards_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        $_POST['url'] = !empty($_POST['url']) && $this->user->plan_settings->custom_url ? get_slug($_POST['url'], '-', false) : null;

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Authentication::is_admin()) {
            Response::json(l('create_link_modal.error_message.main_domain_is_disabled'), 'error');
        }

        /* Check if custom domain is set */
        $domain_id = 0;
        $domains = [];

        if(isset($_POST['domain_id'])) {
            /* Get domains */
            $domains = (new Domain())->get_available_domains_by_user($this->user);

            if(isset($domains[$_POST['domain_id']])) {
                $domain_id = $domains[$_POST['domain_id']]->domain_id;
            }
        }

        /* Check for the plan limit */
        $user_total_vcards = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} AND `type` = 'vcard'")->fetch_object()->total;
        if($this->user->plan_settings->vcards_limit != -1 && $user_total_vcards >= $this->user->plan_settings->vcards_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Check for duplicate url if needed */
        if($_POST['url']) {
            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(l('link.error_message.url_exists'), 'error');
            }
        }

        /* Start the creation process */
        $url = $_POST['url'] ?? mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
        $type = 'vcard';
        $settings = json_encode([
            'password' => null,
            'sensitive_content' => false,
            'clicks_limit' => null,
            'expiration_url' => null,
            'vcard_avatar' => null,
            'vcard_first_name' => null,
            'vcard_last_name' => null,
            'vcard_email' => null,
            'vcard_url' => null,
            'vcard_company' => null,
            'vcard_job_title' => null,
            'vcard_birthday' => null,
            'vcard_street' => null,
            'vcard_city' => null,
            'vcard_zip' => null,
            'vcard_region' => null,
            'vcard_country' => null,
            'vcard_note' => null,
            'vcard_socials' => [],
            'vcard_phone_numbers' => [],
            'seo' => [
                'block' => !$this->user->plan_settings->seo,
            ],
        ]);

        /* Generate random url if not specified */
        while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
            $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
        }

        $this->check_url($_POST['url']);

        /* Unique access token */
        $token = bin2hex(random_bytes(16));

        /* Insert to database */
        $link_id = db()->insert('links', [
            'user_id' => $this->user->user_id,
            'domain_id' => $domain_id,
            'token' => $token,
            'type' => $type,
            'url' => $url,
            'settings' => $settings,
            'datetime' => get_date(),
            'email_reports_last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem($type . '_links_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('links_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->link_new) {
            fire_and_forget('post', settings()->webhooks->link_new, [
                'user_id' => $this->user->user_id,
                'link_id' => $link_id,
                'domain_id' => $domain_id,
                'url' => $url,
                'full_url' => $domain_id ? $domains[$domain_id]->url . $url : SITE_URL . $url,
                'type' => $type,
                'datetime' => get_date(),
            ], signature: true);
        }

        Response::json(l('global.success_message.create2'), 'success', ['url' => url('link/' . $link_id . ($this->user->preferences->links_auto_copy_link ? '?auto_copy_link=true' : ''))]);
    }

    private function create_event() {
        if(!settings()->links->events_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        $_POST['url'] = !empty($_POST['url']) && $this->user->plan_settings->custom_url ? get_slug($_POST['url'], '-', false) : null;

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Authentication::is_admin()) {
            Response::json(l('create_link_modal.error_message.main_domain_is_disabled'), 'error');
        }

        /* Check if custom domain is set */
        $domain_id = 0;
        $domains = [];

        if(isset($_POST['domain_id'])) {
            /* Get domains */
            $domains = (new Domain())->get_available_domains_by_user($this->user);

            if(isset($domains[$_POST['domain_id']])) {
                $domain_id = $domains[$_POST['domain_id']]->domain_id;
            }
        }

        /* Check for the plan limit */
        $user_total_events = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} AND `type` = 'event'")->fetch_object()->total;
        if($this->user->plan_settings->events_limit != -1 && $user_total_events >= $this->user->plan_settings->events_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Check for duplicate url if needed */
        if($_POST['url']) {
            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(l('link.error_message.url_exists'), 'error');
            }
        }

        /* Start the creation process */
        $url = $_POST['url'] ?? mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
        $type = 'event';
        $settings = json_encode([
            'password' => null,
            'sensitive_content' => false,
            'clicks_limit' => null,
            'expiration_url' => null,
            'event_name' => null,
            'event_note' => null,
            'event_url' => null,
            'event_location' => null,
            'event_start_datetime' => null,
            'event_end_datetime' => null,
            'event_first_alert_datetime' => null,
            'event_second_alert_datetime' => null,
            'event_timezone' => $this->user->timezone,
            'seo' => [
                'block' => !$this->user->plan_settings->seo,
            ],
        ]);

        /* Generate random url if not specified */
        while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
            $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
        }

        $this->check_url($_POST['url']);

        /* Unique access token */
        $token = bin2hex(random_bytes(16));

        /* Insert to database */
        $link_id = db()->insert('links', [
            'user_id' => $this->user->user_id,
            'domain_id' => $domain_id,
            'token' => $token,
            'type' => $type,
            'url' => $url,
            'settings' => $settings,
            'datetime' => get_date(),
            'email_reports_last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem($type . '_links_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('links_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->link_new) {
            fire_and_forget('post', settings()->webhooks->link_new, [
                'user_id' => $this->user->user_id,
                'link_id' => $link_id,
                'domain_id' => $domain_id,
                'url' => $url,
                'full_url' => $domain_id ? $domains[$domain_id]->url . $url : SITE_URL . $url,
                'type' => $type,
                'datetime' => get_date(),
            ], signature: true);
        }

        Response::json(l('global.success_message.create2'), 'success', ['url' => url('link/' . $link_id . ($this->user->preferences->links_auto_copy_link ? '?auto_copy_link=true' : ''))]);
    }

    private function create_static() {
        /* Make sure feature is enabled */
        if(!settings()->links->static_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        /* Sanitize custom URL if provided */
        $_POST['url'] = !empty($_POST['url']) && $this->user->plan_settings->custom_url ? get_slug($_POST['url'], '-', false) : null;
        $mode = !empty($_FILES['file']['name']) ? 'file' : 'ai';

        /* Make sure the AI feature is enabled */
        if($mode == 'ai' && !settings()->links->static_ai_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        if($mode == 'ai') {
            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Response::json('This feature is locked on the demo.', 'error');
        }

        /* Check main domain availability */
        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Authentication::is_admin()) {
            Response::json(l('create_link_modal.error_message.main_domain_is_disabled'), 'error');
        }

        /* Enforce user static files limit */
        $user_total_files = database()->query("
			SELECT COUNT(*) AS `total` 
			FROM `links` 
			WHERE `user_id` = {$this->user->user_id} 
			AND `type` = 'static'
		")->fetch_object()->total;

        if($this->user->plan_settings->static_limit != -1 && $user_total_files >= $this->user->plan_settings->static_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Check if custom domain is set */
        $domain_id = 0;
        $domains = [];

        if(isset($_POST['domain_id'])) {
            /* Get domains */
            $domains = (new Domain())->get_available_domains_by_user($this->user);

            if(isset($domains[$_POST['domain_id']])) {
                $domain_id = $domains[$_POST['domain_id']]->domain_id;
            }
        }

        /* Check duplicate URL */
        if($_POST['url']) {
            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(l('link.error_message.url_exists'), 'error');
            }
        }

        /* Cap of amount of files */
        $maximum_files_amount = 1000;

        /* Create target folder */
        $static_folder_name = md5(uniqid('', true) . random_bytes(16));
        $base_folder = \Altum\Uploads::get_full_path('static') . $static_folder_name;
        mkdir($base_folder, 0777, true);

        /* Prepare settings storage */
        $settings = [
            'schedule' => false,
            'clicks_limit' => null,
            'expiration_url' => null,
            'password' => null,
            'sensitive_content' => false,
            'seo' => [
                'block' => !$this->user->plan_settings->seo,
            ]
        ];

        /* Track files and folders */
        $files = [];
        $folders = [];

        /* File upload mode */
        if($mode == 'file') {
            /* Handle file upload */
            if (!empty($_FILES['file']['name'])) {
                $file_extension = mb_strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                $file_temp = $_FILES['file']['tmp_name'];

                /* Increase execution limits for large files */
                set_time_limit(120);

                /* Upload error checks */
                if ($_FILES['file']['error'] == UPLOAD_ERR_INI_SIZE) {
                    Response::json(sprintf(l('global.error_message.file_size_limit'), get_max_upload()), 'error');
                }
                if ($_FILES['file']['error'] && $_FILES['file']['error'] != UPLOAD_ERR_INI_SIZE) {
                    Response::json(l('global.error_message.file_upload'), 'error');
                }

                /* Validate file type */
                if (!in_array($file_extension, \Altum\Uploads::get_whitelisted_file_extensions('static'))) {
                    Response::json(l('global.error_message.invalid_file_type'), 'error');
                }

                /* Validate permissions */
                if (!\Altum\Plugin::is_active('offload') || (\Altum\Plugin::is_active('offload') && !settings()->offload->uploads_url)) {
                    if (!is_writable(UPLOADS_PATH . \Altum\Uploads::get_path('static'))) {
                        Response::json(sprintf(l('global.error_message.directory_not_writable'), UPLOADS_PATH . \Altum\Uploads::get_path('static')), 'error');
                    }
                }

                /* Validate max file size */
                if (settings()->links->static_size_limit && $_FILES['file']['size'] > settings()->links->static_size_limit * 1000000) {
                    Response::json(sprintf(l('global.error_message.file_size_limit'), settings()->links->static_size_limit), 'error');
                }
            }

            /* single html upload */
            if ($file_extension == 'html') {
                move_uploaded_file($file_temp, $base_folder . '/index.html');
                $files[] = 'index.html';
            }

            /* zip archive extraction */
            if ($file_extension == 'zip') {
                $zip = new \ZipArchive;

                if ($zip->open($file_temp) === true) {
                    /* Too many files */
                    if($zip->numFiles > $maximum_files_amount) {
                        $zip->close();
                        Response::json(l('global.error_message.basic'), 'error');
                    }

                    /* Create folders */
                    $created_folders = [];
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $entry_name = $zip->getNameIndex($i);
                        $entry_info = $zip->statIndex($i);

                        if ($entry_info['name'][strlen($entry_info['name']) - 1] == '/' && !str_contains($entry_info['name'], '__MACOSX')) {
                            $folder_path = $base_folder . '/' . $entry_info['name'];
                            if (!in_array($folder_path, $created_folders)) {
                                mkdir($folder_path, 0777, true);
                                $created_folders[] = $folder_path;
                                $folders[] = $entry_info['name'];
                            }
                        }
                    }

                    /* Secure against zip slip */
                    $real_base_folder = realpath($base_folder);

                    /* Extract files */
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $entry_name = $zip->getNameIndex($i);
                        $entry_info = $zip->statIndex($i);
                        $entry_extension = mb_strtolower(pathinfo($entry_name, PATHINFO_EXTENSION));

                        /* Skip folders & __MACOSX junk */
                        if ($entry_info['name'][strlen($entry_info['name']) - 1] == '/' || str_contains($entry_info['name'], '__MACOSX')) {
                            continue;
                        }

                        /* Only allow whitelisted file types */
                        if (in_array($entry_extension, \Altum\Uploads::$uploads['static']['inside_zip_whitelisted_file_extensions'])) {

                            $target_file = $base_folder . '/' . $entry_info['name'];
                            $real_target_file = realpath(dirname($target_file));

                            /* Prevent zip slip attack */
                            if (strpos($real_target_file, $real_base_folder) !== 0) {
                                $zip->close();
                                Response::json(l('global.error_message.basic'), 'error');
                            }

                            /* Extract file */
                            copy('zip://' . $file_temp . '#' . $entry_name, $target_file);
                            $files[] = $entry_info['name'];
                        }

                        /* Flush occasionally to keep response alive */
                        if ($i % 100 == 0) {
                            flush();
                            ob_flush();
                        }
                    }

                    $zip->close();

                    /* Remove temp file */
                    unlink($file_temp);
                } else {
                    Response::json(l('global.error_message.basic'), 'error');
                }
            }
        }

        /* File upload mode */
        if($mode == 'ai') {
            /* Check for the plan limit */
            $ai_static_prompts_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`ai_static_prompts_current_month`');
            if($this->user->plan_settings->ai_static_prompts_per_month_limit != -1 && $ai_static_prompts_current_month >= $this->user->plan_settings->ai_static_prompts_per_month_limit) {
                Response::json(l('global.info_message.plan_feature_limit'), 'error');
            }

            /* Prompt limit */
            if($this->user->plan_settings->ai_static_prompts_characters_limit != -1) {
                $_POST['input'] = mb_substr($_POST['input'], 0, $this->user->plan_settings->ai_static_prompts_characters_limit);
            }

            /* AI static site generation */
            $allowed_extensions = \Altum\Uploads::$uploads['static']['inside_zip_whitelisted_file_extensions'];
            $max_total_bytes = settings()->links->static_size_limit ? (int) settings()->links->static_size_limit * 1000000 : null;
            $total_bytes = 0;

            set_time_limit(600);

            session_write_close();

            \Unirest\Request::timeout(600);
            \Unirest\Request::verifyPeer(true);

            try {
                $response = \Unirest\Request::post(
                    settings()->links->openai_api_url . 'v1/responses',
                    [
                        'Authorization' => 'Bearer ' . settings()->links->openai_api_key,
                        'Content-Type' => 'application/json'
                    ],
                    json_encode([
                        'model' => settings()->links->openai_model,
                        'text' => [
                            'format' => [
                                'type' => 'json_schema',
                                'name' => 'static_site_manifest',
                                'strict' => true,

                                'schema' => [
                                    'type' => 'object',
                                    'additionalProperties' => false,
                                    'required' => ['site_name', 'files'],
                                    'properties' => [
                                        'site_name' => ['type' => 'string'],
                                        'files' => [
                                            'type' => 'array',
                                            'minItems' => 1,
                                            'items' => [
                                                'type' => 'object',
                                                'additionalProperties' => false,
                                                'required' => ['path', 'content'],
                                                'properties' => [
                                                    'path' => ['type' => 'string'],
                                                    'content' => ['type' => 'string']
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'input' => [
                            [
                                'role' => 'developer',
                                'content' => [[
                                    'type' => 'input_text',
                                    'text' => 'You generate a static website as a JSON file manifest only.

Hard rules:
- No README files. No docs/instructions/license/changelog/notes files.
- No code comments anywhere (no HTML/CSS/JS comments).
- Must include "index.html".
- All file paths must be relative, no leading "/" or "\" and must not contain "..".
- Only use allowed extensions for static hosting.
- index.html must reference local files using relative paths starting with "./" (example: <link rel="stylesheet" href="./css/styles.css">).
- No external assets/CDNs unless user explicitly asks.
- Browser-safe code only (no eval, no new Function, no document.write, no inline event handlers like onclick="").
- Every referenced local asset must exist in the manifest.'
                                ]]
                            ],
                            [
                                'role' => 'user',
                                'content' => [[
                                    'type' => 'input_text',
                                    'text' => (string) $_POST['input']
                                ]]
                            ]
                        ]
                    ])
                );
            } catch (\Exception $exception) {
                error_log('[AI STATIC ERROR]: ' . $exception->getMessage());
                Response::json(l('global.error_message.basic'), 'error');
            }

            if($response->code >= 400) {
                error_log('[AI STATIC ERROR]: ' . $response->raw_body);
                Response::json(l('global.error_message.basic'), 'error');
            }

            /* Extract AI output JSON */
            /* get response */
            $ai_output = null;

            foreach($response->body->output as $item) {
                if (($item->type ?? null) !== 'message') {
                    continue;
                }

                foreach ($item->content ?? [] as $content) {
                    if (($content->type ?? null) === 'output_text') {
                        $ai_output = $content->text;
                    }
                }
            }

            if(!$ai_output) {
                error_log('[AI STATIC ERROR]: Missing output_text.');
                Response::json(l('global.error_message.basic'), 'error');
            }

            /* Parse manifest */
            $manifest = json_decode($ai_output, true);

            if(!is_array($manifest) || !isset($manifest['files']) || !is_array($manifest['files'])) {
                error_log('[AI STATIC ERROR]: Invalid manifest JSON: ' . $ai_output);
                Response::json(l('global.error_message.basic'), 'error');
            }

            /* Too many files */
            if(count($manifest['files']) > $maximum_files_amount) Response::json(l('global.error_message.basic'), 'error');

            /* Write files */
            $has_index = false;
            $real_base_folder = realpath($base_folder);

            /* Go through each items from the response and treat them as files */
            foreach($manifest['files'] as $file) {
                if(!isset($file['path']) || !isset($file['content'])) continue;

                $path = str_replace('\\', '/', (string) $file['path']);
                $path = ltrim($path, '/');

                if($path === '' || strpos($path, "\0") !== false) continue;
                if(strpos($path, '../') !== false || preg_match('~(^|/)\.\.?(/|$)~', $path)) continue;

                $file_extension = mb_strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if(!$file_extension || !in_array($file_extension, $allowed_extensions, true)) continue;

                /* Make sure it has an index file */
                if($path === 'index.html') $has_index = true;

                $content = (string) $file['content'];

                /* Make sure the size does not exceed the limit */
                $total_bytes += strlen($content);
                if($max_total_bytes && $total_bytes > $max_total_bytes) {
                    Response::json(sprintf(l('global.error_message.file_size_limit'), settings()->links->static_size_limit), 'error');
                }

                $target_file = $base_folder . '/' . $path;
                $target_directory = dirname($target_file);

                /* Create the directory if not existing */
                if(!is_dir($target_directory)) mkdir($target_directory, 0777, true);

                $real_target_dir = realpath($target_directory);
                if(!$real_target_dir || strpos($real_target_dir, $real_base_folder) !== 0) {
                    Response::json(l('global.error_message.basic'), 'error');
                }

                /* Save file to the disk */
                file_put_contents($target_file, $content);
                $files[] = $path;

                $rel_folder = trim(str_replace($base_folder, '', $target_directory), '/');
                if($rel_folder) $folders[$rel_folder . '/'] = true;
            }

            $folders = array_keys($folders);

            /* Error if no index file */
            if(!$has_index || !is_file($base_folder . '/index.html')) {
                Response::json(l('global.error_message.basic'), 'error');
            }
        }

        /* Store some info */
        $additional = [
            'mode' => $mode,
            'static_folder' => $static_folder_name,
            'files' => $files,
            'total_files' => count($files),
        ];

        /* Create link */
        $url = $_POST['url'] ?? mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
        $type = 'static';

        /* Generate unique URL */
        while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
            $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
        }

        $this->check_url($_POST['url']);

        /* Unique access token */
        $token = bin2hex(random_bytes(16));

        /* Insert into database */
        $link_id = db()->insert('links', [
            'user_id' => $this->user->user_id,
            'domain_id' => $domain_id,
            'token' => $token,
            'type' => $type,
            'url' => $url,
            'settings' => json_encode($settings),
            'additional' => json_encode($additional),
            'datetime' => get_date(),
            'email_reports_last_datetime' => get_date(),
        ]);

        if($mode == 'ai') {
            /* Track AI usage */
            db()->where('user_id', $this->user->user_id)->update('users', [
                'ai_static_prompts_current_month' => db()->inc()
            ]);
        }

        /* Clear caches */
        cache()->deleteItem($type . '_links_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('links_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->link_new) {
            fire_and_forget('post', settings()->webhooks->link_new, [
                'user_id' => $this->user->user_id,
                'link_id' => $link_id,
                'domain_id' => $domain_id,
                'url' => $url,
                'full_url' => $domain_id ? $domains[$domain_id]->url . $url : SITE_URL . $url,
                'type' => $type,
                'datetime' => get_date(),
            ], signature: true);
        }

        /* Return success */
        Response::json(l('global.success_message.create2'), 'success', [
            'url' => url('link/' . $link_id . ($this->user->preferences->links_auto_copy_link ? '?auto_copy_link=true' : ''))
        ]);
    }

    private function update() {
        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.links')) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        if(empty($_POST)) {
            die();
        }

        /* Check for possible errors */
        if(!array_key_exists($_POST['type'], $this->links_types)) {
            die();
        }

        $this->{'update_' . $_POST['type']}();

    }

    private function update_link() {
        if(!settings()->links->shortener_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['project_id'] = empty($_POST['project_id']) ? null : (int) $_POST['project_id'];
        $_POST['url'] = !empty($_POST['url']) ? get_slug($_POST['url'], '-', false) : false;
        $_POST['location_url'] = get_url($_POST['location_url']);
        $_POST['schedule'] = (int) isset($_POST['schedule']);
        if($_POST['schedule'] && !empty($_POST['start_date']) && !empty($_POST['end_date']) && Date::validate($_POST['start_date'], 'Y-m-d H:i:s') && Date::validate($_POST['end_date'], 'Y-m-d H:i:s')) {
            $_POST['start_date'] = (new \DateTime($_POST['start_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
            $_POST['end_date'] = (new \DateTime($_POST['end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
        } else {
            $_POST['start_date'] = $_POST['end_date'] = null;
        }
        $_POST['expiration_url'] = get_url($_POST['expiration_url']);
        $_POST['clicks_limit'] = empty($_POST['clicks_limit']) ? null : (int) $_POST['clicks_limit'];
        $this->check_location_url($_POST['expiration_url'], true);
        $_POST['sensitive_content'] = (int) isset($_POST['sensitive_content']);
        $_POST['app_linking_is_enabled'] = (int) isset($_POST['app_linking_is_enabled']);
        $_POST['cloaking_is_enabled'] = (int) isset($_POST['cloaking_is_enabled']);
        $_POST['cloaking_title'] = input_clean($_POST['cloaking_title'], 70);
        $_POST['cloaking_meta_description'] = input_clean($_POST['cloaking_meta_description'], 160);
        $_POST['cloaking_custom_js'] = mb_substr(trim($_POST['cloaking_custom_js']), 0, 10000);
        $_POST['seo_block'] = (int) isset($_POST['seo_block']);

        /* Query parameters forwarding */
        $_POST['forward_query_parameters_is_enabled'] = (int) isset($_POST['forward_query_parameters_is_enabled']);

        /* UTM */
        $_POST['utm_medium'] = input_clean($_POST['utm_medium'], 128);
        $_POST['utm_source'] = input_clean($_POST['utm_source'], 128);
        $_POST['utm_campaign'] = input_clean($_POST['utm_campaign'], 128);

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Authentication::is_admin()) {
            Response::json(l('create_link_modal.error_message.main_domain_is_disabled'), 'error');
        }

        /* Get domains */
        $domains = (new Domain())->get_available_domains_by_user($this->user);

        /* Check if custom domain is set */
        $domain_id = isset($domains[$_POST['domain_id']]) ? $_POST['domain_id'] : 0;

        /* Exclusivity check */
        $_POST['is_main_link'] = isset($_POST['is_main_link']) && $domain_id && $domains[$_POST['domain_id']]->type == 0;

        /* Existing pixels */
        $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);
        $_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map(
            'intval',
            array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
                return array_key_exists($pixel_id, $pixels);
            })
        ) : [];
        $_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

        /* Check for any errors */
        $required_fields = ['location_url'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                Response::json(l('global.error_message.empty_fields'), 'error');
                break 1;
            }
        }

        $this->check_url($_POST['url']);

        $this->check_location_url($_POST['location_url']);

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }
        $link->settings = json_decode($link->settings ?? '');

        /* Cloaking */
        $link->settings->cloaking_favicon = \Altum\Uploads::process_upload($link->settings->cloaking_favicon, 'favicons', 'cloaking_favicon', 'cloaking_favicon_remove', settings()->links->favicon_size_limit, 'json_error');
        $link->settings->cloaking_opengraph = \Altum\Uploads::process_upload($link->settings->cloaking_opengraph, 'biolink_seo_image', 'cloaking_opengraph', 'cloaking_opengraph_remove', settings()->links->seo_image_size_limit, 'json_error');

        /* Existing projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

        /* Existing splash pages */
        $splash_pages = (new \Altum\Models\SplashPages())->get_splash_pages_by_user_id($this->user->user_id);
        $_POST['splash_page_id'] = !empty($_POST['splash_page_id']) && array_key_exists($_POST['splash_page_id'], $splash_pages) ? (int) $_POST['splash_page_id'] : null;

        /* Check for a password set */
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
        $_POST['password'] = !empty($_POST['qweasdzxc']) ?
            ($_POST['qweasdzxc'] != $link->settings->password ? password_hash($_POST['qweasdzxc'], PASSWORD_DEFAULT) : $link->settings->password)
            : null;


        /* Check for duplicate url if needed */
        if($_POST['url'] && ($_POST['url'] != $link->url || $domain_id != $link->domain_id)) {

            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(l('link.error_message.url_exists'), 'error');
            }

        }

        $url = $_POST['url'];

        if(empty($_POST['url'])) {
            /* Generate random url if not specified */
            $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));

            while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
            }

        }

        /* App linking check */
        $app_linking = [
            'ios_location_url' => null,
            'android_location_url' => null,
            'app' => null,
        ];

        if($_POST['app_linking_is_enabled']) {
            $supported_apps = require APP_PATH . 'includes/app_linking.php';
            $app_linking_location_url = $_POST['location_url'];

            foreach($supported_apps as $app_key => $app) {
                foreach($app['formats'] as $format => $targets) {

                    if(preg_match('~' . $targets['regex'] . '~', $app_linking_location_url, $match)) {

                        /* Extract and normalize hostnames */
                        $user_host = parse_url($app_linking_location_url, PHP_URL_HOST);
                        $format_host = parse_url('https://' . str_replace('%s', 'placeholder', $format), PHP_URL_HOST);

                        /* Remove www. and m. prefixes for more flexible comparison */
                        $user_host = preg_replace('/^(www\.|m\.)/', '', $user_host);
                        $format_host = preg_replace('/^(www\.|m\.)/', '', $format_host);

                        /* Compare the normalized hosts */
                        if($user_host === $format_host) {

                            if(count($match) > 1) {
                                array_shift($match);
                                $app_linking['ios_location_url'] = vsprintf($targets['iOS'], $match);
                                $app_linking['android_location_url'] = vsprintf($targets['Android'], $match);
                                $app_linking['app'] = $app_key;
                            }

                            break 2;
                        }
                    }

                }
            }
        }

        /* Prepare the settings */
        $targeting_types = ['continent_code', 'country_code', 'city_name', 'device_type', 'browser_language', 'rotation', 'os_name', 'browser_name'];
        $_POST['targeting_type'] = in_array($_POST['targeting_type'], array_merge(['false'], $targeting_types)) ? query_clean($_POST['targeting_type']) : 'false';
        $_POST['http_status_code'] = in_array($_POST['http_status_code'], [301, 302, 307, 308]) ? (int) $_POST['http_status_code'] : 301;

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Notification handlers */
        $_POST['email_reports'] = array_map(
            'intval',
            array_filter($_POST['email_reports'] ?? [], function($notification_handler_id) use ($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );

        $settings = [
            'clicks_limit' => $_POST['clicks_limit'],
            'expiration_url' => $_POST['expiration_url'],
            'schedule' => $_POST['schedule'],
            'password' => $_POST['password'],
            'sensitive_content' => $_POST['sensitive_content'],
            'targeting_type' => $_POST['targeting_type'],
            'http_status_code' => $_POST['http_status_code'],

            /* Cloaking */
            'cloaking_is_enabled' => $_POST['cloaking_is_enabled'],
            'cloaking_title' => $_POST['cloaking_title'],
            'cloaking_meta_description' => $_POST['cloaking_meta_description'],
            'cloaking_custom_js' => $_POST['cloaking_custom_js'],
            'cloaking_favicon' => $link->settings->cloaking_favicon,
            'cloaking_opengraph' => $link->settings->cloaking_opengraph,

            /* App linking */
            'app_linking_is_enabled' => $_POST['app_linking_is_enabled'],
            'app_linking' => $app_linking,

            /* Forward query parameters */
            'forward_query_parameters_is_enabled' => $_POST['forward_query_parameters_is_enabled'],

            /* UTM */
            'utm' => [
                'source' => $_POST['utm_source'],
                'medium' => $_POST['utm_medium'],
                'campaign' => $_POST['utm_campaign'],
            ],

            /* SEO */
            'seo' => [
                'block' => $_POST['seo_block'],
            ],
        ];

        /* Process the targeting */
        foreach($targeting_types as $targeting_type) {
            ${'targeting_' . $targeting_type} = [];

            if(isset($_POST['targeting_' . $targeting_type . '_key'])) {
                foreach($_POST['targeting_' . $targeting_type . '_key'] as $key => $value) {
                    if(empty(trim($_POST['targeting_' . $targeting_type . '_value'][$key]))) continue;

                    ${'targeting_' . $targeting_type}[] = [
                        'key' => trim(query_clean($value)),
                        'value' => get_url($_POST['targeting_' . $targeting_type . '_value'][$key]),
                    ];
                }

                $settings['targeting_' . $targeting_type] = ${'targeting_' . $targeting_type};
            }
        }

        $settings = json_encode($settings);

        db()->where('link_id', $_POST['link_id'])->update('links', [
            'project_id' => $_POST['project_id'],
            'email_reports' => json_encode($_POST['email_reports']),
            'email_reports_count' => count($_POST['email_reports']),
            'email_reports_last_datetime' => !$link->email_reports_last_datetime ? get_date() : $link->email_reports_last_datetime,
            'splash_page_id' => $_POST['splash_page_id'],
            'domain_id' => $domain_id,
            'pixels_ids' => $_POST['pixels_ids'],
            'url' => $url,
            'location_url' => $_POST['location_url'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'settings' => $settings,
            'last_datetime' => get_date(),
        ]);

        $this->process_is_main_link_domain($link, $domains);

        $url = $domain_id && $_POST['is_main_link'] ? '' : $url;

        /* Clear the cache */
        cache()->deleteItem('biolink_blocks?link_id=' . $link->link_id);
        cache()->deleteItem('link?link_id=' . $link->link_id);
        cache()->deleteItemsByTag('link_id=' . $link->link_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->link_update) {
            fire_and_forget('post', settings()->webhooks->link_update, [
                'user_id' => $this->user->user_id,
                'link_id' => $_POST['link_id'],
                'domain_id' => $domain_id,
                'url' => $url,
                'location_url' => $_POST['location_url'],
                'full_url' => $domain_id ? $domains[$domain_id]->url . ($domains[$domain_id]->link_id == $_POST['link_id'] ? null : $url) : SITE_URL . $url,
                'type' => 'link',
                'datetime' => get_date(),
            ], signature: true);
        }

        Response::json(l('global.success_message.update2'), 'success', ['url' => $url, 'app_linking' => $app_linking]);
    }

    private function update_biolink() {
        if(!settings()->links->biolinks_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        $_POST['project_id'] = empty($_POST['project_id']) ? null : (int) $_POST['project_id'];
        $_POST['url'] = !empty($_POST['url']) ? get_slug($_POST['url'], '-', false) : false;

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Authentication::is_admin()) {
            Response::json(l('create_link_modal.error_message.main_domain_is_disabled'), 'error');
        }

        /* Get domains */
        $domains = (new Domain())->get_available_domains_by_user($this->user);

        /* Check if custom domain is set */
        $domain_id = isset($domains[$_POST['domain_id']]) ? $_POST['domain_id'] : 0;

        /* Exclusivity check */
        $_POST['is_main_link'] = isset($_POST['is_main_link']) && $domain_id && $domains[$_POST['domain_id']]->type == 0;

        /* Check for any errors */
        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }

        /* Existing projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

        /* Existing splash pages */
        $splash_pages = (new \Altum\Models\SplashPages())->get_splash_pages_by_user_id($this->user->user_id);
        $_POST['splash_page_id'] = !empty($_POST['splash_page_id']) && array_key_exists($_POST['splash_page_id'], $splash_pages) ? (int) $_POST['splash_page_id'] : null;

        $link->settings = json_decode($link->settings ?? '');

        /* Get available themes */
        $biolinks_themes = (new BiolinksThemes())->get_biolinks_themes();
        $_POST['biolink_theme_id'] = isset($_POST['biolink_theme_id']) && array_key_exists($_POST['biolink_theme_id'], $biolinks_themes) ? (int) $_POST['biolink_theme_id'] : null;

        /* Make sure theme is accessible via plan */
        $_POST['biolink_theme_id'] = $_POST['biolink_theme_id'] && in_array($_POST['biolink_theme_id'], $this->user->plan_settings->biolinks_themes ?? []) ? $_POST['biolink_theme_id'] : null;

        /* Existing pixels */
        $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);
        $_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map(
            'intval',
            array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
                return array_key_exists($pixel_id, $pixels);
            })
        ) : [];
        $_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

        if($_POST['url'] == $link->url) {
            $url = $link->url;

            if($link->domain_id != $domain_id) {
                if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                    Response::json(l('link.error_message.url_exists'), 'error');
                }
            }

        } else {
            $url = $_POST['url'] ? $_POST['url'] : mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));

            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(l('link.error_message.url_exists'), 'error');
            }

            /* Generate random url if not specified */
            while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
            }

            $this->check_url($_POST['url']);
        }

        /* Image uploads */
		$image_file_size_limits = [
			'branded_button_icon' => settings()->links->{'branded_button_icon_size_limit'},
			'pwa_icon' => settings()->links->{'pwa_icon_size_limit'},
			'seo_image' => settings()->links->{'seo_image_size_limit'},
			'password_seo_image' => settings()->links->{'seo_image_size_limit'},
			'sensitive_content_seo_image' => settings()->links->{'seo_image_size_limit'},
			'favicon' => settings()->links->{'favicon_size_limit'},
			'background' => settings()->links->{'background_size_limit'},
		];
        $image_allowed_extensions = [
            'branded_button_icon' => \Altum\Uploads::get_whitelisted_file_extensions('branded_button_icon'),
            'pwa_icon' => \Altum\Uploads::get_whitelisted_file_extensions('app_icon'),
			'seo_image' => \Altum\Uploads::get_whitelisted_file_extensions('biolink_seo_image'),
			'password_seo_image' => \Altum\Uploads::get_whitelisted_file_extensions('biolink_seo_image'),
			'sensitive_content_seo_image' => \Altum\Uploads::get_whitelisted_file_extensions('biolink_seo_image'),
            'favicon' => \Altum\Uploads::get_whitelisted_file_extensions('favicons'),
            'background' => \Altum\Uploads::get_whitelisted_file_extensions('biolink_background'),
        ];
        $image = [
            'branded_button_icon' => !empty($_FILES['branded_button_icon']['name']) && !isset($_POST['branded_button_icon_remove']),
            'pwa_icon' => !empty($_FILES['pwa_icon']['name']) && !isset($_POST['pwa_icon_remove']),
			'seo_image' => !empty($_FILES['seo_image']['name']) && !isset($_POST['seo_image_remove']),
			'password_seo_image' => !empty($_FILES['password_seo_image']['name']) && !isset($_POST['password_seo_image_remove']),
			'sensitive_content_seo_image' => !empty($_FILES['sensitive_content_seo_image']['name']) && !isset($_POST['sensitive_content_seo_image_remove']),
            'favicon' => !empty($_FILES['favicon']['name']) && !isset($_POST['favicon_remove']),
            'background' => !empty($_FILES['background']['name']) && !isset($_POST['background_remove']),
        ];
        $image_upload_path = [
            'branded_button_icon' => \Altum\Uploads::get_path('branded_button_icon'),
            'pwa_icon' => \Altum\Uploads::get_path('app_icon'),
			'seo_image' => \Altum\Uploads::get_path('biolink_seo_image'),
			'password_seo_image' => \Altum\Uploads::get_path('biolink_seo_image'),
			'sensitive_content_seo_image' => \Altum\Uploads::get_path('biolink_seo_image'),
            'favicon' => \Altum\Uploads::get_path('favicons'),
            'background' => \Altum\Uploads::get_path('biolink_background'),
        ];
        $image_uploaded_file = [
            'branded_button_icon' => $link->settings->branded_button_icon ?? null,
            'pwa_icon' => $link->settings->pwa_icon ?? null,
			'seo_image' => $link->settings->seo->image ?? null,
			'password_seo_image' => $link->settings->password_seo_image ?? null,
			'sensitive_content_seo_image' => $link->settings->sensitive_content_seo_image ?? null,
            'favicon' => $link->settings->favicon ?? null,
        ];
        $image_url = [
            'branded_button_icon' => null,
            'pwa_icon' => null,
			'seo_image' => null,
			'password_seo_image' => null,
			'sensitive_content_seo_image' => null,
            'favicon' => null,
            'background' => null,
        ];

        foreach(['favicon', 'seo_image', 'password_seo_image', 'sensitive_content_seo_image', 'pwa_icon', 'branded_button_icon'] as $image_key) {
            if($image[$image_key]) {
                $file_name = $_FILES[$image_key]['name'];
                $file_extension = mb_strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $file_temp = $_FILES[$image_key]['tmp_name'];

                if($_FILES[$image_key]['error'] == UPLOAD_ERR_INI_SIZE) {
                    Response::json(sprintf(l('global.error_message.file_size_limit'), $image_file_size_limits[$image_key]), 'error');
                }

                if($_FILES[$image_key]['error'] && $_FILES[$image_key]['error'] != UPLOAD_ERR_INI_SIZE) {
                    Response::json(l('global.error_message.file_upload'), 'error');
                }

                if(!in_array($file_extension, $image_allowed_extensions[$image_key])) {
                    Response::json(l('global.error_message.invalid_file_type'), 'error');
                }

                if(!\Altum\Plugin::is_active('offload') || (\Altum\Plugin::is_active('offload') && !settings()->offload->uploads_url)) {
                    if(!is_writable(UPLOADS_PATH . $image_upload_path[$image_key])) {
                        Response::json(sprintf(l('global.error_message.directory_not_writable'), UPLOADS_PATH . $image_upload_path[$image_key]), 'error');
                    }
                }

                if($_FILES[$image_key]['size'] > $image_file_size_limits[$image_key] * 1000000) {
                    Response::json(sprintf(l('global.error_message.file_size_limit'), $image_file_size_limits[$image_key]), 'error');
                }

                /* Generate new name for image */
                $image_new_name = md5(uniqid('', true) . random_bytes(16)) . '.' . $file_extension;

                /* Try to compress the image */
                if(\Altum\Plugin::is_active('image-optimizer') && settings()->image_optimizer->is_enabled) {
                    \Altum\Plugin\ImageOptimizer::optimize($file_temp, $image_new_name, $_FILES[$image_key]['name'], UPLOADS_PATH . $image_upload_path[$image_key]);
                }

                /* Sanitize SVG uploads */
                if($file_extension == 'svg') {
                    $svg_sanitizer = new \enshrined\svgSanitize\Sanitizer();
                    $dirty_svg = file_get_contents($file_temp);
                    $clean_svg = $svg_sanitizer->sanitize($dirty_svg);
                    $clean_svg = \Altum\Uploads::clean_svg($clean_svg);
                    file_put_contents($file_temp, $clean_svg);
                }

                /* Offload uploading */
                if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url) {
                    try {
                        $s3 = new \Aws\S3\S3Client(get_aws_s3_config());

                        /* Delete current image */
                        $s3->deleteObject([
                            'Bucket' => settings()->offload->storage_name,
                            'Key' => 'uploads/' . $image_upload_path[$image_key] . $image_uploaded_file[$image_key],
                        ]);

                        /* Upload image */
                        $result = $s3->putObject([
                            'Bucket' => settings()->offload->storage_name,
                            'Key' => 'uploads/' . $image_upload_path[$image_key] . $image_new_name,
                            'ContentType' => mime_content_type($file_temp),
                            'SourceFile' => $file_temp,
                            'ACL' => 'public-read'
                        ]);
                    } catch (\Exception $exception) {
                        Response::json($exception->getMessage(), 'error');
                    }
                }

                /* Local uploading */
                else {
                    /* Delete current image */
                    if(!empty($image_uploaded_file[$image_key]) && file_exists(UPLOADS_PATH . $image_upload_path[$image_key] . $image_uploaded_file[$image_key])) {
                        unlink(UPLOADS_PATH . $image_upload_path[$image_key] . $image_uploaded_file[$image_key]);
                    }

                    /* Upload the original */
                    move_uploaded_file($file_temp, UPLOADS_PATH . $image_upload_path[$image_key] . $image_new_name);
                }

                $image_uploaded_file[$image_key] = $image_new_name;
            }

            /* Check for the removal of the already uploaded file */
            if(isset($_POST[$image_key . '_remove'])) {

                /* Offload deleting */
                if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url) {
                    $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
                    $s3->deleteObject([
                        'Bucket' => settings()->offload->storage_name,
                        'Key' => 'uploads/' . $image_upload_path[$image_key] . $image_uploaded_file[$image_key],
                    ]);
                }

                /* Local deleting */
                else {
                    /* Delete current file */
                    if(!empty($image_uploaded_file[$image_key]) && file_exists(UPLOADS_PATH . $image_upload_path[$image_key] . $image_uploaded_file[$image_key])) {
                        unlink(UPLOADS_PATH . $image_upload_path[$image_key] . $image_uploaded_file[$image_key]);
                    }
                }

                $image_uploaded_file[$image_key] = null;
            }

            $image_url[$image_key] = $image_uploaded_file[$image_key] ? UPLOADS_FULL_URL . $image_upload_path[$image_key] . $image_uploaded_file[$image_key] : null;
        }

        $biolink_backgrounds = require APP_PATH . 'includes/biolink_backgrounds.php';
        $_POST['background_type'] = array_key_exists($_POST['background_type'], $biolink_backgrounds) ? $_POST['background_type'] : 'preset';
        $_POST['background_attachment'] = isset($_POST['background_attachment']) && in_array($_POST['background_attachment'], ['scroll', 'fixed']) ? $_POST['background_attachment'] : 'scroll';
        $_POST['background_blur'] = isset($_POST['background_blur']) && in_array((int) $_POST['background_attachment'], range(0, 30)) ? (int) $_POST['background_blur'] : 0;
        $_POST['background_brightness'] = isset($_POST['background_brightness']) && in_array((int) $_POST['background_attachment'], range(0, 150)) ? (int) $_POST['background_brightness'] : 0;

        switch($_POST['background_type']) {
            case 'preset':
            case 'preset_abstract':
                $background = array_key_exists($_POST['background'], $biolink_backgrounds[$_POST['background_type']]) ? $_POST['background'] : 'zero';
                break;

            case 'color':

                $background = !verify_hex_color($_POST['background']) ? '#000000' : $_POST['background'];

                break;

            case 'gradient':

                $background_color_one = !verify_hex_color($_POST['background_color_one']) ? '#000000' : $_POST['background_color_one'];
                $background_color_two = !verify_hex_color($_POST['background_color_two']) ? '#000000' : $_POST['background_color_two'];

                break;

            case 'image':

                /* Background processing */
                if($image['background']) {
                    $background_file_extension = mb_strtolower(pathinfo($_FILES['background']['name'], PATHINFO_EXTENSION));
                    $background_file_temp = $_FILES['background']['tmp_name'];

                    if($_FILES['background']['error'] == UPLOAD_ERR_INI_SIZE) {
                        Response::json(sprintf(l('global.error_message.file_size_limit'), settings()->links->background_size_limit), 'error');
                    }

                    if($_FILES['background']['error'] && $_FILES['background']['error'] != UPLOAD_ERR_INI_SIZE) {
                        Response::json(l('global.error_message.file_upload'), 'error');
                    }

                    if(!is_writable(UPLOADS_PATH . $image_upload_path['background'])) {
                        Response::json(sprintf(l('global.error_message.directory_not_writable'), UPLOADS_PATH . $image_upload_path['background']), 'error');
                    }

                    if(!in_array($background_file_extension, $image_allowed_extensions['background'])) {
                        Response::json(l('global.error_message.invalid_file_type'), 'error');
                    }

                    if($_FILES['background']['size'] > settings()->links->background_size_limit * 1000000) {
                        Response::json(sprintf(l('global.error_message.file_size_limit'), settings()->links->background_size_limit), 'error');
                    }

                    /* Generate new name */
                    $background_new_name = md5(uniqid('', true) . random_bytes(16)) . '.' . $background_file_extension;

                    /* Try to compress the image */
                    if(\Altum\Plugin::is_active('image-optimizer') && settings()->image_optimizer->is_enabled) {
                        \Altum\Plugin\ImageOptimizer::optimize($background_file_temp, $background_new_name, $_FILES['background']['name'], UPLOADS_PATH . $image_upload_path['background']);
                    }

                    /* Sanitize SVG uploads */
                    if($background_file_extension == 'svg') {
                        $svg_sanitizer = new \enshrined\svgSanitize\Sanitizer();
                        $dirty_svg = file_get_contents($background_file_temp);
                        $clean_svg = $svg_sanitizer->sanitize($dirty_svg);
                        $clean_svg = \Altum\Uploads::clean_svg($clean_svg);
                        file_put_contents($background_file_temp, $clean_svg);
                    }

                    /* Offload uploading */
                    if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url) {
                        try {
                            $s3 = new \Aws\S3\S3Client(get_aws_s3_config());

                            /* Delete current image */
                            if(!$link->biolink_theme_id && is_string($link->settings->background)) {
                                $s3->deleteObject([
                                    'Bucket' => settings()->offload->storage_name,
                                    'Key' => 'uploads/backgrounds/' . $link->settings->background,
                                ]);
                            }

                            /* Upload image */
                            $result = $s3->putObject([
                                'Bucket' => settings()->offload->storage_name,
                                'Key' => 'uploads/backgrounds/' . $background_new_name,
                                'ContentType' => mime_content_type($background_file_temp),
                                'SourceFile' => $background_file_temp,
                                'ACL' => 'public-read'
                            ]);
                        } catch (\Exception $exception) {
                            Response::json($exception->getMessage(), 'error');
                        }
                    }

                    /* Local uploading */
                    else {
                        /* Delete current file */
                        if(!$link->biolink_theme_id && is_string($link->settings->background) && !empty($link->settings->background) && file_exists(UPLOADS_PATH . $image_upload_path['background'] . $link->settings->background)) {
                            unlink(UPLOADS_PATH . $image_upload_path['background'] . $link->settings->background);
                        }

                        /* Upload the original */
                        move_uploaded_file($background_file_temp, UPLOADS_PATH . $image_upload_path['background'] . $background_new_name);
                    }

                    $background = $background_new_name;
                }

                break;
        }

        /* Delete existing background file if needed */
        if(
            $link->settings->background_type == 'image'
            && (
                $image['background']
                || ($_POST['biolink_theme_id'] && $link->biolink_theme_id != $_POST['biolink_theme_id'])
                || $_POST['background_type'] != $link->settings->background_type
            )
            && is_string($link->settings->background)
            && !$link->biolink_theme_id
        ) {
            /* Offload deleting */
            if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url) {
                $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
                $s3->deleteObject([
                    'Bucket' => settings()->offload->storage_name,
                    'Key' => 'uploads/backgrounds/' . $link->settings->background,
                ]);
            }

            /* Local deleting */
            else {
                /* Delete current file */
                if(!empty($link->settings->background) && file_exists(UPLOADS_PATH . $image_upload_path['background'] . $link->settings->background)) {
                    unlink(UPLOADS_PATH . $image_upload_path['background'] . $link->settings->background);
                }
            }
        }

        $_POST['text_color'] = !verify_hex_color($_POST['text_color']) ? '#ffffff' : $_POST['text_color'];
        $_POST['display_branding'] = (int) isset($_POST['display_branding']);
        $_POST['verified_location'] = in_array($_POST['verified_location'], ['', 'top', 'bottom']) ? query_clean($_POST['verified_location']) : 'top';
        $_POST['branding_name'] = mb_substr(trim(query_clean($_POST['branding_name'])), 0, 128);
        $_POST['branding_url'] = get_url($_POST['branding_url']);
        $_POST['seo_block'] = (int) isset($_POST['seo_block']);
        $_POST['seo_title'] = trim(query_clean(mb_substr($_POST['seo_title'], 0, 70)));
        $_POST['seo_meta_description'] = trim(query_clean(mb_substr($_POST['seo_meta_description'], 0, 160)));
        $_POST['seo_meta_keywords'] = trim(query_clean(mb_substr($_POST['seo_meta_keywords'], 0, 160)));
        $_POST['language_code'] = array_key_exists($_POST['language_code'], get_locale_languages_array()) ? $_POST['language_code'] : \Altum\Language::$default_code;
        $_POST['utm_medium'] = input_clean($_POST['utm_medium'], 128);
        $_POST['utm_source'] = input_clean($_POST['utm_source'], 128);

		/* Password */
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
        $_POST['password'] = !empty($_POST['qweasdzxc']) ?
            ($_POST['qweasdzxc'] != $link->settings->password ? password_hash($_POST['qweasdzxc'], PASSWORD_DEFAULT) : $link->settings->password)
            : null;
		$_POST['password_seo_title'] = trim(query_clean(mb_substr($_POST['password_seo_title'], 0, 70)));
		$_POST['password_seo_meta_description'] = trim(query_clean(mb_substr($_POST['password_seo_meta_description'], 0, 160)));
		$_POST['password_seo_meta_keywords'] = trim(query_clean(mb_substr($_POST['password_seo_meta_keywords'], 0, 160)));

		/* Sensitive content */
        $_POST['sensitive_content'] = (int) isset($_POST['sensitive_content']);
		$_POST['sensitive_content_seo_title'] = trim(query_clean(mb_substr($_POST['sensitive_content_seo_title'], 0, 70)));
		$_POST['sensitive_content_seo_meta_description'] = trim(query_clean(mb_substr($_POST['sensitive_content_seo_meta_description'], 0, 160)));
		$_POST['sensitive_content_seo_meta_keywords'] = trim(query_clean(mb_substr($_POST['sensitive_content_seo_meta_keywords'], 0, 160)));

		$_POST['custom_css'] = mb_substr(trim($_POST['custom_css']), 0, 10000);
        $_POST['custom_js'] = mb_substr(trim($_POST['custom_js']), 0, 10000);
        $_POST['leap_link'] = get_url($_POST['leap_link'] ?? null);
        $_POST['share_is_enabled'] = (int) isset($_POST['share_is_enabled']);
        $_POST['scroll_buttons_is_enabled'] = (int) isset($_POST['scroll_buttons_is_enabled']);
        $_POST['directory_is_enabled'] = (int) isset($_POST['directory_is_enabled']);
        $this->check_location_url($_POST['leap_link'], true);

        /* Make sure the font is ok */
        $_POST['font'] = !array_key_exists($_POST['font'], (array) settings()->links->biolinks_fonts) ? false : query_clean($_POST['font']);
        $_POST['font_size'] = (int) $_POST['font_size'] < 12 || (int) $_POST['font_size'] > 22 ? 16 : (int) $_POST['font_size'];

        /* Width */
        $_POST['width'] = isset($_POST['width']) && in_array($_POST['width'], [6, 8, 10, 12]) ? (int) $_POST['width'] : 8;

        /* Block spacing */
        $_POST['block_spacing'] = isset($_POST['block_spacing']) && in_array($_POST['block_spacing'], [1, 2, 3,]) ? (int) $_POST['block_spacing'] : 2;

        /* Link hover animation */
        $_POST['hover_animation'] = isset($_POST['hover_animation']) && in_array($_POST['hover_animation'], ['false', 'smooth', 'instant',]) ? input_clean($_POST['hover_animation']) : 'smooth';

        /* Service worker */
        if(settings()->links->sixsixpusher_is_enabled) {
            $service_worker = \Altum\Uploads::process_upload($link->settings->service_worker, 'service_workers', 'service_worker', 'service_worker_remove', settings()->links->sixsixpusher_service_worker_size_limit, 'json_error', force_local: true);
        }

        /* PWA generation */
        $_POST['pwa_is_enabled'] = (int) isset($_POST['pwa_is_enabled']);
        $_POST['pwa_display_install_bar'] = (int) isset($_POST['pwa_display_install_bar']);
        $_POST['pwa_display_install_bar_delay'] = max(1, (int) $_POST['pwa_display_install_bar_delay'] ?? 3);
        $_POST['pwa_theme_color'] = isset($_POST['pwa_theme_color']) && verify_hex_color($_POST['pwa_theme_color']) ? $_POST['pwa_theme_color'] : '#000000';

        if(\Altum\Plugin::is_active('pwa') && settings()->pwa->is_enabled && $this->user->plan_settings->custom_pwa_is_enabled && $_POST['pwa_is_enabled']) {
            $pwa_file_name = $link->settings->pwa_file_name ?? 'biolinks-' . md5(uniqid('', true) . random_bytes(16));

            $start_url = $domain_id ? $domains[$_POST['domain_id']]->scheme . $domains[$_POST['domain_id']]->host . '/' . ($_POST['is_main_link'] ? null : $_POST['url']) : SITE_URL . $_POST['url'];
            $scope_url = $start_url;

            /* Add UTM tracking params */
            $start_url = $start_url . '?' . http_build_query([
                    'utm_source' => 'pwa',
                    'utm_medium' => 'web-app',
                    'utm_campaign' => 'install-or-pwa-launch',
                ]);

            /* Generate the manifest file */
            $manifest = pwa_generate_manifest([
                'name' => $_POST['seo_title'] ?: $_POST['url'] . ' - ' . settings()->main->title,
                'short_name' => $_POST['url'],
                'description' => $_POST['seo_meta_description'] ?: $_POST['url'],
                'theme_color' => $_POST['pwa_theme_color'],
                'app_icon_url' => $image_uploaded_file['pwa_icon'] ? \Altum\Uploads::get_full_url('app_icon') . $image_uploaded_file['pwa_icon'] : (settings()->pwa->app_icon ? \Altum\Uploads::get_full_url('app_icon') . settings()->pwa->app_icon : null),
                'app_icon_maskable_url' => $image_uploaded_file['pwa_icon'] ? \Altum\Uploads::get_full_url('app_icon') . $image_uploaded_file['pwa_icon'] : (settings()->pwa->app_icon_maskable ? \Altum\Uploads::get_full_url('app_icon') . settings()->pwa->app_icon_maskable : null),
                'start_url' => $start_url,
                'scope' => $scope_url,
                'mobile_screenshots' => [],
                'desktop_screenshots' => [],
                'shortcuts' => [],
            ]);
            pwa_save_manifest($manifest, $pwa_file_name);
        }

        /* Branded button */
        $_POST['branded_button_is_enabled'] = (int) isset($_POST['branded_button_is_enabled']);
        $_POST['branded_button_title'] = input_clean($_POST['branded_button_title'], 64);
        $_POST['branded_button_content'] = mb_substr(trim($_POST['branded_button_content']), 0, 10000);

        /* Set the new settings variable */
        $settings = [
            'service_worker' => $service_worker ?? null,

            'pwa_file_name' => $pwa_file_name ?? null,
            'pwa_is_enabled' => $_POST['pwa_is_enabled'],
            'pwa_display_install_bar' => $_POST['pwa_display_install_bar'],
            'pwa_display_install_bar_delay' => $_POST['pwa_display_install_bar_delay'],
            'pwa_theme_color' => $_POST['pwa_theme_color'],
            'pwa_icon' => $image_uploaded_file['pwa_icon'],

            'branded_button_is_enabled' => $_POST['branded_button_is_enabled'],
            'branded_button_icon' => $image_uploaded_file['branded_button_icon'],
            'branded_button_title' => $_POST['branded_button_title'],
            'branded_button_content' => $_POST['branded_button_content'],

            'verified_location' => $_POST['verified_location'],
            'background_type' => $_POST['background_type'],
            'background_attachment' => $_POST['background_attachment'],
            'background_blur' => $_POST['background_blur'],
            'background_brightness' => $_POST['background_brightness'],
            'background' => $background ?? $link->settings->background,
            'background_color_one' => $background_color_one ?? null,
            'background_color_two' => $background_color_two ?? null,
            'favicon' => $image_uploaded_file['favicon'],
            'text_color' => $_POST['text_color'],
            'display_branding' => $_POST['display_branding'],
            'branding' => [
                'name' => $_POST['branding_name'],
                'url' => $_POST['branding_url'],
            ],
            'seo' => [
                'block' => $_POST['seo_block'],
                'title' => $_POST['seo_title'],
                'meta_description' => $_POST['seo_meta_description'],
                'meta_keywords' => $_POST['seo_meta_keywords'],
                'image' => $image_uploaded_file['seo_image'],
            ],
            'utm' => [
                'medium' => $_POST['utm_medium'],
                'source' => $_POST['utm_source'],
            ],
            'font' => $_POST['font'],
            'width' => $_POST['width'],
            'block_spacing' => $_POST['block_spacing'],
            'hover_animation' => $_POST['hover_animation'],
            'font_size' => $_POST['font_size'],

			/* Password */
            'password' => $_POST['password'],
			'password_seo_title' => $_POST['password_seo_title'],
			'password_seo_meta_description' => $_POST['password_seo_meta_description'],
			'password_seo_meta_keywords' => $_POST['password_seo_meta_keywords'],
			'password_seo_image' => $image_uploaded_file['password_seo_image'],

			/* Sensitive content */
            'sensitive_content' => $_POST['sensitive_content'],
			'sensitive_content_seo_title' => $_POST['sensitive_content_seo_title'],
			'sensitive_content_seo_meta_description' => $_POST['sensitive_content_seo_meta_description'],
			'sensitive_content_seo_meta_keywords' => $_POST['sensitive_content_seo_meta_keywords'],
			'sensitive_content_seo_image' => $image_uploaded_file['sensitive_content_seo_image'],

            'leap_link' => $_POST['leap_link'],
            'custom_css' => $_POST['custom_css'],
            'custom_js' => $_POST['custom_js'],
            'share_is_enabled' => $_POST['share_is_enabled'],
            'scroll_buttons_is_enabled' => $_POST['scroll_buttons_is_enabled'],
            'language_code' => $_POST['language_code'],
        ];

        /* Check if we need to override defaults for a new theme */
        $additional = $link->additional ?? '';
        if($_POST['biolink_theme_id'] && $link->biolink_theme_id != $_POST['biolink_theme_id']) {
            $biolink_theme = $biolinks_themes[$_POST['biolink_theme_id']];

            /* Save settings for biolink page */
            $settings = array_merge($settings, (array) $biolink_theme->settings->biolink);

            /* Save the additional settings */
            $additional = json_encode($biolink_theme->settings->additional ?? '');

            /* Save settings for all existing blocks */
            $biolink_blocks = require APP_PATH . 'includes/biolink_blocks.php';
            $themable_blocks = array_keys(array_filter($biolink_blocks, fn($block) => !empty($block['themable'])));
            $themable_blocks_sql = "'" . implode('\', \'', $themable_blocks) . "'";

            $biolink_blocks_result = database()->query("SELECT `biolink_block_id`, `type`, `settings` FROM `biolinks_blocks` WHERE `link_id` = {$link->link_id} AND `type` IN ({$themable_blocks_sql})");
            while($biolink_block = $biolink_blocks_result->fetch_object()) {
                $biolink_block->settings = json_decode($biolink_block->settings ?? '');

                switch($biolink_block->type) {
                    case 'socials':
                        $biolink_block->settings = (object) array_merge((array) $biolink_block->settings, (array) $biolink_theme->settings->biolink_block_socials ?? []);
                        break;

                    case 'heading':
                        $biolink_block->settings = (object) array_merge((array) $biolink_block->settings, (array) $biolink_theme->settings->biolink_block_heading ?? []);
                        break;

                    case 'paragraph':
                        $biolink_block->settings = (object) array_merge((array) $biolink_block->settings, (array) $biolink_theme->settings->biolink_block ?? [], (array) $biolink_theme->settings->biolink_block_paragraph ?? []);
                        break;

                    case 'counter':
                    case 'loading':
                        $biolink_theme->settings->biolink_block->number_color = $biolink_theme->settings->biolink_block->text_color;

                        $biolink_block->settings = (object) array_merge((array) $biolink_block->settings, (array) $biolink_theme->settings->biolink_block ?? []);
                        break;

                    case 'external_item':
                        $biolink_theme->settings->biolink_block->price_color = $biolink_theme->settings->biolink_block->text_color;
                        $biolink_theme->settings->biolink_block->name_color = $biolink_theme->settings->biolink_block->text_color;

                        $biolink_block->settings = (object) array_merge((array) $biolink_block->settings, (array) $biolink_theme->settings->biolink_block ?? []);
                        break;

                    case 'business_hours':
                        $biolink_theme->settings->biolink_block->icon_color = $biolink_theme->settings->biolink_block->text_color;

                        $biolink_block->settings = (object) array_merge((array) $biolink_block->settings, (array) $biolink_theme->settings->biolink_block ?? []);
                        break;

                    default:
                        $biolink_block->settings = (object) array_merge((array) $biolink_block->settings, (array) $biolink_theme->settings->biolink_block ?? []);
                        break;
                }

                $new_biolink_block_settings = json_encode($biolink_block->settings);

                db()->where('biolink_block_id', $biolink_block->biolink_block_id)->update('biolinks_blocks', [
                    'settings' => $new_biolink_block_settings,
                ]);
            }

            /* Clear the cache */
            cache()->deleteItem('biolink_blocks?link_id=' . $link->link_id);
            cache()->deleteItem('link?link_id=' . $link->link_id);
            cache()->deleteItemsByTag('link_id=' . $link->link_id);
            cache()->deleteItem('links?user_id=' . $this->user->user_id);
        }

        /* Prepare background url if needed */
        $image_url['background'] = $settings['background_type'] == 'image' && $settings['background'] ?  UPLOADS_FULL_URL . $image_upload_path['background'] . $settings['background'] : null;

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Notification handlers */
        $_POST['email_reports'] = array_map(
            'intval',
            array_filter($_POST['email_reports'] ?? [], function($notification_handler_id) use ($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );

        /* Prepare settings for JSON insertion */
        $settings = json_encode($settings);

        /* Update the record */
        db()->where('link_id', $link->link_id)->update('links', [
            'email_reports' => json_encode($_POST['email_reports']),
            'email_reports_count' => count($_POST['email_reports']),
            'email_reports_last_datetime' => !$link->email_reports_last_datetime ? get_date() : $link->email_reports_last_datetime,
            'project_id' => $_POST['project_id'],
            'splash_page_id' => $_POST['splash_page_id'],
            'domain_id' => $domain_id,
            'biolink_theme_id' => $_POST['biolink_theme_id'],
            'pixels_ids' => $_POST['pixels_ids'],
            'url' => $url,
            'settings' => $settings,
            'additional' => $additional,
            'directory_is_enabled' => $_POST['directory_is_enabled'],
            'last_datetime' => get_date(),
        ]);

        $this->process_is_main_link_domain($link, $domains);

        $url = $domain_id && $_POST['is_main_link'] ? '' : $url;

        /* Clear the cache */
        cache()->deleteItem('biolink_blocks?link_id=' . $link->link_id);
        cache()->deleteItem('link?link_id=' . $link->link_id);
        cache()->deleteItemsByTag('link_id=' . $link->link_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->link_update) {
            fire_and_forget('post', settings()->webhooks->link_update, [
                'user_id' => $this->user->user_id,
                'link_id' => $_POST['link_id'],
                'domain_id' => $domain_id,
                'url' => $url,
                'full_url' => $domain_id ? $domains[$domain_id]->url . ($domains[$domain_id]->link_id == $_POST['link_id'] ? null : $url) : SITE_URL . $url,
                'type' => 'biolink',
                'datetime' => get_date(),
            ], signature: true);
        }

        Response::json(l('global.success_message.update2'), 'success', [
            'url' => $url,
            'images' => [
                'seo_image' => $image_url['seo_image'],
                'favicon' => $image_url['favicon'],
                'background' => $image_url['background'],
                'pwa_icon' => $image_url['pwa_icon'],
                'branded_button_icon' => $image_url['branded_button_icon'],
            ],
        ]);

    }

    private function update_file() {
        if(!settings()->links->files_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['project_id'] = empty($_POST['project_id']) ? null : (int) $_POST['project_id'];
        $_POST['url'] = !empty($_POST['url']) ? get_slug($_POST['url'], '-', false) : false;
        $_POST['schedule'] = (int) isset($_POST['schedule']);
        if($_POST['schedule'] && !empty($_POST['start_date']) && !empty($_POST['end_date']) && Date::validate($_POST['start_date'], 'Y-m-d H:i:s') && Date::validate($_POST['end_date'], 'Y-m-d H:i:s')) {
            $_POST['start_date'] = (new \DateTime($_POST['start_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
            $_POST['end_date'] = (new \DateTime($_POST['end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
        } else {
            $_POST['start_date'] = $_POST['end_date'] = null;
        }
        $_POST['expiration_url'] = get_url($_POST['expiration_url']);
        $_POST['clicks_limit'] = empty($_POST['clicks_limit']) ? null : (int) $_POST['clicks_limit'];
        $this->check_location_url($_POST['expiration_url'], true);
        $_POST['sensitive_content'] = (int) isset($_POST['sensitive_content']);
        $_POST['force_download_is_enabled'] = (int) isset($_POST['force_download_is_enabled']);
        $_POST['seo_block'] = (int) isset($_POST['seo_block']);

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Authentication::is_admin()) {
            Response::json(l('create_link_modal.error_message.main_domain_is_disabled'), 'error');
        }

        /* Get domains */
        $domains = (new Domain())->get_available_domains_by_user($this->user);

        /* Check if custom domain is set */
        $domain_id = isset($domains[$_POST['domain_id']]) ? $_POST['domain_id'] : 0;

        /* Exclusivity check */
        $_POST['is_main_link'] = isset($_POST['is_main_link']) && $domain_id && $domains[$_POST['domain_id']]->type == 0;

        /* Existing pixels */
        $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);
        $_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map(
            'intval',
            array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
                return array_key_exists($pixel_id, $pixels);
            })
        ) : [];
        $_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

        /* Check for any errors */
        $required_fields = [];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                Response::json(l('global.error_message.empty_fields'), 'error');
                break 1;
            }
        }

        $this->check_url($_POST['url']);

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }

        /* Existing projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

        /* Existing splash pages */
        $splash_pages = (new \Altum\Models\SplashPages())->get_splash_pages_by_user_id($this->user->user_id);
        $_POST['splash_page_id'] = !empty($_POST['splash_page_id']) && array_key_exists($_POST['splash_page_id'], $splash_pages) ? (int) $_POST['splash_page_id'] : null;

        $link->settings = json_decode($link->settings ?? '');

        /* Check for a password set */
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
        $_POST['password'] = !empty($_POST['qweasdzxc']) ?
            ($_POST['qweasdzxc'] != $link->settings->password ? password_hash($_POST['qweasdzxc'], PASSWORD_DEFAULT) : $link->settings->password)
            : null;

        /* Check for duplicate url if needed */
        if($_POST['url'] && ($_POST['url'] != $link->url || $domain_id != $link->domain_id)) {

            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(l('link.error_message.url_exists'), 'error');
            }

        }

        $url = $_POST['url'];

        if(empty($_POST['url'])) {
            /* Generate random url if not specified */
            $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));

            while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
            }
        }

        /* File upload */
        $db_file = \Altum\Uploads::process_upload($link->settings->file, 'files', 'file', 'file_remove', settings()->links->file_size_limit, 'json_error');

        $settings = [
            'file' => $db_file,
            'clicks_limit' => $_POST['clicks_limit'],
            'expiration_url' => $_POST['expiration_url'],
            'schedule' => $_POST['schedule'],
            'password' => $_POST['password'],
            'sensitive_content' => $_POST['sensitive_content'],
            'force_download_is_enabled' => $_POST['force_download_is_enabled'],

            /* SEO */
            'seo' => [
                'block' => $_POST['seo_block'],
            ],
        ];

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Notification handlers */
        $_POST['email_reports'] = array_map(
            'intval',
            array_filter($_POST['email_reports'] ?? [], function($notification_handler_id) use ($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );

        $settings = json_encode($settings);

        db()->where('link_id', $_POST['link_id'])->update('links', [
            'project_id' => $_POST['project_id'],
            'email_reports' => json_encode($_POST['email_reports']),
            'email_reports_count' => count($_POST['email_reports']),
            'email_reports_last_datetime' => !$link->email_reports_last_datetime ? get_date() : $link->email_reports_last_datetime,
            'splash_page_id' => $_POST['splash_page_id'],
            'domain_id' => $domain_id,
            'pixels_ids' => $_POST['pixels_ids'],
            'url' => $url,
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'settings' => $settings,
            'last_datetime' => get_date(),
        ]);

        $this->process_is_main_link_domain($link, $domains);

        $url = $domain_id && $_POST['is_main_link'] ? '' : $url;

        /* Clear the cache */
        cache()->deleteItem('biolink_blocks?link_id=' . $link->link_id);
        cache()->deleteItem('link?link_id=' . $link->link_id);
        cache()->deleteItemsByTag('link_id=' . $link->link_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->link_update) {
            fire_and_forget('post', settings()->webhooks->link_update, [
                'user_id' => $this->user->user_id,
                'link_id' => $_POST['link_id'],
                'domain_id' => $domain_id,
                'url' => $url,
                'full_url' => $domain_id ? $domains[$domain_id]->url . ($domains[$domain_id]->link_id == $_POST['link_id'] ? null : $url) : SITE_URL . $url,
                'type' => 'file',
                'datetime' => get_date(),
            ], signature: true);
        }

        Response::json(l('global.success_message.update2'), 'success', ['url' => $url, 'file' => $db_file, 'file_url' => \Altum\Uploads::get_full_url('files') . $db_file]);
    }

    private function update_static() {
        /* feature check */
        if(!settings()->links->static_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        /* sanitize & normalize inputs */
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['project_id'] = empty($_POST['project_id']) ? null : (int) $_POST['project_id'];
        $_POST['url'] = !empty($_POST['url']) ? get_slug($_POST['url'], '-', false) : false;
        $_POST['schedule'] = (int) isset($_POST['schedule']);

        if($_POST['schedule'] && !empty($_POST['start_date']) && !empty($_POST['end_date']) && Date::validate($_POST['start_date'], 'Y-m-d H:i:s') && Date::validate($_POST['end_date'], 'Y-m-d H:i:s')) {
            $_POST['start_date'] = (new \DateTime($_POST['start_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
            $_POST['end_date'] = (new \DateTime($_POST['end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
        } else {
            $_POST['start_date'] = $_POST['end_date'] = null;
        }

        $_POST['expiration_url'] = get_url($_POST['expiration_url']);
        $_POST['clicks_limit'] = empty($_POST['clicks_limit']) ? null : (int) $_POST['clicks_limit'];
        $this->check_location_url($_POST['expiration_url'], true);
        $_POST['sensitive_content'] = (int) isset($_POST['sensitive_content']);
        $_POST['seo_block'] = (int) isset($_POST['seo_block']);

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Authentication::is_admin()) {
            Response::json(l('create_link_modal.error_message.main_domain_is_disabled'), 'error');
        }

        /* available domains */
        $domains = (new Domain())->get_available_domains_by_user($this->user);
        $domain_id = isset($domains[$_POST['domain_id']]) ? $_POST['domain_id'] : 0;

        /* exclusivity */
        $_POST['is_main_link'] = isset($_POST['is_main_link']) && $domain_id && $domains[$_POST['domain_id']]->type == 0;

        /* pixels */
        $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);
        $_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map(
            'intval',
            array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) { return array_key_exists($pixel_id, $pixels); })
        ) : [];
        $_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

        /* basic validation */
        $required_fields = [];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                Response::json(l('global.error_message.empty_fields'), 'error');
                break 1;
            }
        }

        $this->check_url($_POST['url']);

        /* fetch link */
        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }

        /* projects & splash pages */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

        $splash_pages = (new \Altum\Models\SplashPages())->get_splash_pages_by_user_id($this->user->user_id);
        $_POST['splash_page_id'] = !empty($_POST['splash_page_id']) && array_key_exists($_POST['splash_page_id'], $splash_pages) ? (int) $_POST['splash_page_id'] : null;

        $link->settings = json_decode($link->settings ?? '');
        $link->additional = json_decode($link->additional ?? '{}');

        /* password handling */
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
        $_POST['password'] = !empty($_POST['qweasdzxc'])
            ? ($_POST['qweasdzxc'] != $link->settings->password ? password_hash($_POST['qweasdzxc'], PASSWORD_DEFAULT) : $link->settings->password)
            : null;

        /* duplicate url check */
        if($_POST['url'] && ($_POST['url'] != $link->url || $domain_id != $link->domain_id)) {
            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(l('link.error_message.url_exists'), 'error');
            }
        }

        /* compute final url */
        $url = $_POST['url'];
        if(empty($_POST['url'])) {
            /* Generate random url if not specified */
            $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));

            while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
            }
        }

        /* Get static folder */
        $static_folder_name = null;

        if(isset($link->settings->static_folder) && $link->settings->static_folder) {
            $static_folder_name = $link->settings->static_folder;
        } elseif(isset($link->additional->static_folder) && $link->additional->static_folder) {
            $static_folder_name = $link->additional->static_folder;
        }

        /* file upload handling */
        if(!empty($_FILES['file']['name'])) {
            $uploaded_file_extension = mb_strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $uploaded_file_temp = $_FILES['file']['tmp_name'];

            /* raise limits for large archives */
            set_time_limit(120);

            /* error checks */
            if($_FILES['file']['error'] == UPLOAD_ERR_INI_SIZE) {
                Response::json(sprintf(l('global.error_message.file_size_limit'), get_max_upload()), 'error');
            }
            if($_FILES['file']['error'] && $_FILES['file']['error'] != UPLOAD_ERR_INI_SIZE) {
                Response::json(l('global.error_message.file_upload'), 'error');
            }

            /* type whitelist */
            if(!in_array($uploaded_file_extension, \Altum\Uploads::get_whitelisted_file_extensions('static'))) {
                Response::json(l('global.error_message.invalid_file_type'), 'error');
            }

            /* permissions check when not offloading */
            if(!\Altum\Plugin::is_active('offload') || (\Altum\Plugin::is_active('offload') && !settings()->offload->uploads_url)) {
                if(!is_writable(UPLOADS_PATH . \Altum\Uploads::get_path('static'))) {
                    Response::json(sprintf(l('global.error_message.directory_not_writable'), UPLOADS_PATH . \Altum\Uploads::get_path('static')), 'error');
                }
            }

            /* size limit */
            if(settings()->links->static_size_limit && $_FILES['file']['size'] > settings()->links->static_size_limit * 1000000) {
                Response::json(sprintf(l('global.error_message.file_size_limit'), settings()->links->static_size_limit), 'error');
            }

            /* Cap of amount of files */
            $maximum_files_amount = 1000;

            /* Track files and folders */
            $files = [];
            $folders = [];

            if(!$static_folder_name) {
                $static_folder_name = md5(uniqid('', true) . random_bytes(16));
            }

            /* clear and recreate target */
            remove_directory_and_contents(\Altum\Uploads::get_full_path('static') . $static_folder_name);
            $base_folder = \Altum\Uploads::get_full_path('static') . $static_folder_name;
            mkdir($base_folder, 0777, true);

            /* single html upload */
            if($uploaded_file_extension == 'html') {
                move_uploaded_file($uploaded_file_temp, $base_folder . '/index.html');
                @unlink($uploaded_file_temp);
                $files[] = 'index.html';
            }

            /* zip archive extraction */
            if($uploaded_file_extension == 'zip') {
                $zip = new \ZipArchive;

                if($zip->open($uploaded_file_temp) === true) {
                    /* Too many files */
                    if($zip->numFiles > $maximum_files_amount) {
                        $zip->close();
                        Response::json(l('global.error_message.basic'), 'error');
                    }

                    /* secure base path */
                    $real_base_folder = realpath($base_folder);

                    /* create directories and extract files */
                    for($file_index = 0; $file_index < $zip->numFiles; $file_index++) {
                        $entry_name = $zip->getNameIndex($file_index);
                        $entry_info = $zip->statIndex($file_index);

                        /* skip macos junk and directory entries */
                        if(str_contains($entry_info['name'], '__MACOSX')) { continue; }

                        $is_directory = $entry_info['name'][strlen($entry_info['name']) - 1] == '/';

                        /* normalize relative path and deny zip slip patterns */
                        $relative_path = ltrim(preg_replace('#/+#', '/', $entry_info['name']), '/');
                        if(preg_match('#(^|/)\.\.(?:/|$)#', $relative_path)) {
                            $zip->close();
                            Response::json(l('global.error_message.basic'), 'error');
                        }

                        $target_path = $base_folder . '/' . $relative_path;

                        if($is_directory) {
                            if(!is_dir($target_path)) {
                                mkdir($target_path, 0777, true);
                            }
                            /* continue to next entry */
                            continue;
                        }

                        /* whitelist file extensions for extracted files */
                        $entry_extension = mb_strtolower(pathinfo($entry_name, PATHINFO_EXTENSION));
                        if(!in_array($entry_extension, \Altum\Uploads::$uploads['static']['inside_zip_whitelisted_file_extensions'])) {
                            continue;
                        }

                        /* ensure directory exists */
                        $target_directory = dirname($target_path);
                        if(!is_dir($target_directory)) {
                            mkdir($target_directory, 0777, true);
                        }

                        /* final zip slip guard using realpath on dir */
                        $real_target_dir = realpath($target_directory);
                        if($real_target_dir === false || strpos($real_target_dir, $real_base_folder) !== 0) {
                            $zip->close();
                            Response::json(l('global.error_message.basic'), 'error');
                        }

                        /* extract */
                        copy('zip://' . $uploaded_file_temp . '#' . $entry_name, $target_path);

                        /* keep connection alive on big archives */
                        if($file_index % 100 == 0) {
                            flush();
                            @ob_flush();
                        }
                    }

                    $zip->close();
                    @unlink($uploaded_file_temp);
                } else {
                    Response::json(l('global.error_message.basic'), 'error');
                }
            }
        }

        $link->additional->static_folder = $static_folder_name;
        $additional = json_encode($link->additional);

        /* Settings */
        $settings = json_encode([
            'schedule' => $_POST['schedule'],
            'clicks_limit' => $_POST['clicks_limit'],
            'expiration_url' => $_POST['expiration_url'],
            'password' => $_POST['password'],
            'sensitive_content' => $_POST['sensitive_content'],
            'seo' => [
                'block' => $_POST['seo_block'],
            ],
        ]);

        /* notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);
        $_POST['email_reports'] = array_map(
            'intval',
            array_filter($_POST['email_reports'] ?? [], function($notification_handler_id) use ($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );

        /* Database query */
        db()->where('link_id', $_POST['link_id'])->update('links', [
            'project_id' => $_POST['project_id'],
            'email_reports' => json_encode($_POST['email_reports']),
            'email_reports_count' => count($_POST['email_reports']),
            'email_reports_last_datetime' => !$link->email_reports_last_datetime ? get_date() : $link->email_reports_last_datetime,
            'splash_page_id' => $_POST['splash_page_id'],
            'domain_id' => $domain_id,
            'pixels_ids' => $_POST['pixels_ids'],
            'url' => $url,
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'settings' => $settings,
            'additional' => $additional,
            'last_datetime' => get_date(),
        ]);

        /* process main link exclusivity */
        $this->process_is_main_link_domain($link, $domains);
        $url = $domain_id && $_POST['is_main_link'] ? '' : $url;

        /* Clear the cache */
        cache()->deleteItem('biolink_blocks?link_id=' . $link->link_id);
        cache()->deleteItem('link?link_id=' . $link->link_id);
        cache()->deleteItemsByTag('link_id=' . $link->link_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->link_update) {
            fire_and_forget('post', settings()->webhooks->link_update, [
                'user_id' => $this->user->user_id,
                'link_id' => $_POST['link_id'],
                'domain_id' => $domain_id,
                'url' => $url,
                'full_url' => $domain_id ? $domains[$domain_id]->url . ($domains[$domain_id]->link_id == $_POST['link_id'] ? null : $url) : SITE_URL . $url,
                'type' => 'static',
                'datetime' => get_date(),
            ], signature: true);
        }

        Response::json(l('global.success_message.update2'), 'success', ['url' => $url]);
    }

    private function static_ai_improve() {
        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.links')) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        /* Feature check */
        if(!settings()->links->static_is_enabled || !settings()->links->static_ai_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        /* Required data */
        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['input'] = isset($_POST['input']) ? trim($_POST['input']) : '';

        if(!$_POST['input']) {
            Response::json(l('global.error_message.empty_fields'), 'error');
        }

        /* Check API key */
        if(!settings()->links->openai_api_key) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        /* Check prompt limit */
        if($this->user->plan_settings->ai_static_prompts_characters_limit != -1) {
            $_POST['input'] = mb_substr($_POST['input'], 0, $this->user->plan_settings->ai_static_prompts_characters_limit);
        }

        /* Check plan limit */
        $ai_static_prompts_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`ai_static_prompts_current_month`');
        if($this->user->plan_settings->ai_static_prompts_per_month_limit != -1 && $ai_static_prompts_current_month >= $this->user->plan_settings->ai_static_prompts_per_month_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Get static site */
        $static = $this->get_static_ai_site($_POST['link_id'], false);

        /* Get site context */
        $context = $this->get_static_ai_context($static->base_folder, true);

        if(!count($context['editable_files'])) {
            Response::json(l('link.static.ai_improve_no_files'), 'error');
        }

        set_time_limit(600);

        session_write_close();

        \Unirest\Request::timeout(600);
        \Unirest\Request::verifyPeer(true);

        /* Prepare site manifest */
        $site_manifest = [
            'editable_files' => $context['editable_files'],
            'asset_files' => $context['asset_files'],
        ];

        try {
            $response = \Unirest\Request::post(
                settings()->links->openai_api_url . 'v1/responses',
                [
                    'Authorization' => 'Bearer ' . settings()->links->openai_api_key,
                    'Content-Type' => 'application/json'
                ],
                json_encode([
                    'model' => settings()->links->openai_model,
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'static_site_changes',
                            'strict' => true,

                            'schema' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'required' => ['summary', 'changes'],
                                'properties' => [
                                    'summary' => ['type' => 'string'],
                                    'changes' => [
                                        'type' => 'array',
                                        'minItems' => 1,
                                        'maxItems' => 1000,
                                        'items' => [
                                            'type' => 'object',
                                            'additionalProperties' => false,
                                            'required' => ['action', 'path', 'content'],
                                            'properties' => [
                                                'action' => [
                                                    'type' => 'string',
                                                    'enum' => ['write_file', 'delete_file']
                                                ],
                                                'path' => ['type' => 'string'],
                                                'content' => ['type' => 'string']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'input' => [
                        [
                            'role' => 'developer',
                            'content' => [[
                                'type' => 'input_text',
                                'text' => 'You improve an existing static website as a JSON change manifest only.

Hard rules:
- Return only files that must change.
- Use write_file for creating or replacing text files.
- Use delete_file only when a file is no longer referenced.
- For delete_file, content must be an empty string.
- Only write text files with these extensions: ' . implode(', ', $context['text_extensions']) . '.
- Never create binary files.
- Keep index.html working and present.
- All file paths must be relative, no leading "/" or "\" and must not contain "..".
- No README files. No docs/instructions/license/changelog/notes files.
- No code comments anywhere (no HTML/CSS/JS comments).
- index.html must reference local files using relative paths starting with "./" when possible.
- No external assets/CDNs unless user explicitly asks.
- Browser-safe code only (no eval, no new Function, no document.write, no inline event handlers like onclick="").
- No NULL bytes or invisible control characters in file content.
- Every referenced local asset must exist in either the existing site files or the change manifest.'
                            ]]
                        ],
                        [
                            'role' => 'user',
                            'content' => [[
                                'type' => 'input_text',
                                'text' => 'User requested improvement:
' . $_POST['input'] . '

Existing site JSON:
' . json_encode($site_manifest, JSON_UNESCAPED_SLASHES)
                            ]]
                        ]
                    ]
                ], JSON_UNESCAPED_SLASHES)
            );
        } catch (\Exception $exception) {
            error_log('[AI STATIC IMPROVE ERROR]: ' . $exception->getMessage());
            Response::json(l('global.error_message.basic'), 'error');
        }

        if($response->code >= 400) {
            error_log('[AI STATIC IMPROVE ERROR]: ' . $response->raw_body);
            Response::json(l('global.error_message.basic'), 'error');
        }

        /* Extract AI output JSON */
        $ai_output = $this->get_static_ai_response_text($response);

        if(!$ai_output) {
            error_log('[AI STATIC IMPROVE ERROR]: Missing output_text.');
            Response::json(l('global.error_message.basic'), 'error');
        }

        /* Parse changes */
        $manifest = json_decode($ai_output, true);
        $proposal = $this->validate_static_ai_proposal($manifest, $context['all_files']);

        /* Apply changes */
        $this->apply_static_ai_proposal($static, $proposal);

        /* Track AI usage */
        db()->where('user_id', $this->user->user_id)->update('users', [
            'ai_static_prompts_current_month' => db()->inc()
        ]);

        Response::json(l('global.success_message.update2'), 'success', [
            'static_ai_restore_submit' => l('link.static.ai_restore_submit'),
            'static_ai_restore_help' => l('link.static.ai_restore_help'),
            'static_ai_restore_icon' => 'fa-undo',
        ]);
    }

    private function apply_static_ai_proposal($static, $proposal) {
        /* Remove old backup */
        if(isset($static->link->additional->static_ai_backup_folder) && $static->link->additional->static_ai_backup_folder != $static->static_folder_name && preg_match('/^[a-f0-9]{32}$/', $static->link->additional->static_ai_backup_folder)) {
            remove_directory_and_contents(\Altum\Uploads::get_full_path('static') . $static->link->additional->static_ai_backup_folder);
        }

        /* Save backup */
        $backup_folder_name = md5(uniqid('', true) . random_bytes(16));
        $backup_folder = \Altum\Uploads::get_full_path('static') . $backup_folder_name;
        $this->copy_static_folder($static->base_folder, $backup_folder);

        /* Apply changes */
        foreach($proposal['changes'] as $change) {
            $target_file = $static->base_folder . '/' . $change['path'];

            if($change['action'] == 'delete_file') {
                /* Delete file */
                $real_target_file = realpath($target_file);

                if(!$real_target_file || strpos($real_target_file, $static->real_base_folder) !== 0 || !is_file($real_target_file)) {
                    Response::json(l('global.error_message.basic'), 'error');
                }

                if(!unlink($real_target_file)) {
                    Response::json(l('global.error_message.basic'), 'error');
                }
            } else {
                /* Write file */
                $target_directory = dirname($target_file);

                if(!is_dir($target_directory)) {
                    mkdir($target_directory, 0777, true);
                }

                $real_target_directory = realpath($target_directory);

                if(!$real_target_directory || strpos($real_target_directory, $static->real_base_folder) !== 0) {
                    Response::json(l('global.error_message.basic'), 'error');
                }

                if(file_put_contents($target_file, $change['content']) === false) {
                    Response::json(l('global.error_message.basic'), 'error');
                }
            }
        }

        /* Refresh files data */
        $files = $this->get_static_files_list($static->base_folder);
        $static->link->additional->static_ai_backup_folder = $backup_folder_name;
        $static->link->additional->static_ai_restore_type = 'undo';
        $static->link->additional->static_ai_cache_buster = md5(uniqid('', true) . random_bytes(16));
        $static->link->additional->files = array_keys($files);
        $static->link->additional->total_files = count($files);

        /* Update link */
        db()->where('link_id', $static->link->link_id)->update('links', [
            'additional' => json_encode($static->link->additional),
            'last_datetime' => get_date(),
        ]);

        /* Clear cache */
        cache()->deleteItem('link?link_id=' . $static->link->link_id);
        cache()->deleteItemsByTag('link_id=' . $static->link->link_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);
    }

    private function static_ai_restore() {
        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.links')) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        /* Feature check */
        if(!settings()->links->static_is_enabled || !settings()->links->static_ai_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        /* Required data */
        $_POST['link_id'] = (int) $_POST['link_id'];

        /* Get static site */
        $static = $this->get_static_ai_site($_POST['link_id'], false);

        /* Check backup */
        if(!isset($static->link->additional->static_ai_backup_folder) || $static->link->additional->static_ai_backup_folder == $static->static_folder_name || !preg_match('/^[a-f0-9]{32}$/', $static->link->additional->static_ai_backup_folder)) {
            Response::json(l('link.static.ai_restore_no_backup'), 'error');
        }

        $backup_folder_name = $static->link->additional->static_ai_backup_folder;
        $backup_folder = realpath(\Altum\Uploads::get_full_path('static') . $backup_folder_name);

        if(!$backup_folder || !is_dir($backup_folder)) {
            Response::json(l('link.static.ai_restore_no_backup'), 'error');
        }

        /* Get restore state */
        $static_ai_restore_type = isset($static->link->additional->static_ai_restore_type) && $static->link->additional->static_ai_restore_type == 'redo' ? 'redo' : 'undo';
        $new_static_ai_restore_type = $static_ai_restore_type == 'redo' ? 'undo' : 'redo';

        /* Backup current version */
        $current_backup_folder_name = md5(uniqid('', true) . random_bytes(16));
        $current_backup_folder = \Altum\Uploads::get_full_path('static') . $current_backup_folder_name;
        $this->copy_static_folder($static->base_folder, $current_backup_folder);

        /* Restore backup */
        remove_directory_and_contents($static->base_folder);
        $this->copy_static_folder($backup_folder, $static->base_folder);
        remove_directory_and_contents($backup_folder);

        /* Refresh files data */
        $files = $this->get_static_files_list($static->base_folder);
        $static->link->additional->static_ai_backup_folder = $current_backup_folder_name;
        $static->link->additional->static_ai_restore_type = $new_static_ai_restore_type;
        $static->link->additional->static_ai_cache_buster = md5(uniqid('', true) . random_bytes(16));
        $static->link->additional->files = array_keys($files);
        $static->link->additional->total_files = count($files);

        /* Update link */
        db()->where('link_id', $static->link->link_id)->update('links', [
            'additional' => json_encode($static->link->additional),
            'last_datetime' => get_date(),
        ]);

        /* Clear cache */
        cache()->deleteItem('link?link_id=' . $static->link->link_id);
        cache()->deleteItemsByTag('link_id=' . $static->link->link_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);

        /* Prepare restore button state */
        $message = $static_ai_restore_type == 'redo' ? l('link.static.ai_redo_success') : l('link.static.ai_restore_success');
        $static_ai_restore_submit = $new_static_ai_restore_type == 'redo' ? l('link.static.ai_redo_submit') : l('link.static.ai_restore_submit');
        $static_ai_restore_help = $new_static_ai_restore_type == 'redo' ? l('link.static.ai_redo_help') : l('link.static.ai_restore_help');
        $static_ai_restore_icon = $new_static_ai_restore_type == 'redo' ? 'fa-redo' : 'fa-undo';

        Response::json($message, 'success', [
            'static_ai_restore_submit' => $static_ai_restore_submit,
            'static_ai_restore_help' => $static_ai_restore_help,
            'static_ai_restore_icon' => $static_ai_restore_icon,
        ]);
    }

    private function get_static_ai_site($link_id, $ai_mode_only) {
        /* Get link */
        if(!$link = db()->where('link_id', $link_id)->where('user_id', $this->user->user_id)->where('type', 'static')->getOne('links')) {
            die();
        }

        $link->settings = json_decode($link->settings);
        $link->additional = json_decode($link->additional);

        /* Get folder */
        $static_folder_name = null;

        if(isset($link->settings->static_folder) && $link->settings->static_folder) {
            $static_folder_name = $link->settings->static_folder;
        } elseif(isset($link->additional->static_folder) && $link->additional->static_folder) {
            $static_folder_name = $link->additional->static_folder;
        }

        if(!$static_folder_name) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        $base_folder = \Altum\Uploads::get_full_path('static') . $static_folder_name;
        $real_base_folder = realpath($base_folder);

        /* Validate folder */
        if(!$real_base_folder || !is_dir($real_base_folder)) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        return (object) [
            'link' => $link,
            'static_folder_name' => $static_folder_name,
            'base_folder' => $real_base_folder,
            'real_base_folder' => $real_base_folder,
        ];
    }

    private function get_static_ai_response_text($response) {
        /* Get response text */
        $ai_output = null;

        foreach($response->body->output as $item) {
            $item_type = isset($item->type) ? $item->type : null;

            if($item_type !== 'message') {
                continue;
            }

            $item_content = isset($item->content) ? $item->content : [];

            foreach($item_content as $content) {
                $content_type = isset($content->type) ? $content->type : null;

                if($content_type === 'output_text') {
                    $ai_output = $content->text;
                }
            }
        }

        return $ai_output;
    }

    private function get_static_ai_context($base_folder, $include_content) {
        /* Prepare files */
        $text_extensions = $this->get_static_ai_text_extensions();
        $allowed_extensions = \Altum\Uploads::$uploads['static']['inside_zip_whitelisted_file_extensions'];
        $editable_files = [];
        $asset_files = [];
        $all_files = [];
        $context_bytes = 0;
        $maximum_context_bytes = 750000;

        $files = $this->get_static_files_list($base_folder);

        if(count($files) > 1000) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        foreach($files as $path => $file_size) {
            $file_extension = mb_strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $all_files[$path] = $file_size;

            if(in_array($file_extension, $text_extensions, true)) {
                if($include_content) {
                    /* Check context size */
                    $context_bytes += $file_size;

                    if($context_bytes > $maximum_context_bytes) {
                        Response::json(l('link.static.ai_improve_site_too_large'), 'error');
                    }

                    $content = file_get_contents($base_folder . '/' . $path);

                    if($content === false) {
                        Response::json(l('global.error_message.basic'), 'error');
                    }

                    $editable_files[] = [
                        'path' => $path,
                        'content' => $content,
                    ];
                }
            } elseif(in_array($file_extension, $allowed_extensions, true)) {
                $asset_files[] = [
                    'path' => $path,
                    'size' => $file_size,
                ];
            }
        }

        return [
            'editable_files' => $editable_files,
            'asset_files' => $asset_files,
            'all_files' => $all_files,
            'text_extensions' => $text_extensions,
        ];
    }

    private function get_static_files_list($base_folder) {
        /* Scan files */
        $files = [];
        $base_folder = rtrim($base_folder, '/');
        $base_path_length = strlen($base_folder) + 1;
        $directory_iterator = new \RecursiveDirectoryIterator($base_folder, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_FILEINFO);
        $iterator = new \RecursiveIteratorIterator($directory_iterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach($iterator as $item) {
            if(!$item->isFile()) {
                continue;
            }

            $path = str_replace('\\', '/', substr($item->getPathname(), $base_path_length));
            $files[$path] = $item->getSize();
        }

        ksort($files);

        return $files;
    }

    private function validate_static_ai_proposal($manifest, $current_files) {
        /* Validate manifest */
        if(!is_array($manifest) || !isset($manifest['changes']) || !is_array($manifest['changes'])) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        $summary = isset($manifest['summary']) ? mb_substr((string) $manifest['summary'], 0, 2048) : '';
        $changes = [];
        $changed_paths = [];
        $simulated_files = $current_files;
        $total_bytes = array_sum($current_files);
        $allowed_extensions = \Altum\Uploads::$uploads['static']['inside_zip_whitelisted_file_extensions'];
        $text_extensions = $this->get_static_ai_text_extensions();

        foreach($manifest['changes'] as $change) {
            if(!isset($change['action']) || !isset($change['path']) || !isset($change['content'])) {
                Response::json(l('global.error_message.basic'), 'error');
            }

            $action = (string) $change['action'];

            if(!in_array($action, ['write_file', 'delete_file'], true)) {
                Response::json(l('global.error_message.basic'), 'error');
            }

            $path = $this->normalize_static_ai_path($change['path']);

            if(!$path || isset($changed_paths[$path])) {
                Response::json(l('global.error_message.basic'), 'error');
            }

            $changed_paths[$path] = true;
            $file_extension = mb_strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if(!$file_extension || !in_array($file_extension, $allowed_extensions, true)) {
                Response::json(l('global.error_message.invalid_file_type'), 'error');
            }

            if($action == 'delete_file') {
                if(!isset($current_files[$path])) {
                    Response::json(l('global.error_message.basic'), 'error');
                }

                $total_bytes -= $current_files[$path];
                unset($simulated_files[$path]);

                $changes[] = [
                    'action' => $action,
                    'action_label' => 'delete',
                    'path' => $path,
                    'content' => '',
                ];
            } else {
                if(!in_array($file_extension, $text_extensions, true)) {
                    Response::json(l('global.error_message.invalid_file_type'), 'error');
                }

                /* Clean control characters */
                $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $change['content']);
                $new_size = strlen($content);

                if(isset($simulated_files[$path])) {
                    $total_bytes -= $simulated_files[$path];
                }

                $total_bytes += $new_size;
                $simulated_files[$path] = $new_size;

                $changes[] = [
                    'action' => $action,
                    'action_label' => isset($current_files[$path]) ? 'update' : 'create',
                    'path' => $path,
                    'content' => $content,
                ];
            }
        }

        if(!count($changes)) {
            Response::json(l('link.static.ai_improve_no_changes'), 'error');
        }

        if(!isset($simulated_files['index.html'])) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        if(count($simulated_files) > 1000) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        if(settings()->links->static_size_limit && $total_bytes > settings()->links->static_size_limit * 1000000) {
            Response::json(sprintf(l('global.error_message.file_size_limit'), settings()->links->static_size_limit), 'error');
        }

        return [
            'summary' => $summary,
            'changes' => $changes,
        ];
    }

    private function normalize_static_ai_path($path) {
        /* Normalize path */
        $path = str_replace('\\', '/', (string) $path);
        $path = preg_replace('#/+#', '/', $path);

        if($path === '' || strpos($path, "\0") !== false) {
            return false;
        }

        if($path[0] == '/' || strpos($path, '../') !== false || preg_match('~(^|/)\.\.?(/|$)~', $path)) {
            return false;
        }

        return $path;
    }

    private function get_static_ai_text_extensions() {
        /* Editable files */
        return ['html', 'css', 'js', 'json', 'svg', 'txt', 'xml'];
    }

    private function copy_static_folder($source_folder, $destination_folder) {
        /* Create backup folder */
        if(!is_dir($destination_folder)) {
            mkdir($destination_folder, 0777, true);
        }

        if(!is_dir($destination_folder)) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        $source_folder = rtrim($source_folder, '/');
        $source_path_length = strlen($source_folder) + 1;
        $directory_iterator = new \RecursiveDirectoryIterator($source_folder, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_FILEINFO);
        $iterator = new \RecursiveIteratorIterator($directory_iterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach($iterator as $item) {
            $relative_path = substr($item->getPathname(), $source_path_length);
            $target_path = $destination_folder . '/' . $relative_path;

            if($item->isDir()) {
                if(!is_dir($target_path)) {
                    mkdir($target_path, 0777, true);
                }
            } elseif($item->isFile()) {
                if(!copy($item->getPathname(), $target_path)) {
                    Response::json(l('global.error_message.basic'), 'error');
                }
            }
        }
    }

    private function update_vcard() {
        if(!settings()->links->vcards_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['project_id'] = empty($_POST['project_id']) ? null : (int) $_POST['project_id'];
        $_POST['url'] = !empty($_POST['url']) ? get_slug($_POST['url'], '-', false) : false;
        $_POST['schedule'] = (int) isset($_POST['schedule']);
        if($_POST['schedule'] && !empty($_POST['start_date']) && !empty($_POST['end_date']) && Date::validate($_POST['start_date'], 'Y-m-d H:i:s') && Date::validate($_POST['end_date'], 'Y-m-d H:i:s')) {
            $_POST['start_date'] = (new \DateTime($_POST['start_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
            $_POST['end_date'] = (new \DateTime($_POST['end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
        } else {
            $_POST['start_date'] = $_POST['end_date'] = null;
        }
        $_POST['expiration_url'] = get_url($_POST['expiration_url']);
        $_POST['clicks_limit'] = empty($_POST['clicks_limit']) ? null : (int) $_POST['clicks_limit'];
        $this->check_location_url($_POST['expiration_url'], true);
        $_POST['sensitive_content'] = (int) isset($_POST['sensitive_content']);
        $_POST['seo_block'] = (int) isset($_POST['seo_block']);

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Authentication::is_admin()) {
            Response::json(l('create_link_modal.error_message.main_domain_is_disabled'), 'error');
        }

        /* Get domains */
        $domains = (new Domain())->get_available_domains_by_user($this->user);

        /* Check if custom domain is set */
        $domain_id = isset($domains[$_POST['domain_id']]) ? $_POST['domain_id'] : 0;

        /* Exclusivity check */
        $_POST['is_main_link'] = isset($_POST['is_main_link']) && $domain_id && $domains[$_POST['domain_id']]->type == 0;

        /* Existing pixels */
        $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);
        $_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map(
            'intval',
            array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
                return array_key_exists($pixel_id, $pixels);
            })
        ) : [];
        $_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

        /* Check for any errors */
        $required_fields = [];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                Response::json(l('global.error_message.empty_fields'), 'error');
                break 1;
            }
        }

        $this->check_url($_POST['url']);

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }

        /* Existing projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

        /* Existing splash pages */
        $splash_pages = (new \Altum\Models\SplashPages())->get_splash_pages_by_user_id($this->user->user_id);
        $_POST['splash_page_id'] = !empty($_POST['splash_page_id']) && array_key_exists($_POST['splash_page_id'], $splash_pages) ? (int) $_POST['splash_page_id'] : null;

        $link->settings = json_decode($link->settings ?? '');

        /* Check for a password set */
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
        $_POST['password'] = !empty($_POST['qweasdzxc']) ?
            ($_POST['qweasdzxc'] != $link->settings->password ? password_hash($_POST['qweasdzxc'], PASSWORD_DEFAULT) : $link->settings->password)
            : null;


        /* Check for duplicate url if needed */
        if($_POST['url'] && ($_POST['url'] != $link->url || $domain_id != $link->domain_id)) {

            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(l('link.error_message.url_exists'), 'error');
            }

        }

        $url = $_POST['url'];

        if(empty($_POST['url'])) {
            /* Generate random url if not specified */
            $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));

            while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
            }
        }

        /* File upload */
        $db_vcard_avatar = \Altum\Uploads::process_upload($link->settings->vcard_avatar, 'vcards_avatars', 'vcard_avatar', 'vcard_avatar_remove', 0.75, 'json_error');
        $vcard_avatar_url = $db_vcard_avatar ? \Altum\Uploads::get_full_url('avatars') . $db_vcard_avatar : null;

        $settings = [
            'vcard_avatar' => $db_vcard_avatar,
            'schedule' => $_POST['schedule'],
            'clicks_limit' => $_POST['clicks_limit'],
            'expiration_url' => $_POST['expiration_url'],
            'password' => $_POST['password'],
            'sensitive_content' => $_POST['sensitive_content'],
            /* SEO */
            'seo' => [
                'block' => $_POST['seo_block'],
            ],
        ];

        /* Process vcard */
        $settings['vcard_first_name'] = $_POST['vcard_first_name'] = mb_substr(input_clean($_POST['vcard_first_name']), 0, $this->links_types['vcard']['fields']['first_name']['max_length']);
        $settings['vcard_last_name'] = $_POST['vcard_last_name'] = mb_substr(input_clean($_POST['vcard_last_name']), 0, $this->links_types['vcard']['fields']['last_name']['max_length']);
        $settings['vcard_email'] = $_POST['vcard_email'] = mb_substr(input_clean($_POST['vcard_email']), 0, $this->links_types['vcard']['fields']['email']['max_length']);
        $settings['vcard_url'] = $_POST['vcard_url'] = mb_substr(input_clean($_POST['vcard_url']), 0, $this->links_types['vcard']['fields']['url']['max_length']);
        $settings['vcard_company'] = $_POST['vcard_company'] = mb_substr(input_clean($_POST['vcard_company']), 0, $this->links_types['vcard']['fields']['company']['max_length']);
        $settings['vcard_job_title'] = $_POST['vcard_job_title'] = mb_substr(input_clean($_POST['vcard_job_title']), 0, $this->links_types['vcard']['fields']['job_title']['max_length']);
        $settings['vcard_birthday'] = $_POST['vcard_birthday'] = mb_substr(input_clean($_POST['vcard_birthday']), 0, $this->links_types['vcard']['fields']['birthday']['max_length']);
        $settings['vcard_street'] = $_POST['vcard_street'] = mb_substr(input_clean($_POST['vcard_street']), 0, $this->links_types['vcard']['fields']['street']['max_length']);
        $settings['vcard_city'] = $_POST['vcard_city'] = mb_substr(input_clean($_POST['vcard_city']), 0, $this->links_types['vcard']['fields']['city']['max_length']);
        $settings['vcard_zip'] = $_POST['vcard_zip'] = mb_substr(input_clean($_POST['vcard_zip']), 0, $this->links_types['vcard']['fields']['zip']['max_length']);
        $settings['vcard_region'] = $_POST['vcard_region'] = mb_substr(input_clean($_POST['vcard_region']), 0, $this->links_types['vcard']['fields']['region']['max_length']);
        $settings['vcard_country'] = $_POST['vcard_country'] = mb_substr(input_clean($_POST['vcard_country']), 0, $this->links_types['vcard']['fields']['country']['max_length']);
        $settings['vcard_note'] = $_POST['vcard_note'] = mb_substr(input_clean($_POST['vcard_note']), 0, $this->links_types['vcard']['fields']['note']['max_length']);

        /* Phone numbers */
        if(!isset($_POST['vcard_phone_number_label'])) {
            $_POST['vcard_phone_number_label'] = [];
            $_POST['vcard_phone_number_value'] = [];
        }
        $vcard_phone_numbers = [];
        foreach($_POST['vcard_phone_number_label'] as $key => $value) {
            if($key >= 20) continue;

            $vcard_phone_numbers[] = [
                'label' => mb_substr(input_clean($value), 0, $this->links_types['vcard']['fields']['phone_number_value']['max_length']),
                'value' => mb_substr(input_clean($_POST['vcard_phone_number_value'][$key]), 0, $this->links_types['vcard']['fields']['phone_number_value']['max_length'])
            ];
        }
        $settings['vcard_phone_numbers'] = $vcard_phone_numbers;

        /* Socials */
        if(!isset($_POST['vcard_social_label'])) {
            $_POST['vcard_social_label'] = [];
            $_POST['vcard_social_value'] = [];
        }

        $vcard_socials = [];
        foreach($_POST['vcard_social_label'] as $key => $value) {
            if(empty(trim($value))) continue;
            if($key >= 20) continue;

            $vcard_socials[] = [
                'label' => mb_substr(input_clean($value), 0, $this->links_types['vcard']['fields']['social_value']['max_length']),
                'value' => mb_substr(input_clean($_POST['vcard_social_value'][$key]), 0, $this->links_types['vcard']['fields']['social_value']['max_length'])
            ];
        }
        $settings['vcard_socials'] = $vcard_socials;

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Notification handlers */
        $_POST['email_reports'] = array_map(
            'intval',
            array_filter($_POST['email_reports'] ?? [], function($notification_handler_id) use ($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );

        $settings = json_encode($settings);

        db()->where('link_id', $_POST['link_id'])->update('links', [
            'project_id' => $_POST['project_id'],
            'email_reports' => json_encode($_POST['email_reports']),
            'email_reports_count' => count($_POST['email_reports']),
            'email_reports_last_datetime' => !$link->email_reports_last_datetime ? get_date() : $link->email_reports_last_datetime,
            'splash_page_id' => $_POST['splash_page_id'],
            'domain_id' => $domain_id,
            'pixels_ids' => $_POST['pixels_ids'],
            'url' => $url,
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'settings' => $settings,
            'last_datetime' => get_date(),
        ]);

        $this->process_is_main_link_domain($link, $domains);

        $url = $domain_id && $_POST['is_main_link'] ? '' : $url;

        /* Clear the cache */
        cache()->deleteItem('biolink_blocks?link_id=' . $link->link_id);
        cache()->deleteItem('link?link_id=' . $link->link_id);
        cache()->deleteItemsByTag('link_id=' . $link->link_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->link_update) {
            fire_and_forget('post', settings()->webhooks->link_update, [
                'user_id' => $this->user->user_id,
                'link_id' => $_POST['link_id'],
                'domain_id' => $domain_id,
                'url' => $url,
                'full_url' => $domain_id ? $domains[$domain_id]->url . ($domains[$domain_id]->link_id == $_POST['link_id'] ? null : $url) : SITE_URL . $url,
                'type' => 'vcard',
                'datetime' => get_date(),
            ], signature: true);
        }

        Response::json(l('global.success_message.update2'), 'success', ['url' => $url, 'images' => ['vcard_avatar' => $vcard_avatar_url]]);
    }

    private function update_event() {
        if(!settings()->links->events_is_enabled) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        $_POST['link_id'] = (int) $_POST['link_id'];
        $_POST['project_id'] = empty($_POST['project_id']) ? null : (int) $_POST['project_id'];
        $_POST['url'] = !empty($_POST['url']) ? get_slug($_POST['url'], '-', false) : false;
        $_POST['schedule'] = (int) isset($_POST['schedule']);
        if($_POST['schedule'] && !empty($_POST['start_date']) && !empty($_POST['end_date']) && Date::validate($_POST['start_date'], 'Y-m-d H:i:s') && Date::validate($_POST['end_date'], 'Y-m-d H:i:s')) {
            $_POST['start_date'] = (new \DateTime($_POST['start_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
            $_POST['end_date'] = (new \DateTime($_POST['end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
        } else {
            $_POST['start_date'] = $_POST['end_date'] = null;
        }
        $_POST['expiration_url'] = get_url($_POST['expiration_url']);
        $_POST['clicks_limit'] = empty($_POST['clicks_limit']) ? null : (int) $_POST['clicks_limit'];
        $this->check_location_url($_POST['expiration_url'], true);
        $_POST['sensitive_content'] = (int) isset($_POST['sensitive_content']);
        $_POST['seo_block'] = (int) isset($_POST['seo_block']);

        if(empty($_POST['domain_id']) && !settings()->links->main_domain_is_enabled && !\Altum\Authentication::is_admin()) {
            Response::json(l('create_link_modal.error_message.main_domain_is_disabled'), 'error');
        }

        /* Get domains */
        $domains = (new Domain())->get_available_domains_by_user($this->user);

        /* Check if custom domain is set */
        $domain_id = isset($domains[$_POST['domain_id']]) ? $_POST['domain_id'] : 0;

        /* Exclusivity check */
        $_POST['is_main_link'] = isset($_POST['is_main_link']) && $domain_id && $domains[$_POST['domain_id']]->type == 0;

        /* Existing pixels */
        $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);
        $_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map(
            'intval',
            array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
                return array_key_exists($pixel_id, $pixels);
            })
        ) : [];
        $_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

        /* Check for any errors */
        $required_fields = [];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                Response::json(l('global.error_message.empty_fields'), 'error');
                break 1;
            }
        }

        $this->check_url($_POST['url']);

        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links')) {
            die();
        }

        /* Existing projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

        /* Existing splash pages */
        $splash_pages = (new \Altum\Models\SplashPages())->get_splash_pages_by_user_id($this->user->user_id);
        $_POST['splash_page_id'] = !empty($_POST['splash_page_id']) && array_key_exists($_POST['splash_page_id'], $splash_pages) ? (int) $_POST['splash_page_id'] : null;

        $link->settings = json_decode($link->settings ?? '');

        /* Check for a password set */
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
        $_POST['password'] = !empty($_POST['qweasdzxc']) ?
            ($_POST['qweasdzxc'] != $link->settings->password ? password_hash($_POST['qweasdzxc'], PASSWORD_DEFAULT) : $link->settings->password)
            : null;


        /* Check for duplicate url if needed */
        if($_POST['url'] && ($_POST['url'] != $link->url || $domain_id != $link->domain_id)) {

            if(db()->where('url', $_POST['url'])->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                Response::json(l('link.error_message.url_exists'), 'error');
            }

        }

        $url = $_POST['url'];

        if(empty($_POST['url'])) {
            /* Generate random url if not specified */
            $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));

            while(db()->where('url', $url)->where('domain_id', $domain_id)->getValue('links', 'link_id')) {
                $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
            }
        }

        $settings = [
            'schedule' => $_POST['schedule'],
            'clicks_limit' => $_POST['clicks_limit'],
            'expiration_url' => $_POST['expiration_url'],
            'password' => $_POST['password'],
            'sensitive_content' => $_POST['sensitive_content'],
            /* SEO */
            'seo' => [
                'block' => $_POST['seo_block'],
            ],
        ];

        /* Process event */
        $settings['event_name'] = $_POST['event_name'] = mb_substr(input_clean($_POST['event_name']), 0, $this->links_types['event']['fields']['name']['max_length']);
        $settings['event_location'] = $_POST['event_location'] = mb_substr(input_clean($_POST['event_location']), 0, $this->links_types['event']['fields']['location']['max_length']);
        $settings['event_url'] = $_POST['event_url'] = mb_substr(input_clean($_POST['event_url']), 0, $this->links_types['event']['fields']['url']['max_length']);
        $settings['event_note'] = $_POST['event_note'] = mb_substr(input_clean($_POST['event_note']), 0, $this->links_types['event']['fields']['note']['max_length']);
        $settings['event_timezone'] = $_POST['event_timezone'] = in_array($_POST['event_timezone'], \DateTimeZone::listIdentifiers()) ? input_clean($_POST['event_timezone']) : Date::$default_timezone;
        try {
            $settings['event_start_datetime'] = $_POST['event_start_datetime'] = (new \DateTime($_POST['event_start_datetime']))->format('Y-m-d\TH:i:s');
            $settings['event_end_datetime'] = $_POST['event_end_datetime'] = (new \DateTime($_POST['event_end_datetime']))->format('Y-m-d\TH:i:s');
            $settings['event_first_alert_datetime'] = $_POST['event_first_alert_datetime'] = (new \DateTime($_POST['event_first_alert_datetime']))->format('Y-m-d\TH:i:s');
            $settings['event_second_alert_datetime'] = $_POST['event_second_alert_datetime'] = (new \DateTime($_POST['event_second_alert_datetime']))->format('Y-m-d\TH:i:s');
        } catch (\Exception $exception) {
            /* :) */
        }

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Notification handlers */
        $_POST['email_reports'] = array_map(
            'intval',
            array_filter($_POST['email_reports'] ?? [], function($notification_handler_id) use ($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );

        $settings = json_encode($settings);

        db()->where('link_id', $_POST['link_id'])->update('links', [
            'project_id' => $_POST['project_id'],
            'email_reports' => json_encode($_POST['email_reports']),
            'email_reports_count' => count($_POST['email_reports']),
            'email_reports_last_datetime' => !$link->email_reports_last_datetime ? get_date() : $link->email_reports_last_datetime,
            'splash_page_id' => $_POST['splash_page_id'],
            'domain_id' => $domain_id,
            'pixels_ids' => $_POST['pixels_ids'],
            'url' => $url,
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'settings' => $settings,
            'last_datetime' => get_date(),
        ]);

        $this->process_is_main_link_domain($link, $domains);

        $url = $domain_id && $_POST['is_main_link'] ? '' : $url;

        /* Clear the cache */
        cache()->deleteItem('biolink_blocks?link_id=' . $link->link_id);
        cache()->deleteItem('link?link_id=' . $link->link_id);
        cache()->deleteItemsByTag('link_id=' . $link->link_id);
        cache()->deleteItem('links?user_id=' . $this->user->user_id);

        /* Send webhook notification if needed */
        if(settings()->webhooks->link_update) {
            fire_and_forget('post', settings()->webhooks->link_update, [
                'user_id' => $this->user->user_id,
                'link_id' => $_POST['link_id'],
                'domain_id' => $domain_id,
                'url' => $url,
                'full_url' => $domain_id ? $domains[$domain_id]->url . ($domains[$domain_id]->link_id == $_POST['link_id'] ? null : $url) : SITE_URL . $url,
                'type' => 'event',
                'datetime' => get_date(),
            ], signature: true);
        }

        Response::json(l('global.success_message.update2'), 'success', ['url' => $url]);
    }

    private function delete() {
        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.links')) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        $_POST['link_id'] = (int) $_POST['link_id'];

        /* Check for possible errors */
        if(!$link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links', ['link_id', 'type'])) {
            die();
        }

        (new \Altum\Models\Link())->delete($link->link_id, $this->user->user_id);

        Response::json(l('global.success_message.delete2'), 'success', ['url' => url('links?type=' . $link->type)]);
    }

    public function duplicate() {
        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.links')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('links');
        }

        $_POST['link_id'] = (int) $_POST['link_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('links');
        }

        /* Get the link data */
        $link = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->getOne('links');

        if(!$link) {
            redirect('links');
        }

        /* Check for the plan limit */
        if($link->type == 'link') {
            if(!settings()->links->shortener_is_enabled) {
                Response::json(l('global.error_message.basic'), 'error');
            }

            $user_total_links = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} AND `type` = 'link'")->fetch_object()->total;
            if($this->user->plan_settings->links_limit != -1 && $user_total_links >= $this->user->plan_settings->links_limit) {
                Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            }
        }

        elseif($link->type == 'biolink') {
            if(!settings()->links->biolinks_is_enabled) {
                Response::json(l('global.error_message.basic'), 'error');
            }

            $user_total_biolinks = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} AND `type` = 'biolink'")->fetch_object()->total;
            if($this->user->plan_settings->biolinks_limit != -1 && $user_total_biolinks >= $this->user->plan_settings->biolinks_limit) {
                Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            }
        }

        elseif($link->type == 'file') {
            if(!settings()->links->files_is_enabled) {
                Response::json(l('global.error_message.basic'), 'error');
            }

            $user_total_files = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} AND `type` = 'file'")->fetch_object()->total;
            if($this->user->plan_settings->files_limit != -1 && $user_total_files >= $this->user->plan_settings->files_limit) {
                Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            }
        }

        elseif($link->type == 'vcard') {
            if(!settings()->links->vcards_is_enabled) {
                Response::json(l('global.error_message.basic'), 'error');
            }

            $user_total_vcards = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} AND `type` = 'vcard'")->fetch_object()->total;
            if($this->user->plan_settings->vcards_limit != -1 && $user_total_vcards >= $this->user->plan_settings->vcards_limit) {
                Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            }
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Duplicate the link */
            $link->settings = json_decode($link->settings ?? '');

            if($link->type == 'biolink') {
				$link->settings->password_seo_image = \Altum\Uploads::copy_uploaded_file($link->settings->password_seo_image, 'block_images/', 'block_images/', 'json_error');
                $link->settings->seo->image = \Altum\Uploads::copy_uploaded_file($link->settings->seo->image, 'block_images/', 'block_images/', 'json_error');
                $link->settings->favicon = \Altum\Uploads::copy_uploaded_file($link->settings->favicon, 'favicons/', 'favicons/', 'json_error');
                if($link->settings->background_type == 'image' && !$link->biolink_theme_id) $link->settings->background = \Altum\Uploads::copy_uploaded_file($link->settings->background, 'backgrounds/', 'backgrounds/', 'json_error');
                $link->settings->pwa_icon = \Altum\Uploads::copy_uploaded_file($link->settings->pwa_icon, 'pwa/', 'pwa/', 'json_error');
                $link->settings->branded_button_icon = \Altum\Uploads::copy_uploaded_file($link->settings->branded_button_icon, \Altum\Uploads::get_path('branded_button_icon'), \Altum\Uploads::get_path('branded_button_icon'), 'json_error');
                $link->settings->pwa_is_enabled = false;
                $link->settings->service_worker = false;
            }

            if($link->type == 'vcard') {
                $link->settings->vcard_avatar = \Altum\Uploads::copy_uploaded_file($link->settings->vcard_avatar, \Altum\Uploads::get_path('vcards_avatars'), \Altum\Uploads::get_path('vcards_avatars'), 'json_error');
            }

            if($link->type == 'file') {
                $link->settings->file = \Altum\Uploads::copy_uploaded_file($link->settings->file, \Altum\Uploads::get_path('files'), \Altum\Uploads::get_path('files'), 'json_error');
            }

            /* Generate random url if not specified */
            $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
            while (db()->where('url', $url)->where('domain_id', $link->domain_id)->getValue('links', 'link_id')) {
                $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
            }

            /* Unique access token */
            $token = bin2hex(random_bytes(16));

            /* Database query */
            $link_id = db()->insert('links', [
                'user_id' => $this->user->user_id,
                'project_id' => $link->project_id,
                'token' => $token,
                'email_reports' => $link->email_reports,
                'email_reports_last_datetime' => $link->email_reports_last_datetime,
                'biolink_theme_id' => $link->biolink_theme_id,
                'domain_id' => $link->domain_id,
                'pixels_ids' => $link->pixels_ids,
                'type' => $link->type,
                'url' => $url,
                'location_url' => $link->location_url,
                'settings' => json_encode($link->settings),
                'additional' => $link->additional ?? '',
                'start_date' => $link->start_date,
                'end_date' => $link->end_date,
                'is_verified' => 0,
                'is_enabled' => $link->is_enabled,
                'datetime' => get_date(),
            ]);

            /* Duplicate the biolink blocks */
            if($link->type == 'biolink') {
                /* Get all biolink blocks if needed */
                $biolink_blocks = db()->where('link_id', $_POST['link_id'])->where('user_id', $this->user->user_id)->get('biolinks_blocks');

                foreach($biolink_blocks as $biolink_block) {
                    $biolink_block->settings = json_decode($biolink_block->settings ?? '');

                    if(is_array($biolink_block->settings)) {
                        $biolink_block->settings = (object) $biolink_block->settings;
                    }

                    /* Duplication of resources */
                    switch($biolink_block->type) {
                        case 'file':
                        case 'audio':
                        case 'video':
                        case 'pdf_document':
                        case 'powerpoint_presentation':
                        case 'excel_spreadsheet':
                            $biolink_block->settings->file = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->file, \Altum\Uploads::get_path('files'), \Altum\Uploads::get_path('files'), 'json_error');
                            if(in_array($biolink_block->type, ['file', 'pdf_document', 'powerpoint_presentation', 'excel_spreadsheet'])) {
                                $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image ?? null, \Altum\Uploads::get_path('block_thumbnail_images'), \Altum\Uploads::get_path('block_thumbnail_images'), 'json_error');
                            }
                            break;

                        case 'review':
                        case 'featured_link':
                            $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image, \Altum\Uploads::get_path('block_images'), \Altum\Uploads::get_path('block_images'), 'json_error');
                            break;

                        case 'avatar':
                            $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image, 'avatars/', 'avatars/', 'json_error');
                            break;

                        case 'header':
                            $biolink_block->settings->avatar = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->avatar, 'avatars/', 'avatars/', 'json_error');
                            $biolink_block->settings->background = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->background, 'backgrounds/', 'backgrounds/', 'json_error');
                            $biolink_block->settings->video_file = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->video_file, 'files/', 'files/', 'json_error');
                            break;

                        case 'vcard':
                            $biolink_block->settings->vcard_avatar = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->vcard_avatar, 'avatars/', 'avatars/', 'json_error');
                            $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image, 'block_thumbnail_images/', 'block_thumbnail_images/', 'json_error');
                            break;

                        case 'product':
                            $biolink_block->settings->file = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->file, \Altum\Uploads::get_path('products_files'), \Altum\Uploads::get_path('products_files'), 'json_error');
                            $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image, 'block_thumbnail_images/', 'block_thumbnail_images/', 'json_error');
                            break;

                        case 'image':
                        case 'image_grid':
                            $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image, 'block_images/', 'block_images/', 'json_error');
                            break;

                        case 'image_comparison':
                            $biolink_block->settings->before_image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->before_image, 'block_images/', 'block_images/', 'json_error');
                            $biolink_block->settings->after_image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->after_image, 'block_images/', 'block_images/', 'json_error');
                            break;

                        case 'heading':
                            $biolink_block->settings->verified_location = '';
                            break;

                        case 'image_slider':
                            $biolink_block->settings->items = (array) $biolink_block->settings->items;
                            foreach($biolink_block->settings->items as $key => $item) {
                                $biolink_block->settings->items[$key]->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->items[$key]->image, 'block_images/', 'block_images/', 'json_error');
                            }

                            break;

                        default:
                            $biolink_block->settings->image = \Altum\Uploads::copy_uploaded_file($biolink_block->settings->image, 'block_thumbnail_images/', 'block_thumbnail_images/', 'json_error');
                            break;
                    }

                    /* Database query */
                    db()->insert('biolinks_blocks', [
                        'user_id' => $this->user->user_id,
                        'link_id' => $link_id,
                        'type' => $biolink_block->type,
                        'location_url' => $biolink_block->location_url,
                        'settings' => json_encode($biolink_block->settings),
                        'order' => $biolink_block->order,
                        'start_date' => $biolink_block->start_date,
                        'end_date' => $biolink_block->end_date,
                        'is_enabled' => $biolink_block->is_enabled,
                        'datetime' => get_date(),
                    ]);
                }
            }

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.create2'));

            /* Redirect */
            redirect('link/' . $link_id);
        }

        redirect('links');
    }


    /* Function to bundle together all the checks of a custom url */
    private function check_url($url) {
        if($url) {
            /* Make sure the url alias is not blocked by a route of the product */
            if(array_key_exists($url, \Altum\Router::$routes['']) || in_array($url, \Altum\Language::$active_languages) || file_exists(ROOT_PATH . $url)) {
                Response::json(l('link.error_message.blacklisted_url'), 'error');
            }

            /* Make sure the custom url is not blacklisted */
            if(in_array(mb_strtolower($url), settings()->links->blacklisted_keywords)) {
                Response::json(l('link.error_message.blacklisted_keyword'), 'error');
            }

            /* Make sure the custom url meets the requirements */
            if(mb_strlen($url) < ($this->user->plan_settings->url_minimum_characters ?? 1)) {
                Response::json(sprintf(l('link.error_message.url_minimum_characters'), $this->user->plan_settings->url_minimum_characters ?? 1), 'error');
            }

            if(mb_strlen($url) > ($this->user->plan_settings->url_maximum_characters ?? 64)) {
                Response::json(sprintf(l('link.error_message.url_maximum_characters'), $this->user->plan_settings->url_maximum_characters ?? 64), 'error');
            }
        }
    }

    /* Function to bundle together all the checks of an url */
    private function check_location_url($url, $can_be_empty = false) {

        if(empty(trim($url)) && $can_be_empty) {
            return;
        }

        if(empty(trim($url))) {
            Response::json(l('global.error_message.empty_fields'), 'error');
        }

        $url_details = parse_url($url);

        if(!isset($url_details['scheme'])) {
            Response::json(l('link.error_message.invalid_location_url'), 'error');
        }

        if(!$this->user->plan_settings->deep_links && !in_array($url_details['scheme'], ['http', 'https'])) {
            Response::json(l('link.error_message.invalid_location_url'), 'error');
        }

        /* Make sure the domain is not blacklisted */
        $domain = get_domain_from_host(get_domain_from_url($url));

        if($domain && in_array($domain, settings()->links->blacklisted_domains)) {
            Response::json(l('link.error_message.blacklisted_domain'), 'error');
        }

        /* Check the url with google safe browsing to make sure it is a safe website */
        if(settings()->links->google_safe_browsing_is_enabled) {
            if(google_safe_browsing_check($url, settings()->links->google_safe_browsing_api_key)) {
                Response::json(l('link.error_message.blacklisted_location_url'), 'error');
            }
        }
    }

    private function process_is_main_link_domain($link, $domains) {
        /* Update custom domain if needed */
        if($_POST['is_main_link']) {

            /* If the main status page of a particular domain is changing, update the old domain as well to "free" it */
            if($_POST['domain_id'] != $link->domain_id) {
                /* Database query */
                db()->where('domain_id', $link->domain_id)->update('domains', [
                    'link_id' => null,
                    'last_datetime' => get_date(),
                ]);
            }

            /* Database query */
            db()->where('domain_id', $_POST['domain_id'])->update('domains', [
                'link_id' => $link->link_id,
                'last_datetime' => get_date(),
            ]);

            /* Clear the cache */
            cache()->deleteItems([
                'domains?user_id=' . $this->user->user_id,
                'domain?domain_id=' . $link->domain_id,
                'domain?domain_id=' . $_POST['domain_id'],
                'domain?host=' . md5($domains[$link->domain_id]->host ?? ''),
                'domain?host=' . md5($domains[$_POST['domain_id']]->host ?? ''),
            ]);
            cache()->deleteItemsByTag('domains?user_id=' . $this->user->user_id);
        }

        /* Update old main custom domain if needed */
        if(!$_POST['is_main_link'] && $link->domain_id && $domains[$link->domain_id]->link_id == $link->link_id) {
            /* Database query */
            db()->where('domain_id', $link->domain_id)->update('domains', [
                'link_id' => null,
                'last_datetime' => get_date(),
            ]);

            /* Clear the cache */
            cache()->deleteItems([
                'domains?user_id=' . $this->user->user_id,
                'domain?domain_id=' . $link->domain_id,
                'domain?domain_id=' . $_POST['domain_id'],
                'domain?host=' . md5($domains[$link->domain_id]->host ?? ''),
                'domain?host=' . md5($domains[$_POST['domain_id']]->host ?? ''),
            ]);
            cache()->deleteItemsByTag('domains?user_id=' . $this->user->user_id);
        }
    }
}
