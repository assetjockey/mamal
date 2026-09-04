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

class DomainNameCreate extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->domain_names_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.domain_names')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('domain-names');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `domain_names` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->domain_names_limit != -1 && $total_rows >= $this->user->plan_settings->domain_names_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('domain-names');
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        $domain_name_timings = require APP_PATH . 'includes/domain_name_timings.php';

        if(!empty($_POST)) {
            $_POST['name'] = input_clean($_POST['name'], 64);
            $_POST['target'] = query_clean($_POST['target']);
            $_POST['ssl_port'] = (int) $_POST['ssl_port'];
            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
            $_POST['whois_notifications'] = array_map(
                'intval',
                array_filter($_POST['whois_notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );
            $_POST['whois_notifications_timing'] = array_key_exists($_POST['whois_notifications_timing'], $domain_name_timings) ? $_POST['whois_notifications_timing'] : array_key_first($domain_name_timings);
            $whois_notifications = json_encode([
                'whois_notifications' => $_POST['whois_notifications'],
                'whois_notifications_timing' => $_POST['whois_notifications_timing'],
            ]);
            $_POST['ssl_notifications'] = array_map(
                'intval',
                array_filter($_POST['ssl_notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );
            $_POST['ssl_notifications_timing'] = array_key_exists($_POST['ssl_notifications_timing'], $domain_name_timings) ? $_POST['ssl_notifications_timing'] : array_key_first($domain_name_timings);
            $ssl_notifications = json_encode([
                'ssl_notifications' => $_POST['ssl_notifications'],
                'ssl_notifications_timing' => $_POST['ssl_notifications_timing'],
            ]);

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'target'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(filter_var($_POST['target'], FILTER_VALIDATE_URL)) {
                $_POST['target'] = get_domain_from_url($_POST['target']);
            }

            if(in_array(get_domain_from_url($_POST['target']), settings()->status_pages->blacklisted_domains)) {
                Alerts::add_field_error('target', l('status_page.error_message.blacklisted_domain'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Database query */
                $domain_name_id = db()->insert('domain_names', [
                    'project_id' => $_POST['project_id'],
                    'user_id' => $this->user->user_id,
                    'name' => $_POST['name'],
                    'target' => $_POST['target'],
                    'ssl_port' => $_POST['ssl_port'],
                    'whois_notifications' => $whois_notifications,
                    'ssl_notifications' => $ssl_notifications,
                    'next_check_datetime' => get_date(),
                    'datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(l('domain_name_create.success_message'));

                redirect('domain-name/' . $domain_name_id);
            }

        }

        /* Set default values */
        $values = [
            'name' => $_POST['name'] ?? '',
            'target' => $_POST['target'] ?? '',
            'project_id' => $_POST['project_id'] ?? '',
            'ssl_port' => $_POST['ssl_port'] ?? 443,
            'ssl_notifications' => $_POST['ssl_notifications'] ?? [],
            'ssl_notifications_timing' => 3,
            'whois_notifications' => $_POST['whois_notifications'] ?? [],
            'whois_notifications_timing' => 3,
            'email_reports_is_enabled' => $_POST['email_reports_is_enabled'] ?? 0,
        ];

        /* Prepare the view */
        $data = [
            'projects' => $projects,
            'notification_handlers' => $notification_handlers,
            'values' => $values,
            'domain_name_timings' => $domain_name_timings,
        ];

        $view = new \Altum\View('domain-name-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
