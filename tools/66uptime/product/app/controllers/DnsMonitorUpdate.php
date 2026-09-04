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

class DnsMonitorUpdate extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->dns_monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.dns_monitors')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('dns-monitors');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `dns_monitors` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->dns_monitors_limit != -1 && $total_rows > $this->user->plan_settings->dns_monitors_limit) {
            redirect('dns-monitors');
        }

        $dns_monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$dns_monitor = db()->where('dns_monitor_id', $dns_monitor_id)->where('user_id', $this->user->user_id)->getOne('dns_monitors')) {
            redirect('dns-monitors');
        }

        $dns_monitor->settings = json_decode($dns_monitor->settings ?? '');
        $dns_monitor->notifications = json_decode($dns_monitor->notifications ?? '');

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Monitors vars */
        $dns_monitor_check_intervals = require APP_PATH . 'includes/dns_monitor_check_intervals.php';
        $dns_types = require APP_PATH . 'includes/dns_monitor_types.php';

        if(!empty($_POST)) {
            $_POST['name'] = input_clean($_POST['name'], 64);
            $_POST['target'] = query_clean($_POST['target']);
            $_POST['dns_check_interval_seconds'] = in_array($_POST['dns_check_interval_seconds'], $this->user->plan_settings->dns_monitors_check_intervals ?? []) ? (int) $_POST['dns_check_interval_seconds'] : reset($this->user->plan_settings->dns_monitors_check_intervals);
            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
            $_POST['notifications'] = array_map(
                'intval',
                array_filter($_POST['notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );
            $_POST['dns_types'] = array_filter($_POST['dns_types'] ?? [], function($dns_type) use($dns_types) {
                return array_key_exists($dns_type, $dns_types);
            });
            $_POST['is_enabled'] = (int) isset($_POST['is_enabled']);

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'target'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(filter_var($_POST['target'], FILTER_VALIDATE_URL)) {
                $_POST['target'] = get_domain_from_url($_POST['target']);
            }

            if(in_array(get_domain_from_url($_POST['target']), settings()->status_pages->blacklisted_domains)) {
                db()->where('dns_monitor_id', $dns_monitor->dns_monitor_id)->update('dns_monitors', [
                    'is_enabled' => 0,
                ]);

                Alerts::add_field_error('target', l('status_page.error_message.blacklisted_domain'));
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $settings = json_encode([
                    'dns_check_interval_seconds' => $_POST['dns_check_interval_seconds'],
                    'dns_types' => $_POST['dns_types'],
                ]);

                $notifications = json_encode($_POST['notifications'] ?? '');

                /* Next check recalculation */
                $next_check_datetime = $dns_monitor->next_check_datetime;
                if((new \DateTime($dns_monitor->next_check_datetime)) > (new \DateTime())) {
                    $next_check_datetime = (new \DateTime())->modify('+' . $_POST['dns_check_interval_seconds'] . ' seconds')->format('Y-m-d H:i:s');
                }

                /* Database query */
                $dns_monitor_id = db()->where('dns_monitor_id', $dns_monitor->dns_monitor_id)->update('dns_monitors', [
                    'project_id' => $_POST['project_id'],
                    'name' => $_POST['name'],
                    'target' => $_POST['target'],
                    'settings' => $settings,
                    'notifications' => $notifications,
                    'is_enabled' => $_POST['is_enabled'],
                    'next_check_datetime' => $next_check_datetime,
                    'last_datetime' => get_date(),
                ]);

                /* Clear the cache */
                cache()->deleteItemsByTag('dns_monitor_id=' . $dns_monitor->dns_monitor_id);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('dns-monitor-update/' . $dns_monitor->dns_monitor_id);
            }

        }

        /* Set a custom title */
        Title::set(sprintf(l('dns_monitor_update.title'), $dns_monitor->name));

        /* Prepare the view */
        $data = [
            'dns_monitor' => $dns_monitor,
            'projects' => $projects,
            'notification_handlers' => $notification_handlers,
            'dns_monitor_check_intervals' => $dns_monitor_check_intervals,
            'dns_types' => $dns_types,
        ];

        $view = new \Altum\View('dns-monitor-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
