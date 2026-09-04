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
namespace Altum\controllers;

use Altum\Alerts;

defined('ALTUMCODE') || die();

class WebsiteUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `websites` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->websites_limit != -1 && $total_rows > $this->user->plan_settings->websites_limit) {
            redirect('websites');
        }

        /* Team checks */
        if($this->team) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('websites');
        }

        /* Team checks */
        $website_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$website = db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->getOne('websites')) {
            redirect('websites');
        }

        /* Get available custom domains */
		$domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        if(!empty($_POST)) {
            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'host', 'pixel_key'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            $_POST['name'] = input_clean($_POST['name'], 256);
            $_POST['scheme'] = in_array($_POST['scheme'], ['https://', 'http://']) ? query_clean($_POST['scheme']) : 'https://';
            $_POST['host'] = str_replace(' ', '', mb_strtolower(input_clean($_POST['host'], 128)));
            $_POST['host'] = string_starts_with('http://', $_POST['host']) || string_starts_with('https://', $_POST['host']) ? parse_url($_POST['host'], PHP_URL_HOST) : $_POST['host'];
            $_POST['pixel_key'] = isset($_POST['pixel_key']) ? mb_substr(get_slug($_POST['pixel_key']), 0, 16) : '';

            /* Check the pixel key */
            if($_POST['pixel_key'] && !Alerts::has_field_errors('pixel_key') && $website->pixel_key != $_POST['pixel_key'] && db()->where('website_id', $website->website_id, '<>')->where('pixel_key', $_POST['pixel_key'])->has('websites')) {
                Alerts::add_field_error('pixel_key', l('websites.error_message.pixel_key_exists'));
            }

            $_POST['is_enabled'] = (int) isset($_POST['is_enabled']);
            $_POST['outbound_clicks_is_enabled'] = (int) isset($_POST['outbound_clicks_is_enabled']);
            $_POST['events_children_is_enabled'] = (int) isset($_POST['events_children_is_enabled']);
            $_POST['sessions_replays_is_enabled'] = (int) isset($_POST['sessions_replays_is_enabled']);
            $_POST['sessions_replays_hide_text_selector'] = input_clean($_POST['sessions_replays_hide_text_selector'], 1024);
            $_POST['email_reports_is_enabled'] = $this->user->plan_settings->email_reports_is_enabled ? (int) isset($_POST['email_reports_is_enabled']) : 0;
            $_POST['bot_exclusion_is_enabled'] = (int) isset($_POST['bot_exclusion_is_enabled']);
            $_POST['query_parameters_tracking_is_enabled'] = (int) isset($_POST['query_parameters_tracking_is_enabled']);
            $_POST['ip_storage_is_enabled'] = (int) isset($_POST['ip_storage_is_enabled']);

            $_POST['public_statistics_is_enabled'] = (int) isset($_POST['public_statistics_is_enabled']);
            $_POST['public_statistics_password'] = mb_substr($_POST['public_statistics_password'] ?? '', 0, 64);
            $_POST['public_statistics_password'] = !empty($_POST['public_statistics_password']) ?
                ($_POST['public_statistics_password'] != $website->public_statistics_password ? password_hash($_POST['public_statistics_password'], PASSWORD_DEFAULT) : $website->public_statistics_password)
                : null;

            $_POST['excluded_ips'] = implode(',', array_map(function($value) {
                return query_clean(trim($value));
            }, explode(',', $_POST['excluded_ips'])));

            /* Get available custom domains */
            $domain_id = null;
            if(isset($_POST['domain_id'])) {
                $domain = (new \Altum\Models\Domain())->get_domain_by_domain_id($_POST['domain_id']);

                if($domain && $domain->user_id == $this->user->user_id) {
                    $domain_id = $domain->domain_id;
                }
            }

            /* Domain checking */
            $path = null;
            if(function_exists('idn_to_utf8')) {
                $path = parse_url($_POST['scheme'] . idn_to_utf8($_POST['host']), PHP_URL_PATH);
                $_POST['host'] = parse_url($_POST['scheme'] . idn_to_utf8($_POST['host']), PHP_URL_HOST);
            }

            if(function_exists('idn_to_ascii')) {
                $_POST['host'] = idn_to_ascii($_POST['host']);
            }

            /* Check for blacklisted domain */
            if(in_array($_POST['host'], settings()->analytics->blacklisted_domains)) {
                Alerts::add_error(l('websites.error_message.blacklisted_domain'), 'host');
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Database query */
                db()->where('website_id', $website->website_id)->update('websites', [
                    'domain_id' => $domain_id,
                    'pixel_key' => $_POST['pixel_key'],
                    'name' => $_POST['name'],
                    'scheme' => $_POST['scheme'],
                    'host' => $_POST['host'],
                    'path' => $path,
                    'excluded_ips' => $_POST['excluded_ips'],
                    'outbound_clicks_is_enabled' => $_POST['outbound_clicks_is_enabled'],
                    'events_children_is_enabled' => $_POST['events_children_is_enabled'],
                    'sessions_replays_is_enabled' => $_POST['sessions_replays_is_enabled'],
                    'sessions_replays_hide_text_selector' => $_POST['sessions_replays_hide_text_selector'],
                    'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
                    'bot_exclusion_is_enabled' => $_POST['bot_exclusion_is_enabled'],
                    'query_parameters_tracking_is_enabled' => $_POST['query_parameters_tracking_is_enabled'],
                    'ip_storage_is_enabled' => $_POST['ip_storage_is_enabled'],
                    'public_statistics_is_enabled' => $_POST['public_statistics_is_enabled'],
                    'public_statistics_password' => $_POST['public_statistics_password'],
                    'is_enabled' => $_POST['is_enabled'],
                    'last_datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('websites_' . $this->user->user_id);
                cache()->deleteItemsByTag('website_id=' . $website->website_id);

                redirect('website-update/' . $website_id);
            }
        }

        /* Prepare the view */
        $data = [
            'website' => $website,
            'domains' => $domains,
        ];

        $view = new \Altum\View('website-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
