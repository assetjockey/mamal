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
use Altum\Title;

defined('ALTUMCODE') || die();

class DnsMonitor extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->dns_monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        $dns_monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$dns_monitor = db()->where('dns_monitor_id', $dns_monitor_id)->where('user_id', $this->user->user_id)->getOne('dns_monitors')) {
            redirect('dns-monitors');
        }
        $dns_monitor->settings = json_decode($dns_monitor->settings ?? '');
        $dns_monitor->dns = json_decode($dns_monitor->dns ?? '');

        $start_date = isset($_GET['start_date']) ? query_clean($_GET['start_date']) : Date::get('', 4);
        $end_date = isset($_GET['end_date']) ? query_clean($_GET['end_date']) : Date::get('', 4);
        $date = \Altum\Date::get_start_end_dates($start_date, $end_date);

        /* Get the required statistics */
        $dns_monitor_logs = [];
        $dns_monitor_logs_result = database()->query("
            SELECT  *
            FROM `dns_monitors_logs`
            WHERE
                `dns_monitor_id` = {$dns_monitor->dns_monitor_id}
                AND (`datetime` BETWEEN '{$date->start_date_query}' AND '{$date->end_date_query}')
            LIMIT 10
        ");

        /* Get dns_monitor logs to calculate data and display charts */
        while($dns_monitor_log = $dns_monitor_logs_result->fetch_object()) {
            $dns_monitor_log->dns_changes = json_decode($dns_monitor_log->dns_changes);
            $dns_monitor_logs[] = $dns_monitor_log;
        }

        /* Set a custom title */
        Title::set(sprintf(l('dns_monitor.title'), $dns_monitor->name));

        /* Prepare the view */
        $data = [
            'dns_monitor' => $dns_monitor,
            'dns_monitor_logs' => $dns_monitor_logs,
            'total_dns_monitor_logs' => count($dns_monitor_logs),
            'date' => $date,
        ];

        $view = new \Altum\View('dns-monitor/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
