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
namespace Altum\Controllers;

use Altum\Alerts;

defined('ALTUMCODE') || die();

class WebsitesImport extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if($this->team) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('websites');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `websites` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->websites_limit != -1 && $total_rows >= $this->user->plan_settings->websites_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('websites');
        }

        if(!empty($_POST)) {
            if(!isset($_FILES['file'])) {
                Alerts::add_error(l('global.error_message.empty_field'));
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            \Altum\Uploads::validate_upload('resources_csv', 'file', get_max_upload());

            $csv_array = array_map(function($line) {
                return str_getcsv($line, ',', '"', '\\');
            }, file($_FILES['file']['tmp_name']));

            if(!$csv_array || !is_array($csv_array)) {
                Alerts::add_error(l('global.error_message.invalid_file_type'));
            }

            $headers_array = $csv_array[0];
            unset($csv_array[0]);
            reset($csv_array);

            if(!Alerts::has_errors()) {
                /* Get available custom domains */
                $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

                /* Count the successful inserts */
                $imported_count = 0;

                foreach($csv_array as $csv_row) {
                    /* Skip wrong lines */
                    if(count($headers_array) != count($csv_row)) continue;

                    /* Extract data */
                    $data = array_combine($headers_array, $csv_row);

                    /* Clean & validate input */
                    $name = input_clean($data['name'] ?? '', 256);
                    $scheme = in_array($data['scheme'] ?? '', ['https://', 'http://']) ? $data['scheme'] : 'https://';
                    $host = str_replace(' ', '', mb_strtolower(input_clean($data['host'] ?? '', 128)));
                    $host = string_starts_with('http://', $host) || string_starts_with('https://', $host) ? parse_url($host, PHP_URL_HOST) : $host;
                    $excluded_ips = implode(',', array_map(function($value) { return query_clean(trim($value)); }, explode(',', $data['excluded_ips'] ?? '')));
                    $sessions_replays_hide_text_selector = input_clean($data['sessions_replays_hide_text_selector'] ?? '', 512);

                    $domain_id = !empty($data['domain_id']) && array_key_exists($data['domain_id'], $domains) ? (int) $data['domain_id'] : null;

                    $outbound_clicks_is_enabled = isset($data['outbound_clicks_is_enabled']) ? (int) $data['outbound_clicks_is_enabled'] : 0;
                    $events_children_is_enabled = isset($data['events_children_is_enabled']) ? (int) $data['events_children_is_enabled'] : 0;
                    $sessions_replays_is_enabled = isset($data['sessions_replays_is_enabled']) ? (int) $data['sessions_replays_is_enabled'] : 0;
                    $email_reports_is_enabled = $this->user->plan_settings->email_reports_is_enabled ? (int) ($data['email_reports_is_enabled'] ?? 0) : 0;
                    $bot_exclusion_is_enabled = isset($data['bot_exclusion_is_enabled']) ? (int) $data['bot_exclusion_is_enabled'] : 0;
                    $query_parameters_tracking_is_enabled = isset($data['query_parameters_tracking_is_enabled']) ? (int) $data['query_parameters_tracking_is_enabled'] : 0;
                    $ip_storage_is_enabled = isset($data['ip_storage_is_enabled']) ? (int) $data['ip_storage_is_enabled'] : 0;
                    $public_statistics_is_enabled = isset($data['public_statistics_is_enabled']) ? (int) $data['public_statistics_is_enabled'] : 0;
                    $data['public_statistics_password'] = mb_substr($data['public_statistics_password'] ?? '', 0, 64);
                    $public_statistics_password = !empty($data['public_statistics_password']) ? password_hash($data['public_statistics_password'], PASSWORD_DEFAULT) : null;

                    if(!$name || !$host) continue;

                    if(in_array(get_domain_from_url($host), settings()->analytics->blacklisted_domains)) continue;

                    /* Domain normalization */
                    $path = null;
                    if(function_exists('idn_to_utf8')) {
                        $path = parse_url($scheme . idn_to_utf8($host), PHP_URL_PATH);
                        $host = parse_url($scheme . idn_to_utf8($host), PHP_URL_HOST);
                    }

                    if(function_exists('idn_to_ascii')) {
                        $host = idn_to_ascii($host);
                    }

                    if(in_array($host, settings()->analytics->blacklisted_domains)) continue;

                    /* Generate unique pixel key */
                    $pixel_key = string_generate(16);
                    while(db()->where('pixel_key', $pixel_key)->getOne('websites', ['pixel_key'])) {
                        $pixel_key = string_generate(16);
                    }

                    /* Is is_enabled */
                    $is_enabled = !empty($data['is_enabled']) ? (int) isset($data['is_enabled']) : 1;

                    db()->insert('websites', [
                        'user_id' => $this->user->user_id,
                        'domain_id' => $domain_id,
                        'pixel_key' => $pixel_key,
                        'name' => $name,
                        'scheme' => $scheme,
                        'host' => $host,
                        'path' => $path,
                        'excluded_ips' => $excluded_ips,
                        'outbound_clicks_is_enabled' => $outbound_clicks_is_enabled,
                        'events_children_is_enabled' => $events_children_is_enabled,
                        'sessions_replays_is_enabled' => $sessions_replays_is_enabled,
                        'sessions_replays_hide_text_selector' => $sessions_replays_hide_text_selector,
                        'email_reports_is_enabled' => $email_reports_is_enabled,
                        'bot_exclusion_is_enabled' => $bot_exclusion_is_enabled,
                        'query_parameters_tracking_is_enabled' => $query_parameters_tracking_is_enabled,
                        'ip_storage_is_enabled' => $ip_storage_is_enabled,
                        'public_statistics_is_enabled' => $public_statistics_is_enabled,
                        'public_statistics_password' => $public_statistics_password,
                        'is_enabled' => $is_enabled,
                        'email_reports_last_date' => get_date(),
                        'datetime' => get_date(),
                    ]);

                    $imported_count++;

                    /* Check against limit */
                    if($this->user->plan_settings->websites_limit != -1 && $total_rows + $imported_count >= $this->user->plan_settings->websites_limit) {
                        break;
                    }
                }

                Alerts::add_success(sprintf(l('global.success_message.csv_imported'), '<strong>' . $imported_count . '</strong>', l('websites.title')));

                redirect('websites');
            }
        }

        $values = [];

        /* Prepare the view */
        $data = [
            'values' => $values
        ];

        $view = new \Altum\View('websites-import/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
