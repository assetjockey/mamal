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
use Altum\Meta;
use Altum\Models\User;
use Altum\Title;

defined('ALTUMCODE') || die();

class StatusPage extends Controller {
    public $status_page = null;
    public $status_page_user = null;
    public $has_access = null;

    public function index() {

        $this->init();

        /* Make sure there are no extra URL additions */
        if(isset($this->params[1])) {
            throw_404();
        }

        /* Check if the password form is submitted */
        if(!$this->has_access && !empty($_POST)) {
            $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);

            /* Check for any errors */
            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!password_verify($_POST['password'], $this->status_page->password)) {
                Alerts::add_field_error('password', l('s_status_page.password.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Set a cookie */
                setcookie('status_page_password_' . $this->status_page->status_page_id, $this->status_page->password, time()+60*60*24*30);

                header('Location: ' . $this->status_page->full_url); die();

            }

        }

        /* Display the password form */
        if(!$this->has_access) {

            /* Set a custom title */
            Title::set(l('s_status_page.password.title'));

            /* Main View */
            $data = [
                'status_page' => $this->status_page,
            ];

            $view = new \Altum\View('s/status-page/' . $this->status_page->theme . '/password', (array) $this);

        }

        /* No password or access granted */
        else {

            $this->create_statistics($this->status_page->status_page_id);

            /* Prepare date selector stuff */
            $start_date = isset($_GET['start_date']) ? query_clean($_GET['start_date']) : Date::get('', 4);
            $end_date = isset($_GET['end_date']) ? query_clean($_GET['end_date']) : Date::get('', 4);
            $date = \Altum\Date::get_start_end_dates($start_date, $end_date);

            /* Get all the available monitors */
            $monitors = (new \Altum\Models\Monitors())->get_monitors_by_monitors_ids($this->status_page->monitors_ids);

            /* Get all the available heartbeats */
            $heartbeats = (new \Altum\Models\Heartbeats())->get_heartbeats_by_heartbeats_ids($this->status_page->heartbeats_ids);

            /* Detect the overall status */
            $resources_status = 1;

            /* Earliest datetime available */
            $status_page_earliest_datetime_available = (new \DateTime());

            /* Go through each monitor */
            foreach($monitors as $monitor) {

                if(!$monitor->is_ok) {
                    $resources_status = 0;
                }

                if((new \DateTime($monitor->datetime)) < $status_page_earliest_datetime_available) {
                    $status_page_earliest_datetime_available = $monitor->datetime;
                }

                /* Get logs */
                if($this->status_page->theme == 'new-york') {
                    $monitor_logs_processed = (new \Altum\Models\MonitorsLogs())->get_monitor_logs_chart_data_by_monitor_id($monitor->monitor_id, $date->start_date_query, $date->end_date_query);
                } elseif($this->status_page->theme == 'phoenix') {
                    $monitor_logs_processed = (new \Altum\Models\MonitorsLogs())->get_monitor_logs_data_by_monitor_id($monitor->monitor_id, $date->start_date_query, $date->end_date_query);
                }

                $total_monitor_logs = $monitor_logs_processed['total_logs'];
                $total_ok_checks = $monitor_logs_processed['total_ok_checks'];
                $total_not_ok_checks = $monitor_logs_processed['total_not_ok_checks'];
                $total_response_time = $monitor_logs_processed['total_response_time'];
                $monitor->monitor_logs_chart = $monitor_logs_processed['chart'] ?? null;
                $monitor->monitor_logs = $monitor_logs_processed['logs'] ?? null;

                /* calculate some data */
                $monitor->monitor_logs_data = [
                    'total_monitor_logs' => $total_monitor_logs,
                    'uptime' => $total_ok_checks > 0 ? $total_ok_checks / ($total_ok_checks + $total_not_ok_checks) * 100 : 0,
                    'downtime' => 100 - $monitor->uptime,
                    'total_not_ok_checks' => $total_not_ok_checks,
                    'average_response_time' => $total_monitor_logs > 0 ? $total_response_time / $total_monitor_logs : 0
                ];
            }

            /* Go through each heartbeat */
            foreach($heartbeats as $heartbeat) {

                if(!$heartbeat->is_ok) {
                    $resources_status = 0;
                }

                if((new \DateTime($heartbeat->datetime)) < $status_page_earliest_datetime_available) {
                    $status_page_earliest_datetime_available = $heartbeat->datetime;
                }

                /* Get logs */
                if($this->status_page->theme == 'new-york') {
                    $heartbeat_logs_processed = (new \Altum\Models\HeartbeatsLogs())->get_heartbeat_logs_chart_data_by_heartbeat_id($heartbeat->heartbeat_id, $date->start_date_query, $date->end_date_query);
                } elseif($this->status_page->theme == 'phoenix') {
                    $heartbeat_logs_processed = (new \Altum\Models\HeartbeatsLogs())->get_heartbeat_logs_data_by_heartbeat_id($heartbeat->heartbeat_id, $date->start_date_query, $date->end_date_query);
                }

                $total_heartbeat_logs = $heartbeat_logs_processed['total_logs'];
                $total_ok_checks = $heartbeat_logs_processed['total_ok_checks'];
                $total_not_ok_checks = $heartbeat_logs_processed['total_not_ok_checks'];
                $heartbeat->heartbeat_logs_chart = $heartbeat_logs_processed['chart'] ?? null;
                $heartbeat->heartbeat_logs = $heartbeat_logs_processed['logs'] ?? null;

                /* calculate some data */
                $heartbeat->heartbeat_logs_data = [
                    'total_heartbeat_logs' => $total_heartbeat_logs,
                    'uptime' => $total_ok_checks > 0 ? $total_ok_checks / ($total_ok_checks + $total_not_ok_checks) * 100 : 0,
                    'downtime' => 100 - $heartbeat->uptime,
                    'total_not_ok_checks' => $total_not_ok_checks,
                ];
            }

            /* Set a custom title */
            if($this->status_page->settings->title) {
                Title::set($this->status_page->settings->title, true);
            } else {
                Title::set($this->status_page->name);
            }

            /* Set the meta tags */
            if($this->status_page->settings->meta_description) {
                Meta::set_description(string_truncate($this->status_page->settings->meta_description, 160));
                Meta::set_social_description(string_truncate($this->status_page->settings->meta_description, 160));
            } else {
                Meta::set_description(string_truncate($this->status_page->description, 160));
                Meta::set_social_description(string_truncate($this->status_page->description, 160));
            }
            if($this->status_page->settings->meta_keywords) {
                Meta::set_keywords(string_truncate($this->status_page->settings->meta_keywords, 160));
            }
            Meta::set_social_url($this->status_page->full_url);
            Meta::set_social_title($this->status_page->settings->title ?? $this->status_page->name);
            Meta::set_social_image(!empty($this->status_page->opengraph) ? \Altum\Uploads::get_full_url('status_pages_opengraph') . $this->status_page->opengraph : null);

            /* Prepare the header */
            $view = new \Altum\View('s/partials/header', (array) $this);
            $this->add_view_content('header', $view->run(['status_page' => $this->status_page]));

            /* Main View */
            $data = [
                'status_page' => $this->status_page,
                'status_page_user' => $this->status_page_user,
                'monitors' => $monitors,
                'heartbeats' => $heartbeats,
                'resources_status' => $resources_status,
                'date' => $date,
                'status_page_earliest_datetime_available' => $status_page_earliest_datetime_available
            ];

            $view = new \Altum\View('s/status-page/' . $this->status_page->theme . '/index', (array) $this);
        }

        $this->add_view_content('content', $view->run($data));
    }

    public function init() {

        if(!settings()->status_pages->status_pages_is_enabled) {
            throw_404();
        }

        if(isset(\Altum\Router::$data['status_page'])) {
            $status_page = $this->status_page = \Altum\Router::$data['status_page'];
        }

        /* Make sure the vcard is active */
        if(!$this->status_page->is_enabled) {
            throw_404();
        }

        $this->status_page_user = (new User())->get_user_by_user_id($this->status_page->user_id);

        /* Make sure to check if the user is active */
        if($this->status_page_user->status != 1) {
            throw_404();
        }

        /* Process the plan of the user */
        (new User())->process_user_plan_expiration_by_user($this->status_page_user);

        /* Check if the user has access to the status_page */
        $has_access = !$status_page->password || ($status_page->password && isset($_COOKIE['status_page_password_' . $this->status_page->status_page_id]) && $_COOKIE['status_page_password_' . $this->status_page->status_page_id] == $status_page->password);

        /* Do not let the user have password protection if the plan doesnt allow it */
        if(!$this->status_page_user->plan_settings->password_protection_is_enabled) {
            $has_access = true;
        }

        $this->has_access = $has_access;

        /* Parse some details */
        foreach(['monitors_ids', 'heartbeats_ids', 'socials', 'settings'] as $key) {
            $status_page->{$key} = json_decode($status_page->{$key});
        }

        /* Set the default language of the user, including the status page timezone */
        \Altum\Language::set_by_name($this->status_page_user->language);
        \Altum\Date::$timezone = $this->status_page->timezone;

        /* Meta */
        Meta::set_canonical_url($this->status_page->full_url);
    }

    /* Insert statistics log */
    public function create_statistics($status_page_id = null) {

        $cookie_name = 's_statistics_' . $status_page_id;

        if(isset($_COOKIE[$cookie_name]) && (int) $_COOKIE[$cookie_name] >= 3) {
            return;
        }

        if(!$this->status_page_user->plan_settings->analytics_is_enabled) {
            return;
        }

        /* Detect extra details about the user */
        $whichbrowser = get_whichbrowser();

        /* Do not track bots */
        if($whichbrowser->device->type == 'bot') {
            return;
        }

        /* Ignore excluded ips */
        $excluded_ips = array_flip($this->status_page_user->preferences->excluded_ips ?? []);
        if(isset($excluded_ips[get_ip()])) return;

        /* Detect extra details about the user */
        $browser_name = $whichbrowser->browser->name ?? null;
        $os_name = $whichbrowser->os->name ?? null;
        $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
        $device_type = get_this_device_type();
        $is_unique = isset($_COOKIE[$cookie_name]) ? 0 : 1;

        /* Detect the location */
        try {
            $maxmind = (get_maxmind_reader_city())->get(get_ip());
        } catch(\Exception $exception) {
            /* :) */
        }
        $continent_code = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['code'] : null;
        $country_code = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
        $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;

        /* Process referrer */
        $referrer = [
            'host' => null,
            'path' => null
        ];

        if(isset($_SERVER['HTTP_REFERER'])) {
            $referrer = parse_url($_SERVER['HTTP_REFERER']);

            if($_SERVER['HTTP_REFERER'] == $this->status_page->full_url) {
                $is_unique = 0;

                $referrer = [
                    'host' => null,
                    'path' => null
                ];
            }
        }

        /* Check if referrer actually comes from the QR code */
        if(isset($_GET['referrer']) && $_GET['referrer'] == 'qr') {
            $referrer = [
                'host' => 'qr',
                'path' => null
            ];
        }

        $utm_source = input_clean($_GET['utm_source'] ?? null);
        $utm_medium = input_clean($_GET['utm_medium'] ?? null);
        $utm_campaign = input_clean($_GET['utm_campaign'] ?? null);

        /* Insert the log */
        db()->insert('statistics', [
            'status_page_id' => $status_page_id,
            'user_id' => $this->status_page_user->user_id,
            'continent_code' => $continent_code,
            'country_code' => $country_code,
            'city_name' => $city_name,
            'os_name' => $os_name,
            'browser_name' => $browser_name,
            'referrer_host' => $referrer['host'],
            'referrer_path' => $referrer['path'],
            'device_type' => $device_type,
            'browser_language' => $browser_language,
            'utm_source' => $utm_source,
            'utm_medium' => $utm_medium,
            'utm_campaign' => $utm_campaign,
            'is_unique' => $is_unique,
            'datetime' => get_date(),
        ]);

        /* Add the unique hit to the status_page table as well */
        db()->where('status_page_id', $status_page_id)->update('status_pages', ['pageviews' => db()->inc()]);

        /* Set cookie to try and avoid multiple entrances */
        $cookie_new_value = isset($_COOKIE[$cookie_name]) ? (int) $_COOKIE[$cookie_name] + 1 : 0;
        setcookie($cookie_name, (int) $cookie_new_value, time()+60*60*24*1);
    }

}
