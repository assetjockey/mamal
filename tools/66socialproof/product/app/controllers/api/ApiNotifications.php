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

use Altum\Date;
use Altum\Notification;
use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiNotifications extends Controller {
    use Apiable;

    public function index() {

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                /* Detect if we only need an object, or the whole list */
                if(isset($this->params[0])) {
                    $this->get();
                } else {
                    $this->get_all();
                }

                break;

            case 'POST':

                /* Detect what method to use */
                if(isset($this->params[0])) {
                    $this->patch();
                } else {
                    $this->post();
                }

                break;

            case 'DELETE':
                $this->delete();
                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'type'], ['name'], ['notification_id', 'last_datetime', 'datetime', 'name', 'impressions', 'hovers', 'clicks', 'form_submissions'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->notifications_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `notifications` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/notifications?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `notifications`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->notification_id,
                'user_id' => (int) $row->user_id,
                'campaign_id' => (int) $row->campaign_id,
                'notification_key' => $row->notification_key,
                'name' => $row->name,
                'type' => $row->type,
                'settings' => json_decode($row->settings ?? ''),
                'impressions' => (int) $row->impressions,
                'hovers' => (int) $row->hovers,
                'clicks' => (int) $row->clicks,
                'form_submissions' => (int) $row->form_submissions,
                'is_enabled' => (bool) $row->is_enabled,
                'last_datetime' => $row->last_datetime,
                'datetime' => $row->datetime,
            ];

            $data[] = $row;
        }

        /* Prepare the data */
        $meta = [
            'page' => $_GET['page'] ?? 1,
            'total_pages' => $paginator->getNumPages(),
            'results_per_page' => $filters->get_results_per_page(),
            'total_results' => (int) $total_rows,
        ];

        /* Prepare the pagination notifications */
        $others = ['notifications' => [
            'first' => $paginator->getPageUrl(1),
            'last' => $paginator->getNumPages() ? $paginator->getPageUrl($paginator->getNumPages()) : null,
            'next' => $paginator->getNextUrl(),
            'prev' => $paginator->getPrevUrl(),
            'self' => $paginator->getPageUrl($_GET['page'] ?? 1)
        ]];

        Response::jsonapi_success($data, $meta, 200, $others);
    }

    private function get() {

        $notification_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $notification = db()->where('notification_id', $notification_id)->where('user_id', $this->user->user_id)->getOne('notifications');

        /* We haven't found the resource */
        if(!$notification) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $notification->notification_id,
            'user_id' => (int) $notification->user_id,
            'campaign_id' => (int) $notification->campaign_id,
            'notification_key' => $notification->notification_key,
            'name' => $notification->name,
            'type' => $notification->type,
            'settings' => json_decode($notification->settings),
            'impressions' => (int) $notification->impressions,
            'hovers' => (int) $notification->hovers,
            'clicks' => (int) $notification->clicks,
            'form_submissions' => (int) $notification->form_submissions,
            'is_enabled' => (bool) $notification->is_enabled,
            'last_datetime' => $notification->last_datetime,
            'datetime' => $notification->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for any errors */
        $required_fields = ['type', 'campaign_id'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['type'] = isset($_POST['type']) ? $_POST['type'] : null;
        $_POST['campaign_id'] = isset($_POST['campaign_id']) ? (int) $_POST['campaign_id'] : null;

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `notifications` WHERE `user_id` = {$this->user->user_id} AND `campaign_id` = {$_POST['campaign_id']}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->notifications_limit != -1 && $total_rows >= $this->user->plan_settings->notifications_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        $enabled_notifications = require APP_PATH . 'includes/enabled_notifications.php';

        /* If the notification settings is not set it means we got an invalid type */
        if(!isset($enabled_notifications[$_POST['type']])) {
            $this->response_error(l('global.error_message.basic'), 401);
        }

        /* Check for possible errors */
        if(!db()->where('campaign_id', $_POST['campaign_id'])->where('user_id', $this->user->user_id)->getValue('campaigns', 'campaign_id')) {
            $this->response_error(l('global.error_message.basic'), 401);
        }

        /* Check for permission of usage of the notification */
        $plan_enabled_notifications = $this->user->plan_settings->enabled_notifications;
        $notification_has_direct_access = property_exists($plan_enabled_notifications, $_POST['type']);
        $notification_is_enabled = $notification_has_direct_access ? $plan_enabled_notifications->{$_POST['type']} : false;

        if(!$notification_is_enabled) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Determine the default settings */
        $notification_settings = $enabled_notifications[$_POST['type']];
        $notification_key = md5($this->user->user_id . $_POST['campaign_id'] . $_POST['type'] . time());
        $name = isset($_POST['name']) && !empty($_POST['name']) ? input_clean($_POST['name'], 256) : generate_prefilled_dynamic_names(l('notifications.notification'));
        $_POST['is_enabled'] = isset($_POST['is_enabled']) ? (int) $_POST['is_enabled'] : 0;

        /* Database query */
        $notification_id = db()->insert('notifications', [
            'user_id' => $this->user->user_id,
            'campaign_id' => $_POST['campaign_id'],
            'name' => $name,
            'type' => $_POST['type'],
            'settings' => json_encode($notification_settings),
            'notification_key' => $notification_key,
            'impressions' => (int) 0,
            'hovers' => (int) 0,
            'clicks' => (int) 0,
            'form_submissions' => (int) 0,
            'is_enabled' => $_POST['is_enabled'],
            'datetime' => get_date(),
        ]);

        $notification_settings['custom_css'] = sprintf(l('global.custom_css_placeholder'), $notification_id);

        db()->where('notification_id', $notification_id)->update('notifications', [
            'settings' => json_encode($notification_settings),
        ]);

        /* Clear the cache */
        cache()->deleteItem('notifications_total?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $notification_id,
            'user_id' => (int) $this->user->user_id,
            'campaign_id' => $_POST['campaign_id'],
            'notification_key' => $notification_key,
            'name' => $name,
            'type' => $_POST['type'],
            'settings' => $notification_settings,
            'impressions' => 0,
            'hovers' => 0,
            'clicks' => 0,
            'form_submissions' => 0,
            'is_enabled' => $_POST['is_enabled'],
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $notification_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $notification = db()->where('notification_id', $notification_id)->where('user_id', $this->user->user_id)->getOne('notifications');

        /* We haven't found the resource */
        if(!$notification) {
            $this->return_404();
        }

        $notification->settings = json_decode($notification->settings ?? '');

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `notifications` WHERE `user_id` = {$this->user->user_id} AND `campaign_id` = {$notification->campaign_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->notifications_limit != -1 && $total_rows > $this->user->plan_settings->notifications_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->notifications_limit, mb_strtolower(l('notifications.title')), l('global.info_message.plan_upgrade')), 401);
        }

        /* Start the processing */
        $name = !empty($_POST['name']) ? input_clean($_POST['name'], 256) : $notification->name;
        $_POST['is_enabled'] = isset($_POST['is_enabled']) ? (int) $_POST['is_enabled'] : $notification->is_enabled;

        /* Trigger */
        $_POST['trigger_all_pages'] =  (int) (bool) ($_POST['trigger_all_pages'] ?? $notification->settings->trigger_all_pages);
        $_POST['display_trigger'] = isset($_POST['display_trigger']) && in_array($_POST['display_trigger'], [
            'delay',
            'time_on_site',
            'pageviews',
            'inactivity',
            'exit_intent',
            'scroll',
            'target_visible',
            'click',
            'hover',
        ]) ? $_POST['display_trigger'] : $notification->settings->display_trigger;
        $_POST['display_trigger_value'] = $_POST['display_trigger_value'] ?? $notification->settings->display_trigger_value;
        $_POST['display_trigger_value'] = in_array($_POST['display_trigger'], ['delay', 'time_on_site', 'pageviews', 'inactivity', 'exit_intent', 'scroll', 'target_visible']) ? (int) $_POST['display_trigger_value'] : input_clean($_POST['display_trigger_value']);
        $_POST['display_trigger_selector'] = isset($_POST['display_trigger_selector']) ? html_entity_decode(input_clean($_POST['display_trigger_selector'], 256), ENT_QUOTES | ENT_HTML5, 'UTF-8') : ($notification->settings->display_trigger_selector ?? (in_array($notification->type, ['inline_informational', 'inline_conversions', 'inline_reviews', 'inline_rating_summary', 'inline_conversions_counter', 'inline_live_counter']) ? ($notification->settings->selector ?? '') : ''));

        $_POST['display_delay_type_after_close'] = isset($_POST['display_delay_type_after_close']) && in_array($_POST['display_delay_type_after_close'], ['time_on_site', 'pageviews',]) ? $_POST['display_delay_type_after_close'] : $notification->settings->display_delay_type_after_close;
        $_POST['display_delay_value_after_close'] = (int) ($_POST['display_delay_value_after_close'] ?? $notification->settings->display_delay_value_after_close);

        $_POST['display_frequency'] = isset($_POST['display_frequency']) && in_array($_POST['display_frequency'], [
            'all_time',
            'once_per_session',
            'once_per_browser',
        ]) ? $_POST['display_frequency'] : $notification->settings->display_frequency;
        $_POST['direction'] = isset($_POST['direction']) && in_array($_POST['direction'], ['rtl', 'ltr']) ? $_POST['direction'] : $notification->settings->direction;

        /* Targeting */
        $_POST['display_continents'] = array_filter($_POST['display_continents'] ?? $notification->settings->display_continents, function($country) {
            return array_key_exists($country, get_continents_array());
        });

        $_POST['display_countries'] = array_filter($_POST['display_countries'] ?? $notification->settings->display_countries, function($country) {
            return array_key_exists($country, get_countries_array());
        });

        $_POST['display_languages'] = array_filter($_POST['display_languages'] ?? $notification->settings->display_languages, function($locale) {
            return array_key_exists($locale, get_locale_languages_array());
        });

        $_POST['display_operating_systems'] = array_filter($_POST['display_operating_systems'] ?? $notification->settings->display_operating_systems, function($os_name) {
            return in_array($os_name, ['iOS', 'Android', 'Windows', 'OS X', 'Linux', 'Ubuntu', 'Chrome OS']);
        });

        $_POST['display_browsers'] = array_filter($_POST['display_browsers'] ?? $notification->settings->display_browsers, function($browser_name) {
            return in_array($browser_name, ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Samsung Internet']);
        });

        $_POST['display_cities'] = isset($_POST['display_cities']) ? explode(',', $_POST['display_cities']) : $notification->settings->display_cities;
        if(count($_POST['display_cities'] ?? [])) {
            $_POST['display_cities'] = array_map(function($city) {
                return query_clean($city);
            }, $_POST['display_cities']);

            $_POST['display_cities'] = array_filter($_POST['display_cities'], function($city) {
                return $city !== '';
            });

            $_POST['display_cities'] = array_unique($_POST['display_cities']);
        }

        $_POST['display_mobile'] = isset($_POST['display_mobile']) ? (int) $_POST['display_mobile'] : $notification->settings->display_mobile;
        $_POST['display_desktop'] = isset($_POST['display_desktop']) ? (int) $_POST['display_desktop'] : $notification->settings->display_desktop;

        /* Schedule */
        $_POST['schedule'] = isset($_POST['schedule']) ? (int) (bool) $_POST['schedule'] : $notification->settings->schedule;
        $_POST['start_date'] = !empty($_POST['start_date']) && Date::validate($_POST['start_date'], 'Y-m-d H:i:s') ? (new \DateTime($_POST['start_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s') : $notification->settings->start_date;
        $_POST['end_date'] = !empty($_POST['end_date']) && Date::validate($_POST['end_date'], 'Y-m-d H:i:s') ? (new \DateTime($_POST['end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s') : $notification->settings->end_date;

        $_POST['display_duration'] = in_array($notification->type, ['inline_informational', 'inline_rating_summary', 'inline_conversions_counter', 'inline_live_counter']) ? -1 : (isset($_POST['display_duration']) ? (int) $_POST['display_duration'] : $notification->settings->display_duration);
        $_POST['display_position'] = in_array($notification->type, ['inline_informational', 'inline_conversions', 'inline_reviews', 'inline_rating_summary', 'inline_conversions_counter', 'inline_live_counter']) ? 'inline' : (isset($_POST['display_position']) && in_array($_POST['display_position'], [
            'top_left',
            'top_center',
            'top_right',
            'middle_left',
            'middle_center',
            'middle_right',
            'bottom_left',
            'bottom_center',
            'bottom_right',
            'top',
            'bottom',
            'top_floating',
            'bottom_floating'
        ]) ? $_POST['display_position'] : $notification->settings->display_position);
        $_POST['display_close_button'] = in_array($notification->type, ['inline_informational', 'inline_conversions', 'inline_reviews', 'inline_rating_summary', 'inline_conversions_counter', 'inline_live_counter']) ? 0 : (isset($_POST['display_close_button']) ? (int) $_POST['display_close_button'] : $notification->settings->display_close_button);
        $_POST['display_branding'] = isset($_POST['display_branding']) ? (int) $_POST['display_branding'] : $notification->settings->display_branding;

        $_POST['shadow'] = isset($_POST['shadow']) && in_array($_POST['shadow'], [
            '',
            'subtle',
            'feather',
            '3d',
            'layered'
        ]) ? $_POST['shadow'] : $notification->settings->shadow;
        $_POST['border_width'] = isset($_POST['border_width']) ? (int) ($_POST['border_width'] >= 0 && $_POST['border_width'] <= 5 ? $_POST['border_width'] : 0) : $notification->settings->border_width;
        $_POST['internal_padding'] = isset($_POST['internal_padding']) ? (int) ($_POST['internal_padding'] >= 5 && $_POST['internal_padding'] <= 25 ? $_POST['internal_padding'] : 12) : $notification->settings->internal_padding;
        $_POST['background_blur'] = isset($_POST['background_blur']) && in_array((int) $_POST['background_blur'], range(0, 30)) ? (int) $_POST['background_blur'] : $notification->settings->background_blur;
        $_POST['hover_animation'] = isset($_POST['hover_animation']) && in_array($_POST['hover_animation'], [
            '',
            'fast_scale_up',
            'slow_scale_up',
            'fast_scale_down',
            'slow_scale_down',
        ]) ? $_POST['hover_animation'] : $notification->settings->hover_animation;
        $_POST['on_animation'] = isset($_POST['on_animation']) && in_array($_POST['on_animation'], [
            'fadeIn',
            'slideInUp',
            'slideInDown',
            'zoomIn',
            'bounceIn',
        ]) ? $_POST['on_animation'] : $notification->settings->on_animation;
        $_POST['off_animation'] = isset($_POST['off_animation']) && in_array($_POST['off_animation'], [
            'fadeOut',
            'slideOutUp',
            'slideOutDown',
            'zoomOut',
            'bounceOut',
        ]) ? $_POST['off_animation'] : $notification->settings->off_animation;
        $_POST['animation'] = isset($_POST['animation']) && in_array($_POST['animation'], [
            '',
            'heartbeat',
            'bounce',
            'flash',
            'pulse',
        ]) ? $_POST['animation'] : $notification->settings->animation;
        $_POST['animation_interval'] = isset($_POST['animation_interval']) ? (int) $_POST['animation_interval'] : $notification->settings->animation_interval;

        $_POST['font'] = isset($_POST['font']) && in_array($_POST['font'], [
            'inherit',
            'Arial',
            'Verdana',
            'Helvetica',
            'Tahoma',
            'Trebuchet MS',
            'Times New Roman',
            'Georgia',
            'Courier New',
            'Monaco',
            'Comic Sans MS',
            'Courier',
            'Impact',
            'Futura',
            'Luminari',
            'Baskerville',
            'Papyrus',
        ]) ? $_POST['font'] : $notification->settings->font;

        $_POST['custom_css'] = isset($_POST['custom_css']) ? mb_substr(trim($_POST['custom_css']), 0, 10000) : $notification->settings->custom_css;

        /* Dark mode */
        $_POST['dark_mode_is_enabled'] = isset($_POST['dark_mode_is_enabled']) ? (int) $_POST['dark_mode_is_enabled'] : $notification->settings->dark_mode_is_enabled;

        /* Initiate purifier */
        $purifier_config = \HTMLPurifier_Config::createDefault();
        $purifier_config->set('Cache.SerializerPath', UPLOADS_PATH . 'cache');
        $purifier_config->set('HTML.Allowed', 'span[style]');
        $purifier_config->set('CSS.AllowedProperties', 'color,font-weight,font-style,text-decoration,font-family,background-color,text-transform,margin,padding,text-align');
        $purifier = new \HTMLPurifier($purifier_config);

        /* Translations */
        $translations = json_decode(json_encode($notification->settings->translations), true);

        if(in_array($notification->type, ['inline_informational', 'inline_conversions', 'inline_reviews', 'inline_rating_summary', 'inline_conversions_counter', 'inline_live_counter'])) {
            $_POST['selector'] = isset($_POST['selector']) ? html_entity_decode(input_clean($_POST['selector'], 256), ENT_QUOTES | ENT_HTML5, 'UTF-8') : ($notification->settings->selector ?? '');
            $_POST['inline_placement'] = isset($_POST['inline_placement']) && in_array($_POST['inline_placement'], ['append', 'prepend', 'before', 'after']) ? $_POST['inline_placement'] : ($notification->settings->inline_placement ?? 'append');

            foreach(['margin_top', 'margin_right', 'margin_bottom', 'margin_left'] as $margin) {
                $_POST[$margin] = isset($_POST[$margin]) ? trim(mb_substr($_POST[$margin], 0, 32)) : ($notification->settings->{$margin} ?? '0');
                $_POST[$margin] = preg_match('/\A(?:auto|0|-?(?:\d+(?:\.\d+)?|\.\d+)(?:px|rem|em|%|vh|vw|vmin|vmax|ch|ex|cm|mm|in|pt|pc|q))\z/i', $_POST[$margin]) ? $_POST[$margin] : '0';
            }
        }

        switch($notification->type) {

            case 'inline_informational':
            case 'informational':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $image = \Altum\Uploads::process_upload($notification->settings->image, 'notifications', 'image', 'image_remove', settings()->notifications->image_size_limit, 'json_error');
                $_POST['image_alt'] = isset($_POST['image_alt']) ? mb_substr(query_clean($_POST['image_alt']), 0, 100) : $notification->settings->image_alt;
                $_POST['url'] = isset($_POST['url']) ? get_url($_POST['url']) : $notification->settings->url;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['border_radius'] = isset($_POST['border_radius']) && in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                    'round',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'informational_mini':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $image = \Altum\Uploads::process_upload($notification->settings->image, 'notifications', 'image', 'image_remove', settings()->notifications->image_size_limit, 'json_error');
                $_POST['image_alt'] = isset($_POST['image_alt']) ? mb_substr(query_clean($_POST['image_alt']), 0, 100) : $notification->settings->image_alt;
                $_POST['url'] = isset($_POST['url']) ? get_url($_POST['url']) : $notification->settings->url;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['border_radius'] = isset($_POST['border_radius']) && in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                    'round',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'coupon':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $_POST['coupon_code'] = isset($_POST['coupon_code']) ? input_clean($_POST['coupon_code'], 64) : $notification->settings->coupon_code;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['coupon_code'])) foreach ($_POST['translations']['coupon_code'] as $translation) {
                    $translations['coupon_code'][$translation['key']] = input_clean($translation['value'], 64);
                }

                $image = \Altum\Uploads::process_upload($notification->settings->image, 'notifications', 'image', 'image_remove', settings()->notifications->image_size_limit, 'json_error');
                $_POST['image_alt'] = isset($_POST['image_alt']) ? mb_substr(query_clean($_POST['image_alt']), 0, 100) : $notification->settings->image_alt;
                $_POST['button_url'] = isset($_POST['button_url']) ? get_url($_POST['button_url']) : $notification->settings->button_url;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }
                $_POST['border_radius'] = isset($_POST['border_radius']) && in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'live_counter':
            case 'live_counter_bar':
            case 'inline_live_counter':

                /* Clean some posted variables */
                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $_POST['last_activity'] = (int) (isset($_POST['last_activity']) ? $_POST['last_activity'] : $notification->settings->last_activity);
                $_POST['activity_type'] = isset($_POST['activity_type']) && in_array($_POST['activity_type'], ['all', 'page']) ? $_POST['activity_type'] : ($notification->settings->activity_type ?? 'all');
                $_POST['url'] = isset($_POST['url']) ? get_url($_POST['url']) : $notification->settings->url;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['display_minimum_activity'] = (int) (isset($_POST['display_minimum_activity']) ? $_POST['display_minimum_activity'] : $notification->settings->display_minimum_activity);
                $_POST['border_radius'] = isset($_POST['border_radius']) && in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'email_collector':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $_POST['name_placeholder'] = isset($_POST['name_placeholder']) ? input_clean($_POST['name_placeholder'], 128) : $notification->settings->name_placeholder;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['name_placeholder'])) foreach ($_POST['translations']['name_placeholder'] as $translation) {
                    $translations['name_placeholder'][$translation['key']] = input_clean($translation['value'],  128);
                }

                $_POST['email_placeholder'] = isset($_POST['email_placeholder']) ? input_clean($_POST['email_placeholder'], 128) : $notification->settings->email_placeholder;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['email_placeholder'])) foreach ($_POST['translations']['email_placeholder'] as $translation) {
                    $translations['email_placeholder'][$translation['key']] = input_clean($translation['value'],  128);
                }

                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }
                $_POST['show_agreement'] = (int) (isset($_POST['show_agreement']) ? $_POST['show_agreement'] : $notification->settings->show_agreement);
                $_POST['agreement_text'] = isset($_POST['agreement_text']) ? input_clean($_POST['agreement_text'], 256) : $notification->settings->agreement_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agreement_text'])) foreach ($_POST['translations']['agreement_text'] as $translation) {
                    $translations['agreement_text'][$translation['key']] = input_clean($translation['value'],  256);
                }

                $_POST['agreement_url'] = isset($_POST['agreement_url']) ? get_url($_POST['agreement_url']) : $notification->settings->agreement_url;
                $_POST['thank_you_url'] = isset($_POST['thank_you_url']) ? get_url($_POST['thank_you_url']) : $notification->settings->thank_you_url;
                $_POST['border_radius'] = isset($_POST['border_radius']) && in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'inline_conversions':
            case 'conversions':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $image = \Altum\Uploads::process_upload($notification->settings->image, 'notifications', 'image', 'image_remove', settings()->notifications->image_size_limit, 'json_error');
                $_POST['image_alt'] = isset($_POST['image_alt']) ? mb_substr(query_clean($_POST['image_alt']), 0, 100) : $notification->settings->image_alt;
                $_POST['url'] = isset($_POST['url']) ? get_url($_POST['url']) : $notification->settings->url;
                $_POST['display_time'] = isset($_POST['display_time']) ? (bool) (int) $_POST['display_time'] : $notification->settings->display_time;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['conversions_count'] = (int) (isset($_POST['conversions_count']) && $_POST['conversions_count'] >= 1 ? $_POST['conversions_count'] : 1);
                $_POST['in_between_delay'] = (int) (isset($_POST['in_between_delay']) && $_POST['in_between_delay'] >= 1 ? $_POST['in_between_delay'] : 0);
                $_POST['infinite_rotation'] = isset($_POST['infinite_rotation']) ? (bool) (int) $_POST['infinite_rotation'] : ($notification->settings->infinite_rotation ?? false);
                $_POST['order'] = isset($_POST['order']) && in_array($_POST['order'], ['descending', 'random']) ? $_POST['order'] : 'descending';
                $_POST['border_radius'] = isset($_POST['border_radius']) && in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                    'round',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;
                $_POST['data_trigger_auto'] = isset($_POST['data_trigger_auto']) ? (int) $_POST['data_trigger_auto'] : $notification->settings->data_trigger_auto;

                break;

            case 'inline_conversions_counter':
            case 'conversions_counter':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['last_activity'] = (int) (isset($_POST['last_activity']) ? $_POST['last_activity'] : $notification->settings->last_activity);
                $_POST['url'] = isset($_POST['url']) ? get_url($_POST['url']) : $notification->settings->url;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['display_minimum_activity'] = (int) ($_POST['display_minimum_activity'] ?? $notification->settings->display_minimum_activity);
                $_POST['border_radius'] = isset($_POST['border_radius']) && in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                    'round',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;
                $_POST['data_trigger_auto'] = isset($_POST['data_trigger_auto']) ? (int) $_POST['data_trigger_auto'] : $notification->settings->data_trigger_auto;

                break;

            case 'video':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['video'] = isset($_POST['video']) ? query_clean($_POST['video']) : $notification->settings->video;
                $_POST['button_url'] = isset($_POST['button_url']) ? get_url($_POST['button_url']) : $notification->settings->button_url;
                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }
                $_POST['border_radius'] = isset($_POST['border_radius']) && in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['video_autoplay'] = (bool) (int) ($_POST['video_autoplay'] ?? $notification->settings->video_autoplay);
                $_POST['video_controls'] = (bool) (int) ($_POST['video_controls'] ?? $notification->settings->video_controls);
                $_POST['video_loop'] = (bool) (int) ($_POST['video_loop'] ?? $notification->settings->video_loop);
                $_POST['video_muted'] = (bool) (int) ($_POST['video_muted'] ?? $notification->settings->video_muted);

                /* Parse YouTube and Vimeo video links */
                $video_url = $_POST['video'];
                $youtube_match = [];
                $vimeo_match = [];

                if(preg_match('/(?:https?:\/\/)?(?:www\.)?(?:youtube-nocookie\.com\/youtu\.be\/|youtube\.com\/(?:embed\/|shorts\/|v\/|watch\?v=|watch\?.+&v=))((?:\w|-){11})/', $video_url, $youtube_match)) {
                    $_POST['video'] = 'https://www.youtube.com/embed/' . $youtube_match[1];
                    $_POST['video_is_youtube'] = true;
                    $_POST['youtube_video_id'] = $youtube_match[1];
                    $_POST['video_is_vimeo'] = false;
                    $_POST['vimeo_video_id'] = null;
                } elseif(preg_match('/(?:https?:\/\/)?(?:www\.)?vimeo\.com\/(?:video\/)?(\d+)/', $video_url, $vimeo_match)) {
                    $_POST['video'] = 'https://player.vimeo.com/video/' . $vimeo_match[1];
                    $_POST['video_is_vimeo'] = true;
                    $_POST['vimeo_video_id'] = $vimeo_match[1];
                    $_POST['video_is_youtube'] = false;
                    $_POST['youtube_video_id'] = null;
                } else {
                    $_POST['video_is_youtube'] = false;
                    $_POST['youtube_video_id'] = null;
                    $_POST['video_is_vimeo'] = false;
                    $_POST['vimeo_video_id'] = null;
                }

                break;

            case 'audio':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $audio = \Altum\Uploads::process_upload($notification->settings->audio, 'notifications_audios', 'audio', 'audio_remove', settings()->notifications->audio_size_limit);
                $_POST['button_url'] = isset($_POST['button_url']) ? get_url($_POST['button_url']) : $notification->settings->button_url;
                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }
                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['audio_autoplay'] = (bool) (int) ($_POST['audio_autoplay'] ?? $notification->settings->audio_autoplay);
                $_POST['audio_controls'] = (bool) (int) ($_POST['audio_controls'] ?? $notification->settings->audio_controls);
                $_POST['audio_loop'] = (bool) (int) ($_POST['audio_loop'] ?? $notification->settings->audio_loop);
                $_POST['audio_muted'] = (bool) (int) ($_POST['audio_muted'] ?? $notification->settings->audio_muted);

                break;

            case 'social_share':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }


                $_POST['share_url'] = isset($_POST['share_url']) ? get_url($_POST['share_url']) : $notification->settings->share_url;
                $_POST['share_facebook'] = (bool) (int) ($_POST['share_facebook'] ?? $notification->settings->share_facebook);
                $_POST['share_x'] = (bool) (int) ($_POST['share_x'] ?? $notification->settings->share_x);
                $_POST['share_threads'] = (bool) (int) ($_POST['share_threads'] ?? $notification->settings->share_threads);
                $_POST['share_facebook'] = (bool) (int) ($_POST['share_facebook'] ?? $notification->settings->share_facebook);
                $_POST['share_reddit'] = (bool) (int) ($_POST['share_reddit'] ?? $notification->settings->share_reddit);
                $_POST['share_tumblr'] = (bool) (int) ($_POST['share_tumblr'] ?? $notification->settings->share_tumblr);
                $_POST['share_linkedin'] = (bool) (int) ($_POST['share_linkedin'] ?? $notification->settings->share_linkedin);
                $_POST['share_telegram'] = (bool) (int) ($_POST['share_telegram'] ?? $notification->settings->share_telegram);
                $_POST['share_whatsapp'] = (bool) (int) ($_POST['share_whatsapp'] ?? $notification->settings->share_whatsapp);

                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'inline_reviews':
            case 'reviews':

                /* Clean some posted variables */
                $_POST['url'] = isset($_POST['url']) ? get_url($_POST['url']) : $notification->settings->url;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['reviews_count'] = isset($_POST['reviews_count']) ? ((int) $_POST['reviews_count'] < 1 ? 1 : (int) $_POST['reviews_count']) : $notification->settings->reviews_count;
                $_POST['in_between_delay'] = isset($_POST['in_between_delay']) ? ((int) $_POST['in_between_delay'] < 1 ? 0 : (int) $_POST['in_between_delay']) : $notification->settings->in_between_delay;
                $_POST['infinite_rotation'] = isset($_POST['infinite_rotation']) ? (bool) (int) $_POST['infinite_rotation'] : ($notification->settings->infinite_rotation ?? false);
                $_POST['order'] = isset($_POST['order']) && in_array($_POST['order'], ['descending', 'random']) ? $_POST['order'] : $notification->settings->order;
                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                    'round',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'inline_rating_summary':
            case 'rating_summary':

                /* Clean some posted variables */
                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $_POST['average_rating'] = (float) ($_POST['average_rating'] ?? $notification->settings->average_rating);
                $_POST['average_rating'] = $_POST['average_rating'] < 1 ? 1 : ($_POST['average_rating'] > 5 ? 5 : $_POST['average_rating']);
                $_POST['reviews_count'] = (int) (isset($_POST['reviews_count']) && $_POST['reviews_count'] >= 0 ? $_POST['reviews_count'] : $notification->settings->reviews_count);
                $_POST['display_minimum_reviews'] = (int) ($_POST['display_minimum_reviews'] ?? $notification->settings->display_minimum_reviews);
                $_POST['display_minimum_reviews'] = $_POST['display_minimum_reviews'] < 0 ? 0 : $_POST['display_minimum_reviews'];
                $_POST['url'] = isset($_POST['url']) ? get_url($_POST['url']) : $notification->settings->url;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['border_radius'] = isset($_POST['border_radius']) && in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                    'round',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'emoji_feedback':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['thank_you_url'] = isset($_POST['thank_you_url']) ? get_url($_POST['thank_you_url']) : $notification->settings->thank_you_url;
                $_POST['show_angry'] = (bool) (int) ($_POST['show_angry'] ?? $notification->settings->show_angry);
                $_POST['show_sad'] = (bool) (int) ($_POST['show_sad'] ?? $notification->settings->show_sad);
                $_POST['show_neutral'] = (bool) (int) ($_POST['show_neutral'] ?? $notification->settings->show_neutral);
                $_POST['show_happy'] = (bool) (int) ($_POST['show_happy'] ?? $notification->settings->show_happy);
                $_POST['show_excited'] = (bool) (int) ($_POST['show_excited'] ?? $notification->settings->show_excited);
                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'cookie_notification':

                /* Clean some posted variables */
                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $image = \Altum\Uploads::process_upload($notification->settings->image, 'notifications', 'image', 'image_remove', settings()->notifications->image_size_limit, 'json_error');
                $_POST['image_alt'] = isset($_POST['image_alt']) ? mb_substr(query_clean($_POST['image_alt']), 0, 100) : $notification->settings->image_alt;
                $_POST['url_text'] = isset($_POST['url_text']) ? input_clean($_POST['url_text'], 256) : $notification->settings->url_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['url_text'])) foreach ($_POST['translations']['url_text'] as $translation) {
                    $translations['url_text'][$translation['key']] = input_clean($translation['value'], 256);
                }

                $_POST['url'] = isset($_POST['url']) ? get_url($_POST['url']) : $notification->settings->url;
                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }
                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'score_feedback':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $_POST['thank_you_url'] = isset($_POST['thank_you_url']) ? get_url($_POST['thank_you_url']) : $notification->settings->thank_you_url;
                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'request_collector' :

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $image = \Altum\Uploads::process_upload($notification->settings->image, 'notifications', 'image', 'image_remove', settings()->notifications->image_size_limit, 'json_error');
                $_POST['image_alt'] = isset($_POST['image_alt']) ? mb_substr(query_clean($_POST['image_alt']), 0, 100) : $notification->settings->image_alt;

                $_POST['content_title'] = isset($_POST['content_title']) ? mb_substr(query_clean($_POST['content_title']), 0, 256) : $notification->settings->content_title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['content_title'])) foreach ($_POST['translations']['content_title'] as $translation) {
                    $translations['content_title'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 256);
                }

                $_POST['content_description'] = isset($_POST['content_description']) ? mb_substr(query_clean($_POST['content_description']), 0, 512) : $notification->settings->content_description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['content_description'])) foreach ($_POST['translations']['content_description'] as $translation) {
                    $translations['content_description'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 512);
                }

                $_POST['input_placeholder'] = isset($_POST['input_placeholder']) ? mb_substr(query_clean($_POST['input_placeholder']), 0, 128) : $notification->settings->input_placeholder;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['input_placeholder'])) foreach ($_POST['translations']['input_placeholder'] as $translation) {
                    $translations['input_placeholder'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 128);
                }

                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }
                $_POST['show_agreement'] = (bool) (int) ($_POST['show_agreement'] ?? $notification->settings->show_agreement);
                $_POST['agreement_text'] = isset($_POST['agreement_text']) ? input_clean($_POST['agreement_text'], 256) : $notification->settings->agreement_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agreement_text'])) foreach ($_POST['translations']['agreement_text'] as $translation) {
                    $translations['agreement_text'][$translation['key']] = input_clean($translation['value'],  256);
                }

                $_POST['agreement_url'] = isset($_POST['agreement_url']) ? get_url($_POST['agreement_url']) : $notification->settings->agreement_url;
                $_POST['thank_you_url'] = isset($_POST['thank_you_url']) ? get_url($_POST['thank_you_url']) : $notification->settings->thank_you_url;
                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'countdown_collector' :

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $_POST['content_title'] = isset($_POST['content_title']) ? mb_substr(query_clean($_POST['content_title']), 0, 256) : $notification->settings->content_title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['content_title'])) foreach ($_POST['translations']['content_title'] as $translation) {
                    $translations['content_title'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 256);
                }

                $_POST['input_placeholder'] = isset($_POST['input_placeholder']) ? mb_substr(query_clean($_POST['input_placeholder']), 0, 128) : $notification->settings->input_placeholder;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['input_placeholder'])) foreach ($_POST['translations']['input_placeholder'] as $translation) {
                    $translations['input_placeholder'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 128);
                }

                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }
                $_POST['countdown_end_date'] = (new \DateTime($_POST['countdown_end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
                $_POST['show_agreement'] = (bool) (int) ($_POST['show_agreement'] ?? $notification->settings->show_agreement);
                $_POST['agreement_text'] = isset($_POST['agreement_text']) ? input_clean($_POST['agreement_text'], 256) : $notification->settings->agreement_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agreement_text'])) foreach ($_POST['translations']['agreement_text'] as $translation) {
                    $translations['agreement_text'][$translation['key']] = input_clean($translation['value'],  256);
                }

                $_POST['agreement_url'] = isset($_POST['agreement_url']) ? get_url($_POST['agreement_url']) : $notification->settings->agreement_url;
                $_POST['thank_you_url'] = isset($_POST['thank_you_url']) ? get_url($_POST['thank_you_url']) : $notification->settings->thank_you_url;
                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'informational_bar':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $image = \Altum\Uploads::process_upload($notification->settings->image, 'notifications', 'image', 'image_remove', settings()->notifications->image_size_limit, 'json_error');
                $_POST['image_alt'] = isset($_POST['image_alt']) ? mb_substr(query_clean($_POST['image_alt']), 0, 100) : $notification->settings->image_alt;
                $_POST['url'] = isset($_POST['url']) ? get_url($_POST['url']) : $notification->settings->url;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['border_radius'] = 'straight';

                break;

            case 'informational_bar_mini':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $image = \Altum\Uploads::process_upload($notification->settings->image, 'notifications', 'image', 'image_remove', settings()->notifications->image_size_limit, 'json_error');
                $_POST['image_alt'] = isset($_POST['image_alt']) ? mb_substr(query_clean($_POST['image_alt']), 0, 100) : $notification->settings->image_alt;
                $_POST['url'] = isset($_POST['url']) ? get_url($_POST['url']) : $notification->settings->url;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['border_radius'] = 'straight';

                break;

            case 'image':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $image = \Altum\Uploads::process_upload($notification->settings->image, 'notifications', 'image', 'image_remove', settings()->notifications->image_size_limit, 'json_error');
                $_POST['image_alt'] = isset($_POST['image_alt']) ? mb_substr(query_clean($_POST['image_alt']), 0, 100) : $notification->settings->image_alt;
                $_POST['button_url'] = isset($_POST['button_url']) ? get_url($_POST['button_url']) : $notification->settings->button_url;
                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }
                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'collector_bar' :

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }


                $_POST['input_placeholder'] = isset($_POST['input_placeholder']) ? mb_substr(query_clean($_POST['input_placeholder']), 0, 128) : $notification->settings->input_placeholder;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['input_placeholder'])) foreach ($_POST['translations']['input_placeholder'] as $translation) {
                    $translations['input_placeholder'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 128);
                }

                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }
                $_POST['show_agreement'] = (bool) (int) ($_POST['show_agreement'] ?? $notification->settings->show_agreement);
                $_POST['agreement_text'] = isset($_POST['agreement_text']) ? input_clean($_POST['agreement_text'], 256) : $notification->settings->agreement_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agreement_text'])) foreach ($_POST['translations']['agreement_text'] as $translation) {
                    $translations['agreement_text'][$translation['key']] = input_clean($translation['value'],  256);
                }

                $_POST['agreement_url'] = isset($_POST['agreement_url']) ? get_url($_POST['agreement_url']) : $notification->settings->agreement_url;
                $_POST['thank_you_url'] = isset($_POST['thank_you_url']) ? get_url($_POST['thank_you_url']) : $notification->settings->thank_you_url;
                $_POST['border_radius'] = 'straight';

                break;

            case 'coupon_bar':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['coupon_code'] = isset($_POST['coupon_code']) ? input_clean($_POST['coupon_code'], 64) : $notification->settings->coupon_code;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['coupon_code'])) foreach ($_POST['translations']['coupon_code'] as $translation) {
                    $translations['coupon_code'][$translation['key']] = input_clean($translation['value'], 64);
                }

                $_POST['url'] = isset($_POST['url']) ? get_url($_POST['url']) : $notification->settings->url;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['border_radius'] = 'straight';

                break;

            case 'button_bar':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }
                $_POST['url'] = isset($_POST['url']) ? get_url($_POST['url']) : $notification->settings->url;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;
                $_POST['border_radius'] = 'straight';

                break;

            case 'collector_modal' :
            case 'collector_two_modal' :

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $image = \Altum\Uploads::process_upload($notification->settings->image, 'notifications', 'image', 'image_remove', settings()->notifications->image_size_limit, 'json_error');
                $_POST['image_alt'] = isset($_POST['image_alt']) ? mb_substr(query_clean($_POST['image_alt']), 0, 100) : $notification->settings->image_alt;

                $_POST['input_placeholder'] = isset($_POST['input_placeholder']) ? mb_substr(query_clean($_POST['input_placeholder']), 0, 128) : $notification->settings->input_placeholder;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['input_placeholder'])) foreach ($_POST['translations']['input_placeholder'] as $translation) {
                    $translations['input_placeholder'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 128);
                }

                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }
                $_POST['show_agreement'] = (bool) (int) ($_POST['show_agreement'] ?? $notification->settings->show_agreement);
                $_POST['agreement_text'] = isset($_POST['agreement_text']) ? input_clean($_POST['agreement_text'], 256) : $notification->settings->agreement_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agreement_text'])) foreach ($_POST['translations']['agreement_text'] as $translation) {
                    $translations['agreement_text'][$translation['key']] = input_clean($translation['value'],  256);
                }

                $_POST['agreement_url'] = isset($_POST['agreement_url']) ? get_url($_POST['agreement_url']) : $notification->settings->agreement_url;
                $_POST['thank_you_url'] = isset($_POST['thank_you_url']) ? get_url($_POST['thank_you_url']) : $notification->settings->thank_you_url;
                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;


                break;

            case 'button_modal' :

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $image = \Altum\Uploads::process_upload($notification->settings->image, 'notifications', 'image', 'image_remove', settings()->notifications->image_size_limit, 'json_error');
                $_POST['image_alt'] = isset($_POST['image_alt']) ? mb_substr(query_clean($_POST['image_alt']), 0, 100) : $notification->settings->image_alt;
                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }
                $_POST['button_url'] = isset($_POST['button_url']) ? get_url($_POST['button_url']) : $notification->settings->button_url;
                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;

                break;

            case 'text_feedback' :

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $_POST['input_placeholder'] = isset($_POST['input_placeholder']) ? mb_substr(query_clean($_POST['input_placeholder']), 0, 128) : $notification->settings->input_placeholder;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['input_placeholder'])) foreach ($_POST['translations']['input_placeholder'] as $translation) {
                    $translations['input_placeholder'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 128);
                }

                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }
                $_POST['thank_you_url'] = isset($_POST['thank_you_url']) ? get_url($_POST['thank_you_url']) : $notification->settings->thank_you_url;
                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;


                break;

            case 'engagement_links' :

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }



                $_POST['categories'] = isset($_POST['categories']) && is_array($_POST['categories']) ? array_map(function($category) use ($purifier) {
                    $category['title'] = $purifier->purify(mb_substr($category['title'] ?? '', 0, 256));
                    $category['description'] = $purifier->purify(mb_substr($category['description'] ?? '', 0, 512));

                    $category['links'] = array_map(function($category_link) use ($purifier) {
                        $category_link['title'] = $purifier->purify(mb_substr($category_link['title'] ?? '', 0, 256));
                        $category_link['description'] = $purifier->purify(mb_substr($category_link['description'] ?? '', 0, 512));
                        $category_link['image'] = mb_substr(query_clean($category_link['image'] ?? ''), 0, 2048);
                        $category_link['url'] = get_url($category_link['url'] ?? '');

                        return $category_link;
                    }, $category['links'] ?? []);

                    return $category;
                }, $_POST['categories']) : null;
                $_POST['categories'] = array_values($_POST['categories'] ?? $notification->settings->categories);

                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'help_center' :

                /* Clean help center variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $_POST['badge_text'] = isset($_POST['badge_text']) ? $purifier->purify(mb_substr($_POST['badge_text'], 0, 64)) : $notification->settings->badge_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['badge_text'])) foreach ($_POST['translations']['badge_text'] as $translation) {
                    $translations['badge_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 64));
                }

                $_POST['hint_text'] = isset($_POST['hint_text']) ? $purifier->purify(mb_substr($_POST['hint_text'], 0, 128)) : $notification->settings->hint_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['hint_text'])) foreach ($_POST['translations']['hint_text'] as $translation) {
                    $translations['hint_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }

                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }

                $_POST['button_url'] = isset($_POST['button_url']) ? get_url($_POST['button_url']) : $notification->settings->button_url;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;

                if(isset($_POST['items']) && is_array($_POST['items'])) {
                    $_POST['items'] = array_map(function($item) use ($purifier) {
                        $item['question'] = $purifier->purify(mb_substr(isset($item['question']) ? $item['question'] : '', 0, 256));
                        $item['answer'] = $purifier->purify(mb_substr(isset($item['answer']) ? $item['answer'] : '', 0, 1024));
                        $item['url'] = get_url(isset($item['url']) ? $item['url'] : '');
                        $item['url_text'] = $purifier->purify(mb_substr(isset($item['url_text']) ? $item['url_text'] : '', 0, 128));

                        return $item;
                    }, $_POST['items']);
                    $_POST['items'] = array_values($_POST['items']);
                } else {
                    $_POST['items'] = $notification->settings->items;
                }

                $_POST['border_radius'] = isset($_POST['border_radius']) && in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'round',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'whatsapp_chat' :

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['agent_image'] = isset($_POST['agent_image']) ? mb_substr(query_clean($_POST['agent_image']), 0, 2048) : $notification->settings->agent_image;
                $_POST['agent_image_alt'] = isset($_POST['agent_image_alt']) ? mb_substr(query_clean($_POST['agent_image_alt']), 0, 100) : $notification->settings->agent_image_alt;

                $_POST['agent_name'] = isset($_POST['agent_name']) ? mb_substr(query_clean($_POST['agent_name']), 0, 64) : $notification->settings->agent_name;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agent_name'])) foreach ($_POST['translations']['agent_name'] as $translation) {
                    $translations['agent_name'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 64);
                }

                $_POST['agent_description'] = isset($_POST['agent_description']) ? mb_substr(query_clean($_POST['agent_description']), 0, 512) : $notification->settings->agent_description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agent_description'])) foreach ($_POST['translations']['agent_description'] as $translation) {
                    $translations['agent_description'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 512);
                }

                $_POST['agent_message'] = isset($_POST['agent_message']) ? mb_substr(query_clean($_POST['agent_message']), 0, 1024) : $notification->settings->agent_message;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agent_message'])) foreach ($_POST['translations']['agent_message'] as $translation) {
                    $translations['agent_message'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 1024);
                }

                $_POST['agent_phone_number'] = isset($_POST['agent_phone_number']) ? (int) mb_substr(query_clean($_POST['agent_phone_number']), 0, 32) : $notification->settings->agent_phone_number;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agent_phone_number'])) foreach ($_POST['translations']['agent_phone_number'] as $translation) {
                    $translations['agent_phone_number'][$translation['key']] = (int) mb_substr(query_clean($translation['value']), 0, 32);
                }

                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }

                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'round',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'telegram_chat' :

                /* Clean Telegram chat variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['agent_image'] = isset($_POST['agent_image']) ? mb_substr(query_clean($_POST['agent_image']), 0, 2048) : $notification->settings->agent_image;
                $_POST['agent_image_alt'] = isset($_POST['agent_image_alt']) ? mb_substr(query_clean($_POST['agent_image_alt']), 0, 100) : $notification->settings->agent_image_alt;

                $_POST['agent_name'] = isset($_POST['agent_name']) ? mb_substr(query_clean($_POST['agent_name']), 0, 64) : $notification->settings->agent_name;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agent_name'])) foreach ($_POST['translations']['agent_name'] as $translation) {
                    $translations['agent_name'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 64);
                }

                $_POST['agent_description'] = isset($_POST['agent_description']) ? mb_substr(query_clean($_POST['agent_description']), 0, 512) : $notification->settings->agent_description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agent_description'])) foreach ($_POST['translations']['agent_description'] as $translation) {
                    $translations['agent_description'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 512);
                }

                $_POST['agent_message'] = isset($_POST['agent_message']) ? mb_substr(query_clean($_POST['agent_message']), 0, 1024) : $notification->settings->agent_message;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agent_message'])) foreach ($_POST['translations']['agent_message'] as $translation) {
                    $translations['agent_message'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 1024);
                }

                $_POST['agent_telegram'] = isset($_POST['agent_telegram']) ? mb_substr(query_clean($_POST['agent_telegram']), 0, 32) : $notification->settings->agent_telegram;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agent_telegram'])) foreach ($_POST['translations']['agent_telegram'] as $translation) {
                    $translations['agent_telegram'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 32);
                }

                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }

                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'round',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'facebook_messenger_chat' :

                /* Clean Messenger chat variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['agent_image'] = isset($_POST['agent_image']) ? mb_substr(query_clean($_POST['agent_image']), 0, 2048) : $notification->settings->agent_image;
                $_POST['agent_image_alt'] = isset($_POST['agent_image_alt']) ? mb_substr(query_clean($_POST['agent_image_alt']), 0, 100) : $notification->settings->agent_image_alt;

                $_POST['agent_name'] = isset($_POST['agent_name']) ? mb_substr(query_clean($_POST['agent_name']), 0, 64) : $notification->settings->agent_name;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agent_name'])) foreach ($_POST['translations']['agent_name'] as $translation) {
                    $translations['agent_name'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 64);
                }

                $_POST['agent_description'] = isset($_POST['agent_description']) ? mb_substr(query_clean($_POST['agent_description']), 0, 512) : $notification->settings->agent_description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agent_description'])) foreach ($_POST['translations']['agent_description'] as $translation) {
                    $translations['agent_description'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 512);
                }

                $_POST['agent_message'] = isset($_POST['agent_message']) ? mb_substr(query_clean($_POST['agent_message']), 0, 1024) : $notification->settings->agent_message;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agent_message'])) foreach ($_POST['translations']['agent_message'] as $translation) {
                    $translations['agent_message'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 1024);
                }

                $_POST['agent_facebook_messenger'] = isset($_POST['agent_facebook_messenger']) ? mb_substr(query_clean($_POST['agent_facebook_messenger']), 0, 64) : $notification->settings->agent_facebook_messenger;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['agent_facebook_messenger'])) foreach ($_POST['translations']['agent_facebook_messenger'] as $translation) {
                    $translations['agent_facebook_messenger'][$translation['key']] = mb_substr(query_clean($translation['value']), 0, 64);
                }

                $_POST['button_text'] = isset($_POST['button_text']) ? $purifier->purify(mb_substr($_POST['button_text'], 0, 128)) : $notification->settings->button_text;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['button_text'])) foreach ($_POST['translations']['button_text'] as $translation) {
                    $translations['button_text'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 128));
                }

                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'round',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

            case 'badge' :

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $image = \Altum\Uploads::process_upload($notification->settings->image, 'notifications', 'image', 'image_remove', settings()->notifications->image_size_limit, 'json_error');
                $_POST['image_alt'] = isset($_POST['image_alt']) ? mb_substr(query_clean($_POST['image_alt']), 0, 100) : $notification->settings->image_alt;

                $_POST['url'] = isset($_POST['url']) ? get_url($_POST['url']) : $notification->settings->url;
                $_POST['url_new_tab'] = isset($_POST['url_new_tab']) ? (bool) (int) $_POST['url_new_tab'] : $notification->settings->url_new_tab;

                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'round',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;
                $_POST['image_size'] = isset($_POST['image_size']) ? (int) ($_POST['image_size'] >= 15 && $_POST['image_size'] <= 55 ? $_POST['image_size'] : 45) : $notification->settings->image_size;

                break;

            case 'custom_html':

                /* Clean some posted variables */
                $_POST['html'] = isset($_POST['html']) ? mb_substr($_POST['html'], 0, 16000) : $notification->settings->html;;
                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                    'round',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;
                break;

            case 'contact_us':

                /* Clean some posted variables */
                $_POST['title'] = isset($_POST['title']) ? $purifier->purify(mb_substr($_POST['title'], 0, 256)) : $notification->settings->title;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['title'])) foreach ($_POST['translations']['title'] as $translation) {
                    $translations['title'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 256));
                }

                $_POST['description'] = isset($_POST['description']) ? $purifier->purify(mb_substr($_POST['description'], 0, 512)) : $notification->settings->description;
                if(!empty($_POST['translations']) && !empty($_POST['translations']['description'])) foreach ($_POST['translations']['description'] as $translation) {
                    $translations['description'][$translation['key']] = $purifier->purify(mb_substr($translation['value'], 0, 512));
                }

                $_POST['contact_email'] = isset($_POST['contact_email']) ? query_clean($_POST['contact_email'], 320) : $notification->settings->contact_email;
                $_POST['contact_phone_number'] = isset($_POST['contact_phone_number']) ? query_clean($_POST['contact_phone_number'], 64) : $notification->settings->contact_phone_number;
                $_POST['contact_whatsapp'] = isset($_POST['contact_whatsapp']) ? query_clean($_POST['contact_whatsapp'], 64) : $notification->settings->contact_whatsapp;
                $_POST['contact_telegram'] = isset($_POST['contact_telegram']) ? query_clean($_POST['contact_telegram'], 32) : $notification->settings->contact_telegram;
                $_POST['contact_facebook_messenger'] = isset($_POST['contact_facebook_messenger']) ? query_clean($_POST['contact_facebook_messenger'], 64) : $notification->settings->contact_facebook_messenger;

                $_POST['border_radius'] = in_array($_POST['border_radius'], [
                    'straight',
                    'rounded',
                    'highly_rounded',
                ]) ? $_POST['border_radius'] : $notification->settings->border_radius;

                break;

        }

        /* Go over all the possible color inputs and make sure they comply */
        foreach($_POST as $key => $value) {
            if(string_ends_with('_color', $key) && !verify_hex_color($value)) {

                /* Replace it with a plain black color */
                $_POST[$key] = '#000000';

            }
        }

        $triggers = $notification->settings->triggers;

        /* Go over the triggers and clean them */
        foreach($_POST['trigger_type'] ?? [] as $key => $value) {
            $_POST['trigger_type'][$key] = in_array($value, ['exact', 'not_exact', 'contains', 'not_contains', 'starts_with', 'not_starts_with', 'ends_with', 'not_ends_with', 'page_contains']) ? query_clean($value) : 'exact';
        }

        foreach($_POST['trigger_value'] ?? [] as $key => $value) {
            $_POST['trigger_value'][$key] = input_clean($value, 512);
        }

        /* Generate the trigger rules var */
        $new_triggers = [];

        foreach($_POST['trigger_type'] ?? [] as $key => $value) {
            if(empty($_POST['trigger_value'][$key])) continue;

            $new_triggers[$key] = [
                'type' => $value,
                'value' => $_POST['trigger_value'][$key]
            ];
        }

        $triggers = array_merge($triggers, $new_triggers);

        /* Default notification settings */
        $new_notification_settings = [
            'trigger_all_pages' => $_POST['trigger_all_pages'],
            'triggers' => $triggers,
            'display_trigger' => $_POST['display_trigger'],
            'display_trigger_value' => $_POST['display_trigger_value'],
            'display_trigger_selector' => $_POST['display_trigger_selector'],
            'display_frequency' => $_POST['display_frequency'],
            'display_delay_type_after_close' => $_POST['display_delay_type_after_close'],
            'display_delay_value_after_close' => $_POST['display_delay_value_after_close'],

            /* Targeting */
            'display_continents' => $_POST['display_continents'],
            'display_countries' => $_POST['display_countries'],
            'display_languages' => $_POST['display_languages'],
            'display_operating_systems' => $_POST['display_operating_systems'],
            'display_browsers' => $_POST['display_browsers'],
            'display_cities' => $_POST['display_cities'],
            'display_mobile' => $_POST['display_mobile'],
            'display_desktop' => $_POST['display_desktop'],

            'close_button_color' => $_POST['close_button_color'] ?? $notification->settings->close_button_color,
            'dark_mode_close_button_color' => $_POST['dark_mode_close_button_color'] ?? $notification->settings->dark_mode_close_button_color,
            'shadow' => $_POST['shadow'],
            'hover_animation' => $_POST['hover_animation'],
            'internal_padding' => $_POST['internal_padding'],
            'background_blur' => $_POST['background_blur'],
            'border_radius' => $_POST['border_radius'],
            'border_width' => $_POST['border_width'],
            'border_color' => $_POST['border_color'] ?? $notification->settings->border_color,
            'dark_mode_border_color' => $_POST['dark_mode_border_color'] ?? $notification->settings->dark_mode_border_color,
            'shadow_color' => $_POST['shadow_color'] ?? $notification->settings->shadow_color,
            'dark_mode_shadow_color' => $_POST['dark_mode_shadow_color'] ?? $notification->settings->dark_mode_shadow_color,
            'on_animation' => $_POST['on_animation'],
            'off_animation' => $_POST['off_animation'],
            'animation' => $_POST['animation'],
            'animation_interval' => $_POST['animation_interval'],
            'font' => $_POST['font'],

            'direction' => $_POST['direction'],
            'display_duration' => $_POST['display_duration'],
            'display_position' => $_POST['display_position'],
            'display_close_button' => $_POST['display_close_button'],
            'display_branding' => $_POST['display_branding'],

            'schedule' => $_POST['schedule'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],

            'custom_css' => $_POST['custom_css'],

            /* Dark mode */
            'dark_mode_is_enabled' => $_POST['dark_mode_is_enabled'],

            /* Translations */
            'translations' => $translations,
        ];

        /* Prepare the settings json based on the notification type */
        switch($notification->type) {

            case 'inline_informational' :
            case 'informational' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'image' => $image,
                        'image_alt' => $_POST['image_alt'],
                        'url' => $_POST['url'],
                        'url_new_tab' => $_POST['url_new_tab'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                    ]
                );

                if($notification->type == 'inline_informational') {
                    $new_notification_settings = array_merge(
                        $new_notification_settings,
                        [
                            'selector' => $_POST['selector'],
                            'inline_placement' => $_POST['inline_placement'],
                            'margin_top' => $_POST['margin_top'],
                            'margin_right' => $_POST['margin_right'],
                            'margin_bottom' => $_POST['margin_bottom'],
                            'margin_left' => $_POST['margin_left'],
                        ]
                    );
                }

                break;

            case 'informational_mini' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'image' => $image,
                        'image_alt' => $_POST['image_alt'],
                        'url' => $_POST['url'],
                        'url_new_tab' => $_POST['url_new_tab'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                    ]
                );

                break;

            case 'coupon' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'image' => $image,
                        'image_alt' => $_POST['image_alt'],
                        'coupon_code' => $_POST['coupon_code'],
                        'button_url' => $_POST['button_url'],
                        'url_new_tab' => $_POST['url_new_tab'],
                        'button_text' => $_POST['button_text'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'live_counter' :
            case 'live_counter_bar' :
            case 'inline_live_counter' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'description' => $_POST['description'],
                        'last_activity' => $_POST['last_activity'],
                        'activity_type' => $_POST['activity_type'],
                        'url' => $_POST['url'],
                        'url_new_tab' => $_POST['url_new_tab'],

                        'display_minimum_activity' => $_POST['display_minimum_activity'],

                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'number_background_color' => $_POST['number_background_color'] ?? $notification->settings->number_background_color,
                        'number_color' => $_POST['number_color'] ?? $notification->settings->number_color,
                        'pulse_background_color' => $_POST['pulse_background_color'] ?? $notification->settings->pulse_background_color,

                        /* Dark mode */
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_number_background_color' => $_POST['dark_mode_number_background_color'] ?? $notification->settings->dark_mode_number_background_color,
                        'dark_mode_number_color' => $_POST['dark_mode_number_color'] ?? $notification->settings->dark_mode_number_color,
                        'dark_mode_pulse_background_color' => $_POST['dark_mode_pulse_background_color'] ?? $notification->settings->dark_mode_pulse_background_color,
                    ]
                );

                /* Save inline settings */
                if($notification->type == 'inline_live_counter') {
                    $new_notification_settings = array_merge(
                        $new_notification_settings,
                        [
                            'selector' => $_POST['selector'],
                            'inline_placement' => $_POST['inline_placement'],
                            'margin_top' => $_POST['margin_top'],
                            'margin_right' => $_POST['margin_right'],
                            'margin_bottom' => $_POST['margin_bottom'],
                            'margin_left' => $_POST['margin_left'],
                        ]
                    );
                }

                break;

            case 'email_collector' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'name_placeholder' => $_POST['name_placeholder'],
                        'email_placeholder' => $_POST['email_placeholder'],
                        'button_text' => $_POST['button_text'],
                        'show_agreement' => $_POST['show_agreement'],
                        'agreement_text' => $_POST['agreement_text'],
                        'agreement_url' => $_POST['agreement_url'],
                        'thank_you_url' => $_POST['thank_you_url'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'inline_conversions' :
            case 'conversions' :

                $data_triggers_auto = $notification->settings->data_triggers_auto;

                /* Go over the data triggers auto and clean them */
                foreach($_POST['data_trigger_auto_type'] ?? [] as $key => $value) {
                    $_POST['data_trigger_auto_type'][$key] = in_array($value, ['exact', 'contains', 'starts_with', 'ends_with']) ? query_clean($value) : 'exact';
                }

                foreach($_POST['data_trigger_auto_value'] ?? [] as $key => $value) {
                    $_POST['data_trigger_auto_value'][$key] = query_clean($value);
                }

                /* Generate the trigger rules var */
                $new_data_triggers_auto = [];

                foreach($_POST['data_trigger_auto_type'] ?? [] as $key => $value) {
                    if(empty($_POST['data_trigger_auto_value'][$key])) continue;

                    $new_data_triggers_auto[] = [
                        'type' => $value,
                        'value' => $_POST['data_trigger_auto_value'][$key]
                    ];
                }

                $data_triggers_auto = array_merge($data_triggers_auto, $new_data_triggers_auto);

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'image' => $image,
                        'image_alt' => $_POST['image_alt'],
                        'url' => $_POST['url'],
                        'url_new_tab' => $_POST['url_new_tab'],
                        'conversions_count' => $_POST['conversions_count'],
                        'in_between_delay' => $_POST['in_between_delay'],
                        'infinite_rotation' => $_POST['infinite_rotation'],
                        'order' => $_POST['order'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'date_color' => $_POST['date_color'] ?? $notification->settings->date_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_date_color' => $_POST['dark_mode_date_color'] ?? $notification->settings->dark_mode_date_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,

                        'data_trigger_auto' => $_POST['data_trigger_auto'],
                        'data_triggers_auto' => $data_triggers_auto
                    ]);

                if($notification->type == 'inline_conversions') {
                    $new_notification_settings = array_merge(
                        $new_notification_settings,
                        [
                            'selector' => $_POST['selector'],
                            'inline_placement' => $_POST['inline_placement'],
                            'margin_top' => $_POST['margin_top'],
                            'margin_right' => $_POST['margin_right'],
                            'margin_bottom' => $_POST['margin_bottom'],
                            'margin_left' => $_POST['margin_left'],
                        ]
                    );
                }

                break;

            case 'inline_conversions_counter' :
            case 'conversions_counter' :

                $data_triggers_auto = $notification->settings->data_triggers_auto;

                /* Go over the data triggers auto and clean them */
                foreach($_POST['data_trigger_auto_type'] ?? [] as $key => $value) {
                    $_POST['data_trigger_auto_type'][$key] = in_array($value, ['exact', 'contains', 'starts_with', 'ends_with']) ? query_clean($value) : 'exact';
                }

                foreach($_POST['data_trigger_auto_value'] ?? [] as $key => $value) {
                    $_POST['data_trigger_auto_value'][$key] = query_clean($value);
                }

                /* Generate the trigger rules var */
                $new_data_triggers_auto = [];

                foreach($_POST['data_trigger_auto_type'] ?? [] as $key => $value) {
                    if(empty($_POST['data_trigger_auto_value'][$key])) continue;

                    $new_data_triggers_auto[] = [
                        'type' => $value,
                        'value' => $_POST['data_trigger_auto_value'][$key]
                    ];
                }

                $data_triggers_auto = array_merge($data_triggers_auto, $new_data_triggers_auto);

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'last_activity' => $_POST['last_activity'],
                        'url' => $_POST['url'],
                        'url_new_tab' => $_POST['url_new_tab'],

                        'display_minimum_activity' => $_POST['display_minimum_activity'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'number_background_color' => $_POST['number_background_color'] ?? $notification->settings->number_background_color,
                        'number_color' => $_POST['number_color'] ?? $notification->settings->number_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_number_background_color' => $_POST['dark_mode_number_background_color'] ?? $notification->settings->dark_mode_number_background_color,
                        'dark_mode_number_color' => $_POST['dark_mode_number_color'] ?? $notification->settings->dark_mode_number_color,

                        'data_trigger_auto' => $_POST['data_trigger_auto'],
                        'data_triggers_auto' => $data_triggers_auto
                    ]
                );

                if($notification->type == 'inline_conversions_counter') {
                    $new_notification_settings = array_merge(
                        $new_notification_settings,
                        [
                            'selector' => $_POST['selector'],
                            'inline_placement' => $_POST['inline_placement'],
                            'margin_top' => $_POST['margin_top'],
                            'margin_right' => $_POST['margin_right'],
                            'margin_bottom' => $_POST['margin_bottom'],
                            'margin_left' => $_POST['margin_left'],
                        ]
                    );
                }

                break;

            case 'video' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'video' => $_POST['video'],
                        'video_is_youtube' => $_POST['video_is_youtube'],
                        'video_is_vimeo' => $_POST['video_is_vimeo'],
                        'youtube_video_id' => $_POST['youtube_video_id'],
                        'vimeo_video_id' => $_POST['vimeo_video_id'],
                        'video_autoplay' => $_POST['video_autoplay'],
                        'video_controls' => $_POST['video_controls'],
                        'video_loop' => $_POST['video_loop'],
                        'video_muted' => $_POST['video_muted'],
                        'button_url' => $_POST['button_url'],
                        'button_text' => $_POST['button_text'],
                        'url_new_tab' => $_POST['url_new_tab'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'audio' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'audio' => $audio,
                        'audio_autoplay' => $_POST['audio_autoplay'],
                        'audio_controls' => $_POST['audio_controls'],
                        'audio_loop' => $_POST['audio_loop'],
                        'audio_muted' => $_POST['audio_muted'],
                        'button_url' => $_POST['button_url'],
                        'button_text' => $_POST['button_text'],
                        'url_new_tab' => $_POST['url_new_tab'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'social_share' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'share_url' => $_POST['share_url'],
                        'share_facebook' => $_POST['share_facebook'],
                        'share_x' => $_POST['share_x'],
                        'share_threads' => $_POST['share_threads'],
                        'share_linkedin' => $_POST['share_linkedin'],
                        'share_reddit' => $_POST['share_reddit'],
                        'share_pinterest' => $_POST['share_pinterest'],
                        'share_tumblr' => $_POST['share_tumblr'],
                        'share_telegram' => $_POST['share_telegram'],
                        'share_whatsapp' => $_POST['share_whatsapp'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                    ]
                );

                break;

            case 'inline_reviews' :
            case 'reviews' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'url' => $_POST['url'],
                        'url_new_tab' => $_POST['url_new_tab'],
                        'reviews_count' => $_POST['reviews_count'],
                        'in_between_delay' => $_POST['in_between_delay'],
                        'infinite_rotation' => $_POST['infinite_rotation'],
                        'order' => $_POST['order'],

                        /* Keep the following keys to default */
                        'title' => l('notification.reviews.title_default'),
                        'description' => l('notification.reviews.description_default'),
                        'image' => l('notification.reviews.image_default'),
                        'stars' => 5,

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                    ]
                );

                if($notification->type == 'inline_reviews') {
                    $new_notification_settings = array_merge(
                        $new_notification_settings,
                        [
                            'selector' => $_POST['selector'],
                            'inline_placement' => $_POST['inline_placement'],
                            'margin_top' => $_POST['margin_top'],
                            'margin_right' => $_POST['margin_right'],
                            'margin_bottom' => $_POST['margin_bottom'],
                            'margin_left' => $_POST['margin_left'],
                        ]
                    );
                }

                break;

            case 'inline_rating_summary' :
            case 'rating_summary' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'description' => $_POST['description'],
                        'average_rating' => $_POST['average_rating'],
                        'reviews_count' => $_POST['reviews_count'],
                        'display_minimum_reviews' => $_POST['display_minimum_reviews'],
                        'url' => $_POST['url'],
                        'url_new_tab' => $_POST['url_new_tab'],

                        'rating_color' => $_POST['rating_color'] ?? $notification->settings->rating_color,
                        'rating_background_color' => $_POST['rating_background_color'] ?? ($notification->settings->rating_background_color ?? '#5b83ff'),
                        'stars_color' => $_POST['stars_color'] ?? $notification->settings->stars_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_rating_color' => $_POST['dark_mode_rating_color'] ?? $notification->settings->dark_mode_rating_color,
                        'dark_mode_rating_background_color' => $_POST['dark_mode_rating_background_color'] ?? ($notification->settings->dark_mode_rating_background_color ?? '#002c73'),
                        'dark_mode_stars_color' => $_POST['dark_mode_stars_color'] ?? $notification->settings->dark_mode_stars_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                    ]
                );

                if($notification->type == 'inline_rating_summary') {
                    $new_notification_settings = array_merge(
                        $new_notification_settings,
                        [
                            'selector' => $_POST['selector'],
                            'inline_placement' => $_POST['inline_placement'],
                            'margin_top' => $_POST['margin_top'],
                            'margin_right' => $_POST['margin_right'],
                            'margin_bottom' => $_POST['margin_bottom'],
                            'margin_left' => $_POST['margin_left'],
                        ]
                    );
                }

                break;

            case 'emoji_feedback' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'thank_you_url' => $_POST['thank_you_url'],

                        'show_angry' => $_POST['show_angry'],
                        'show_sad' => $_POST['show_sad'],
                        'show_neutral' => $_POST['show_neutral'],
                        'show_happy' => $_POST['show_happy'],
                        'show_excited' => $_POST['show_excited'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                    ]
                );

                break;

            case 'cookie_notification' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'description' => $_POST['description'],
                        'image' => $image,
                        'image_alt' => $_POST['image_alt'],
                        'url_text' => $_POST['url_text'],
                        'url' => $_POST['url'],
                        'button_text' => $_POST['button_text'],

                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'score_feedback' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'thank_you_url' => $_POST['thank_you_url'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'request_collector' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'image' => $image,
                        'image_alt' => $_POST['image_alt'],
                        'content_title' => $_POST['content_title'],
                        'content_description' => $_POST['content_description'],
                        'input_placeholder' => $_POST['input_placeholder'],
                        'button_text' => $_POST['button_text'],
                        'show_agreement' => $_POST['show_agreement'],
                        'agreement_text' => $_POST['agreement_text'],
                        'agreement_url' => $_POST['agreement_url'],
                        'thank_you_url' => $_POST['thank_you_url'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'content_title_color' => $_POST['content_title_color'] ?? $notification->settings->content_title_color,
                        'content_description_color' => $_POST['content_description_color'] ?? $notification->settings->content_description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_content_title_color' => $_POST['dark_mode_content_title_color'] ?? $notification->settings->dark_mode_content_title_color,
                        'dark_mode_content_description_color' => $_POST['dark_mode_content_description_color'] ?? $notification->settings->dark_mode_content_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'countdown_collector' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'content_title' => $_POST['content_title'],
                        'input_placeholder' => $_POST['input_placeholder'],
                        'button_text' => $_POST['button_text'],
                        'countdown_end_date' => $_POST['countdown_end_date'],
                        'show_agreement' => $_POST['show_agreement'],
                        'agreement_text' => $_POST['agreement_text'],
                        'agreement_url' => $_POST['agreement_url'],
                        'thank_you_url' => $_POST['thank_you_url'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'content_title_color' => $_POST['content_title_color'] ?? $notification->settings->content_title_color,
                        'time_color' => $_POST['time_color'] ?? $notification->settings->time_color,
                        'time_background_color' => $_POST['time_background_color'] ?? $notification->settings->time_background_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_content_title_color' => $_POST['dark_mode_content_title_color'] ?? $notification->settings->dark_mode_content_title_color,
                        'dark_mode_time_color' => $_POST['dark_mode_time_color'] ?? $notification->settings->dark_mode_time_color,
                        'dark_mode_time_background_color' => $_POST['dark_mode_time_background_color'] ?? $notification->settings->dark_mode_time_background_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'badge' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'image' => $image,
                        'image_alt' => $_POST['image_alt'],
                        'url' => $_POST['url'],
                        'url_new_tab' => $_POST['url_new_tab'],

                        'background_color' => $_POST['background_color'],
                        'image_size' => $_POST['image_size'],

                        /* Dark mode */
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'],
                    ]
                );

                break;

            case 'custom_html' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'html' => $_POST['html'],
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                    ]
                );

                break;

            case 'informational_bar' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'image' => $image,
                        'image_alt' => $_POST['image_alt'],
                        'url' => $_POST['url'],
                        'url_new_tab' => $_POST['url_new_tab'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                    ]
                );

                break;

            case 'informational_bar_mini' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'image' => $image,
                        'image_alt' => $_POST['image_alt'],
                        'url' => $_POST['url'],
                        'url_new_tab' => $_POST['url_new_tab'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                    ]
                );

                break;

            case 'image' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'image' => $image,
                        'image_alt' => $_POST['image_alt'],
                        'button_url' => $_POST['button_url'],
                        'button_text' => $_POST['button_text'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'collector_bar' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'input_placeholder' => $_POST['input_placeholder'],
                        'button_text' => $_POST['button_text'],
                        'show_agreement' => $_POST['show_agreement'],
                        'agreement_text' => $_POST['agreement_text'],
                        'agreement_url' => $_POST['agreement_url'],
                        'thank_you_url' => $_POST['thank_you_url'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'coupon_bar' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'coupon_code' => $_POST['coupon_code'],
                        'url' => $_POST['url'],
                        'url_new_tab' => $_POST['url_new_tab'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'coupon_code_color' => $_POST['coupon_code_color'] ?? $notification->settings->coupon_code_color,
                        'coupon_code_background_color' => $_POST['coupon_code_background_color'] ?? $notification->settings->coupon_code_background_color,
                        'coupon_code_border_color' => $_POST['coupon_code_border_color'] ?? $notification->settings->coupon_code_border_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_coupon_code_color' => $_POST['dark_mode_coupon_code_color'] ?? $notification->settings->dark_mode_coupon_code_color,
                        'dark_mode_coupon_code_background_color' => $_POST['dark_mode_coupon_code_background_color'] ?? $notification->settings->dark_mode_coupon_code_background_color,
                        'dark_mode_coupon_code_border_color' => $_POST['dark_mode_coupon_code_border_color'] ?? $notification->settings->dark_mode_coupon_code_border_color,
                    ]
                );

                break;

            case 'button_bar' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'button_text' => $_POST['button_text'],
                        'url' => $_POST['url'],
                        'url_new_tab' => $_POST['url_new_tab'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                    ]
                );

                break;

            case 'collector_modal' :
            case 'collector_two_modal' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'image' => $image,
                        'image_alt' => $_POST['image_alt'],
                        'input_placeholder' => $_POST['input_placeholder'],
                        'button_text' => $_POST['button_text'],
                        'show_agreement' => $_POST['show_agreement'],
                        'agreement_text' => $_POST['agreement_text'],
                        'agreement_url' => $_POST['agreement_url'],
                        'thank_you_url' => $_POST['thank_you_url'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'button_modal' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'image' => $image,
                        'image_alt' => $_POST['image_alt'],
                        'button_text' => $_POST['button_text'],
                        'button_url' => $_POST['button_url'],
                        'url_new_tab' => $_POST['url_new_tab'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'text_feedback' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'input_placeholder' => $_POST['input_placeholder'],
                        'button_text' => $_POST['button_text'],
                        'thank_you_url' => $_POST['thank_you_url'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,
                        'button_background_color' => $_POST['button_background_color'] ?? $notification->settings->button_background_color,
                        'button_color' => $_POST['button_color'] ?? $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => $_POST['dark_mode_button_background_color'] ?? $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => $_POST['dark_mode_button_color'] ?? $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'engagement_links' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'categories' => $_POST['categories'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'categories_title_color' => $_POST['categories_title_color'] ?? $notification->settings->categories_title_color,
                        'categories_description_color' => $_POST['categories_description_color'] ?? $notification->settings->categories_description_color,
                        'categories_links_title_color' => $_POST['categories_links_title_color'] ?? $notification->settings->categories_links_title_color,
                        'categories_links_description_color' => $_POST['categories_links_description_color'] ?? $notification->settings->categories_links_description_color,
                        'categories_links_background_color' => $_POST['categories_links_background_color'] ?? $notification->settings->categories_links_background_color,
                        'categories_links_border_color' => $_POST['categories_links_border_color'] ?? $notification->settings->categories_links_border_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_categories_title_color' => $_POST['dark_mode_categories_title_color'] ?? $notification->settings->dark_mode_categories_title_color,
                        'dark_mode_categories_description_color' => $_POST['dark_mode_categories_description_color'] ?? $notification->settings->dark_mode_categories_description_color,
                        'dark_mode_categories_links_title_color' => $_POST['dark_mode_categories_links_title_color'] ?? $notification->settings->dark_mode_categories_links_title_color,
                        'dark_mode_categories_links_description_color' => $_POST['dark_mode_categories_links_description_color'] ?? $notification->settings->dark_mode_categories_links_description_color,
                        'dark_mode_categories_links_background_color' => $_POST['dark_mode_categories_links_background_color'] ?? $notification->settings->dark_mode_categories_links_background_color,
                        'dark_mode_categories_links_border_color' => $_POST['dark_mode_categories_links_border_color'] ?? $notification->settings->dark_mode_categories_links_border_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                    ]
                );

                break;

            case 'help_center' :

                /* Save help center settings */
                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'badge_text' => $_POST['badge_text'],
                        'hint_text' => $_POST['hint_text'],
                        'button_text' => $_POST['button_text'],
                        'button_url' => $_POST['button_url'],
                        'url_new_tab' => $_POST['url_new_tab'],
                        'items' => $_POST['items'],

                        'badge_background_color' => isset($_POST['badge_background_color']) ? $_POST['badge_background_color'] : $notification->settings->badge_background_color,
                        'badge_color' => isset($_POST['badge_color']) ? $_POST['badge_color'] : $notification->settings->badge_color,
                        'hint_background_color' => isset($_POST['hint_background_color']) ? $_POST['hint_background_color'] : $notification->settings->hint_background_color,
                        'hint_color' => isset($_POST['hint_color']) ? $_POST['hint_color'] : $notification->settings->hint_color,
                        'title_color' => isset($_POST['title_color']) ? $_POST['title_color'] : $notification->settings->title_color,
                        'description_color' => isset($_POST['description_color']) ? $_POST['description_color'] : $notification->settings->description_color,
                        'item_question_color' => isset($_POST['item_question_color']) ? $_POST['item_question_color'] : $notification->settings->item_question_color,
                        'item_answer_color' => isset($_POST['item_answer_color']) ? $_POST['item_answer_color'] : $notification->settings->item_answer_color,
                        'item_background_color' => isset($_POST['item_background_color']) ? $_POST['item_background_color'] : $notification->settings->item_background_color,
                        'item_border_color' => isset($_POST['item_border_color']) ? $_POST['item_border_color'] : $notification->settings->item_border_color,
                        'background_color' => isset($_POST['background_color']) ? $_POST['background_color'] : $notification->settings->background_color,
                        'button_background_color' => isset($_POST['button_background_color']) ? $_POST['button_background_color'] : $notification->settings->button_background_color,
                        'button_color' => isset($_POST['button_color']) ? $_POST['button_color'] : $notification->settings->button_color,

                        /* Dark mode */
                        'dark_mode_badge_background_color' => isset($_POST['dark_mode_badge_background_color']) ? $_POST['dark_mode_badge_background_color'] : $notification->settings->dark_mode_badge_background_color,
                        'dark_mode_badge_color' => isset($_POST['dark_mode_badge_color']) ? $_POST['dark_mode_badge_color'] : $notification->settings->dark_mode_badge_color,
                        'dark_mode_hint_background_color' => isset($_POST['dark_mode_hint_background_color']) ? $_POST['dark_mode_hint_background_color'] : $notification->settings->dark_mode_hint_background_color,
                        'dark_mode_hint_color' => isset($_POST['dark_mode_hint_color']) ? $_POST['dark_mode_hint_color'] : $notification->settings->dark_mode_hint_color,
                        'dark_mode_title_color' => isset($_POST['dark_mode_title_color']) ? $_POST['dark_mode_title_color'] : $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => isset($_POST['dark_mode_description_color']) ? $_POST['dark_mode_description_color'] : $notification->settings->dark_mode_description_color,
                        'dark_mode_item_question_color' => isset($_POST['dark_mode_item_question_color']) ? $_POST['dark_mode_item_question_color'] : $notification->settings->dark_mode_item_question_color,
                        'dark_mode_item_answer_color' => isset($_POST['dark_mode_item_answer_color']) ? $_POST['dark_mode_item_answer_color'] : $notification->settings->dark_mode_item_answer_color,
                        'dark_mode_item_background_color' => isset($_POST['dark_mode_item_background_color']) ? $_POST['dark_mode_item_background_color'] : $notification->settings->dark_mode_item_background_color,
                        'dark_mode_item_border_color' => isset($_POST['dark_mode_item_border_color']) ? $_POST['dark_mode_item_border_color'] : $notification->settings->dark_mode_item_border_color,
                        'dark_mode_background_color' => isset($_POST['dark_mode_background_color']) ? $_POST['dark_mode_background_color'] : $notification->settings->dark_mode_background_color,
                        'dark_mode_button_background_color' => isset($_POST['dark_mode_button_background_color']) ? $_POST['dark_mode_button_background_color'] : $notification->settings->dark_mode_button_background_color,
                        'dark_mode_button_color' => isset($_POST['dark_mode_button_color']) ? $_POST['dark_mode_button_color'] : $notification->settings->dark_mode_button_color,
                    ]
                );

                break;

            case 'whatsapp_chat' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'agent_image' => $_POST['agent_image'],
                        'agent_image_alt' => $_POST['agent_image_alt'],
                        'agent_name' => $_POST['agent_name'],
                        'agent_description' => $_POST['agent_description'],
                        'agent_message' => $_POST['agent_message'],
                        'agent_phone_number' => $_POST['agent_phone_number'],
                        'button_text' => $_POST['button_text'],

                        'header_agent_name_color' => $_POST['header_agent_name_color'] ?? $notification->settings->header_agent_name_color,
                        'header_agent_description_color' => $_POST['header_agent_description_color'] ?? $notification->settings->header_agent_description_color,
                        'header_background_color' => $_POST['header_background_color'] ?? $notification->settings->header_background_color,
                        'content_background_color' => $_POST['content_background_color'] ?? $notification->settings->content_background_color,
                        'content_agent_name_color' => $_POST['content_agent_name_color'] ?? $notification->settings->content_agent_name_color,
                        'content_agent_message_color' => $_POST['content_agent_message_color'] ?? $notification->settings->content_agent_message_color,
                        'content_agent_message_background_color' => $_POST['content_agent_message_background_color'] ?? $notification->settings->content_agent_message_background_color,
                        'footer_background_color' => $_POST['footer_background_color'] ?? $notification->settings->footer_background_color,
                        'footer_button_background_color' => $_POST['footer_button_background_color'] ?? $notification->settings->footer_button_background_color,
                        'footer_button_color' => $_POST['footer_button_color'] ?? $notification->settings->footer_button_color,
                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_header_agent_name_color' => $_POST['dark_mode_header_agent_name_color'] ?? $notification->settings->dark_mode_header_agent_name_color,
                        'dark_mode_header_agent_description_color' => $_POST['dark_mode_header_agent_description_color'] ?? $notification->settings->dark_mode_header_agent_description_color,
                        'dark_mode_header_background_color' => $_POST['dark_mode_header_background_color'] ?? $notification->settings->dark_mode_header_background_color,
                        'dark_mode_content_background_color' => $_POST['dark_mode_content_background_color'] ?? $notification->settings->dark_mode_content_background_color,
                        'dark_mode_content_agent_name_color' => $_POST['dark_mode_content_agent_name_color'] ?? $notification->settings->dark_mode_content_agent_name_color,
                        'dark_mode_content_agent_message_color' => $_POST['dark_mode_content_agent_message_color'] ?? $notification->settings->dark_mode_content_agent_message_color,
                        'dark_mode_content_agent_message_background_color' => $_POST['dark_mode_content_agent_message_background_color'] ?? $notification->settings->dark_mode_content_agent_message_background_color,
                        'dark_mode_footer_background_color' => $_POST['dark_mode_footer_background_color'] ?? $notification->settings->dark_mode_footer_background_color,
                        'dark_mode_footer_button_background_color' => $_POST['dark_mode_footer_button_background_color'] ?? $notification->settings->dark_mode_footer_button_background_color,
                        'dark_mode_footer_button_color' => $_POST['dark_mode_footer_button_color'] ?? $notification->settings->dark_mode_footer_button_color,
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                    ]
                );

                break;

            case 'telegram_chat' :

                /* Save Telegram chat settings */
                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'agent_image' => $_POST['agent_image'],
                        'agent_image_alt' => $_POST['agent_image_alt'],
                        'agent_name' => $_POST['agent_name'],
                        'agent_description' => $_POST['agent_description'],
                        'agent_message' => $_POST['agent_message'],
                        'agent_telegram' => $_POST['agent_telegram'],
                        'button_text' => $_POST['button_text'],

                        'header_agent_name_color' => isset($_POST['header_agent_name_color']) ? $_POST['header_agent_name_color'] : $notification->settings->header_agent_name_color,
                        'header_agent_description_color' => isset($_POST['header_agent_description_color']) ? $_POST['header_agent_description_color'] : $notification->settings->header_agent_description_color,
                        'header_background_color' => isset($_POST['header_background_color']) ? $_POST['header_background_color'] : $notification->settings->header_background_color,
                        'content_background_color' => isset($_POST['content_background_color']) ? $_POST['content_background_color'] : $notification->settings->content_background_color,
                        'content_agent_name_color' => isset($_POST['content_agent_name_color']) ? $_POST['content_agent_name_color'] : $notification->settings->content_agent_name_color,
                        'content_agent_message_color' => isset($_POST['content_agent_message_color']) ? $_POST['content_agent_message_color'] : $notification->settings->content_agent_message_color,
                        'content_agent_message_background_color' => isset($_POST['content_agent_message_background_color']) ? $_POST['content_agent_message_background_color'] : $notification->settings->content_agent_message_background_color,
                        'footer_background_color' => isset($_POST['footer_background_color']) ? $_POST['footer_background_color'] : $notification->settings->footer_background_color,
                        'footer_button_background_color' => isset($_POST['footer_button_background_color']) ? $_POST['footer_button_background_color'] : $notification->settings->footer_button_background_color,
                        'footer_button_color' => isset($_POST['footer_button_color']) ? $_POST['footer_button_color'] : $notification->settings->footer_button_color,
                        'title_color' => isset($_POST['title_color']) ? $_POST['title_color'] : $notification->settings->title_color,
                        'background_color' => isset($_POST['background_color']) ? $_POST['background_color'] : $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_header_agent_name_color' => isset($_POST['dark_mode_header_agent_name_color']) ? $_POST['dark_mode_header_agent_name_color'] : $notification->settings->dark_mode_header_agent_name_color,
                        'dark_mode_header_agent_description_color' => isset($_POST['dark_mode_header_agent_description_color']) ? $_POST['dark_mode_header_agent_description_color'] : $notification->settings->dark_mode_header_agent_description_color,
                        'dark_mode_header_background_color' => isset($_POST['dark_mode_header_background_color']) ? $_POST['dark_mode_header_background_color'] : $notification->settings->dark_mode_header_background_color,
                        'dark_mode_content_background_color' => isset($_POST['dark_mode_content_background_color']) ? $_POST['dark_mode_content_background_color'] : $notification->settings->dark_mode_content_background_color,
                        'dark_mode_content_agent_name_color' => isset($_POST['dark_mode_content_agent_name_color']) ? $_POST['dark_mode_content_agent_name_color'] : $notification->settings->dark_mode_content_agent_name_color,
                        'dark_mode_content_agent_message_color' => isset($_POST['dark_mode_content_agent_message_color']) ? $_POST['dark_mode_content_agent_message_color'] : $notification->settings->dark_mode_content_agent_message_color,
                        'dark_mode_content_agent_message_background_color' => isset($_POST['dark_mode_content_agent_message_background_color']) ? $_POST['dark_mode_content_agent_message_background_color'] : $notification->settings->dark_mode_content_agent_message_background_color,
                        'dark_mode_footer_background_color' => isset($_POST['dark_mode_footer_background_color']) ? $_POST['dark_mode_footer_background_color'] : $notification->settings->dark_mode_footer_background_color,
                        'dark_mode_footer_button_background_color' => isset($_POST['dark_mode_footer_button_background_color']) ? $_POST['dark_mode_footer_button_background_color'] : $notification->settings->dark_mode_footer_button_background_color,
                        'dark_mode_footer_button_color' => isset($_POST['dark_mode_footer_button_color']) ? $_POST['dark_mode_footer_button_color'] : $notification->settings->dark_mode_footer_button_color,
                        'dark_mode_title_color' => isset($_POST['dark_mode_title_color']) ? $_POST['dark_mode_title_color'] : $notification->settings->dark_mode_title_color,
                        'dark_mode_background_color' => isset($_POST['dark_mode_background_color']) ? $_POST['dark_mode_background_color'] : $notification->settings->dark_mode_background_color,
                    ]
                );

                break;

            case 'facebook_messenger_chat' :

                /* Save Messenger chat settings */
                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'agent_image' => $_POST['agent_image'],
                        'agent_image_alt' => $_POST['agent_image_alt'],
                        'agent_name' => $_POST['agent_name'],
                        'agent_description' => $_POST['agent_description'],
                        'agent_message' => $_POST['agent_message'],
                        'agent_facebook_messenger' => $_POST['agent_facebook_messenger'],
                        'button_text' => $_POST['button_text'],

                        'header_agent_name_color' => isset($_POST['header_agent_name_color']) ? $_POST['header_agent_name_color'] : $notification->settings->header_agent_name_color,
                        'header_agent_description_color' => isset($_POST['header_agent_description_color']) ? $_POST['header_agent_description_color'] : $notification->settings->header_agent_description_color,
                        'header_background_color' => isset($_POST['header_background_color']) ? $_POST['header_background_color'] : $notification->settings->header_background_color,
                        'content_background_color' => isset($_POST['content_background_color']) ? $_POST['content_background_color'] : $notification->settings->content_background_color,
                        'content_agent_name_color' => isset($_POST['content_agent_name_color']) ? $_POST['content_agent_name_color'] : $notification->settings->content_agent_name_color,
                        'content_agent_message_color' => isset($_POST['content_agent_message_color']) ? $_POST['content_agent_message_color'] : $notification->settings->content_agent_message_color,
                        'content_agent_message_background_color' => isset($_POST['content_agent_message_background_color']) ? $_POST['content_agent_message_background_color'] : $notification->settings->content_agent_message_background_color,
                        'footer_background_color' => isset($_POST['footer_background_color']) ? $_POST['footer_background_color'] : $notification->settings->footer_background_color,
                        'footer_button_background_color' => isset($_POST['footer_button_background_color']) ? $_POST['footer_button_background_color'] : $notification->settings->footer_button_background_color,
                        'footer_button_color' => isset($_POST['footer_button_color']) ? $_POST['footer_button_color'] : $notification->settings->footer_button_color,
                        'title_color' => isset($_POST['title_color']) ? $_POST['title_color'] : $notification->settings->title_color,
                        'background_color' => isset($_POST['background_color']) ? $_POST['background_color'] : $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_header_agent_name_color' => isset($_POST['dark_mode_header_agent_name_color']) ? $_POST['dark_mode_header_agent_name_color'] : $notification->settings->dark_mode_header_agent_name_color,
                        'dark_mode_header_agent_description_color' => isset($_POST['dark_mode_header_agent_description_color']) ? $_POST['dark_mode_header_agent_description_color'] : $notification->settings->dark_mode_header_agent_description_color,
                        'dark_mode_header_background_color' => isset($_POST['dark_mode_header_background_color']) ? $_POST['dark_mode_header_background_color'] : $notification->settings->dark_mode_header_background_color,
                        'dark_mode_content_background_color' => isset($_POST['dark_mode_content_background_color']) ? $_POST['dark_mode_content_background_color'] : $notification->settings->dark_mode_content_background_color,
                        'dark_mode_content_agent_name_color' => isset($_POST['dark_mode_content_agent_name_color']) ? $_POST['dark_mode_content_agent_name_color'] : $notification->settings->dark_mode_content_agent_name_color,
                        'dark_mode_content_agent_message_color' => isset($_POST['dark_mode_content_agent_message_color']) ? $_POST['dark_mode_content_agent_message_color'] : $notification->settings->dark_mode_content_agent_message_color,
                        'dark_mode_content_agent_message_background_color' => isset($_POST['dark_mode_content_agent_message_background_color']) ? $_POST['dark_mode_content_agent_message_background_color'] : $notification->settings->dark_mode_content_agent_message_background_color,
                        'dark_mode_footer_background_color' => isset($_POST['dark_mode_footer_background_color']) ? $_POST['dark_mode_footer_background_color'] : $notification->settings->dark_mode_footer_background_color,
                        'dark_mode_footer_button_background_color' => isset($_POST['dark_mode_footer_button_background_color']) ? $_POST['dark_mode_footer_button_background_color'] : $notification->settings->dark_mode_footer_button_background_color,
                        'dark_mode_footer_button_color' => isset($_POST['dark_mode_footer_button_color']) ? $_POST['dark_mode_footer_button_color'] : $notification->settings->dark_mode_footer_button_color,
                        'dark_mode_title_color' => isset($_POST['dark_mode_title_color']) ? $_POST['dark_mode_title_color'] : $notification->settings->dark_mode_title_color,
                        'dark_mode_background_color' => isset($_POST['dark_mode_background_color']) ? $_POST['dark_mode_background_color'] : $notification->settings->dark_mode_background_color,
                    ]
                );

                break;

            case 'contact_us' :

                $new_notification_settings = array_merge(
                    $new_notification_settings,
                    [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'contact_email' => $_POST['contact_email'],
                        'contact_phone_number' => $_POST['contact_phone_number'],
                        'contact_whatsapp' => $_POST['contact_whatsapp'],
                        'contact_telegram' => $_POST['contact_telegram'],
                        'contact_facebook_messenger' => $_POST['contact_facebook_messenger'],

                        'title_color' => $_POST['title_color'] ?? $notification->settings->title_color,
                        'description_color' => $_POST['description_color'] ?? $notification->settings->description_color,
                        'background_color' => $_POST['background_color'] ?? $notification->settings->background_color,

                        /* Dark mode */
                        'dark_mode_title_color' => $_POST['dark_mode_title_color'] ?? $notification->settings->dark_mode_title_color,
                        'dark_mode_description_color' => $_POST['dark_mode_description_color'] ?? $notification->settings->dark_mode_description_color,
                        'dark_mode_background_color' => $_POST['dark_mode_background_color'] ?? $notification->settings->dark_mode_background_color,
                    ]
                );

                break;
        }

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Notification handlers */
        $_POST['notifications'] = array_map(
            'intval',
            array_filter($_POST['notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
            $_POST['notifications'] = array_slice($_POST['notifications'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
        }

        /* Notifications */
        $notifications = json_encode($_POST['notifications']);

        /* Database query */
        db()->where('notification_id', $notification->notification_id)->update('notifications', [
            'name' => $name,
            'settings' => json_encode($new_notification_settings),
            'notifications' => $notifications,
            'is_enabled' => $_POST['is_enabled'],
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('campaign_id=' . $notification->campaign_id);

        /* Prepare the data */
        $data = [
            'id' => $notification_id,
            'user_id' => (int) $this->user->user_id,
            'campaign_id' => $notification->campaign_id,
            'notification_key' => $notification->notification_key,
            'name' => $name,
            'type' => $notification->type,
            'settings' => $new_notification_settings,
            'impressions' => (int) $notification->impressions,
            'hovers' => (int) $notification->hovers,
            'clicks' => (int) $notification->clicks,
            'form_submissions' => (int) $notification->form_submissions,
            'is_enabled' => $_POST['is_enabled'],
            'last_datetime' => get_date(),
            'datetime' => $notification->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $notification_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $notification = db()->where('notification_id', $notification_id)->where('user_id', $this->user->user_id)->getOne('notifications');

        /* We haven't found the resource */
        if(!$notification) {
            $this->return_404();
        }

        (new \Altum\Models\Notification())->delete($notification_id);

        http_response_code(200);
        die();

    }

}
