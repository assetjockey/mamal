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

use Altum\Title;

defined('ALTUMCODE') || die();

class DnsMonitorLogs extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->dns_monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        $dns_monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$dns_monitor = db()->where('dns_monitor_id', $dns_monitor_id)->where('user_id', $this->user->user_id)->getOne('dns_monitors')) {
            redirect('dns_monitors');
        }
        $dns_monitor->settings = json_decode($dns_monitor->settings ?? '');

        $datetime = \Altum\Date::get_start_end_dates_new();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters([], [], ['dns_monitor_log_id', 'datetime'], allowed_datetime_fields: ['datetime']));
        $filters->set_default_order_by('dns_monitor_log_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `dns_monitors_logs` WHERE `dns_monitor_id` = {$dns_monitor->dns_monitor_id} AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}') {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('dns-monitor-logs/' . $dns_monitor->dns_monitor_id . '?' . $filters->get_get() . '&start_date=' . $datetime['start_date'] . '&end_date=' . $datetime['end_date'] . '&page={{PAGE}}')));

        /* Get the required logs */
        $dns_monitor_logs = [];
        $dns_monitor_logs_result = database()->query("
            SELECT
                *
            FROM
                 `dns_monitors_logs`
            WHERE
                `dns_monitor_id` = {$dns_monitor->dns_monitor_id}
                AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");

        /* Get dns_monitor logs to calculate data and display charts */
        while($dns_monitor_log = $dns_monitor_logs_result->fetch_object()) {
            $dns_monitor_log->dns_changes = json_decode($dns_monitor_log->dns_changes);
            $dns_monitor_logs[] = $dns_monitor_log;
        }

        /* Set a custom title */
        Title::set(sprintf(l('dns_monitor_logs.title'), $dns_monitor->name));

        /* Export handler */
        process_export_csv($dns_monitor_logs, ['dns_monitor_id', 'total_dns_records_found', 'total_dns_types_found', 'datetime'], sprintf(l('dns_monitor_logs.title'), $dns_monitor->name));
        process_export_json($dns_monitor_logs, ['dns_monitor_id', 'dns', 'dns_changes', 'total_dns_records_found', 'total_dns_types_found', 'datetime'], sprintf(l('dns_monitor_logs.title'), $dns_monitor->name));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'dns_monitor' => $dns_monitor,
            'dns_monitor_logs' => $dns_monitor_logs,
            'datetime' => $datetime,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('dns-monitor-logs/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }
}
