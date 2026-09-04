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

class DomainNameUpdate extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->domain_names_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.domain_names')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('domain-names');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `domain_names` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->domain_names_limit != -1 && $total_rows > $this->user->plan_settings->domain_names_limit) {
            redirect('domain-names');
        }

        $domain_name_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$domain_name = db()->where('domain_name_id', $domain_name_id)->where('user_id', $this->user->user_id)->getOne('domain_names')) {
            redirect('domain-names');
        }
        $domain_name->whois_notifications = json_decode($domain_name->whois_notifications);
        $domain_name->ssl_notifications = json_decode($domain_name->ssl_notifications);

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
            $_POST['is_enabled'] = (int) isset($_POST['is_enabled']);

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

                /* Next check recalculation */
                $next_check_datetime = $domain_name->next_check_datetime;
                if(!$next_check_datetime || (new \DateTime($domain_name->next_check_datetime)) > (new \DateTime())) {
                    $next_check_datetime = get_date();
                }

                /* Database query */
                db()->where('domain_name_id', $domain_name_id)->update('domain_names', [
                    'project_id' => $_POST['project_id'],
                    'name' => $_POST['name'],
                    'target' => $_POST['target'],
                    'ssl_port' => $_POST['ssl_port'],
                    'whois_notifications' => $whois_notifications,
                    'ssl_notifications' => $ssl_notifications,
                    'is_enabled' => $_POST['is_enabled'],
                    'next_check_datetime' => $next_check_datetime,
                    'last_datetime' => get_date(),
                ]);

                /* Clear the cache */
                cache()->deleteItemsByTag('domain_name_id=' . $domain_name_id);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('domain-name-update/' . $domain_name_id);
            }

        }

        /* Set a custom title */
        Title::set(sprintf(l('domain_name_update.title'), $domain_name->name));

        /* Prepare the view */
        $data = [
            'projects' => $projects,
            'notification_handlers' => $notification_handlers,
            'domain_name' => $domain_name,
            'domain_name_timings' => $domain_name_timings,
        ];

        $view = new \Altum\View('domain-name-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
