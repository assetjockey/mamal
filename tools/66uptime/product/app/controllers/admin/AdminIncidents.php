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

class AdminIncidents extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', 'monitor_id', 'heartbeat_id', 'notification_handlers_ids'], ['comment'], ['incident_id', 'start_datetime', 'end_datetime', 'last_failed_check_datetime', 'failed_checks'], [], ['notification_handlers_ids' => 'json_contains'], allowed_datetime_fields: ['start_datetime', 'end_datetime', 'last_failed_check_datetime']));
        $filters->set_default_order_by($this->user->preferences->incidents_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `incidents` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/incidents?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the incidents */
        $incidents = [];
        $incidents_result = database()->query("
            SELECT
                `incidents`.*,
                `users`.`name` AS `user_name`, 
                `users`.`email` AS `user_email`,
                `monitors`.`name` as `monitor_name`,
                `heartbeats`.`name` as `heartbeat_name`
            FROM
                `incidents`
            LEFT JOIN `users` ON `incidents`.`user_id` = `users`.`user_id`
            LEFT JOIN `monitors` ON `incidents`.`monitor_id` = `monitors`.`monitor_id`
            LEFT JOIN `heartbeats` ON `incidents`.`heartbeat_id` = `heartbeats`.`heartbeat_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('incidents')}
                {$filters->get_sql_order_by('incidents')}
            
            {$paginator->get_sql_limit()}
        ");
        while($row = $incidents_result->fetch_object()) {
            $row->error = json_decode($row->error ?? '');
            $incidents[] = $row;
        }

        /* Export handler */
        process_export_csv($incidents, ['incident_id', 'user_id', 'monitor_id', 'start_monitor_log_id', 'end_monitor_log_id', 'heartbeat_id', 'start_heartbeat_log_id', 'end_heartbeat_log_id', 'start_datetime', 'end_datetime', 'last_failed_check_datetime', 'failed_checks', 'notification_handlers_ids', 'comment'], sprintf(l('incidents.title')));
        process_export_json($incidents, ['incident_id', 'user_id', 'monitor_id', 'start_monitor_log_id', 'end_monitor_log_id', 'heartbeat_id', 'start_heartbeat_log_id', 'end_heartbeat_log_id', 'start_datetime', 'end_datetime', 'last_failed_check_datetime', 'failed_checks', 'notification_handlers_ids', 'comment', 'error'], sprintf(l('incidents.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'incidents' => $incidents,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/incidents/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/incidents');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/incidents');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            set_time_limit(0);

            session_write_close();

            $_POST['selected'] = is_array($_POST['selected']) ? array_unique(array_map('intval', $_POST['selected'])) : [];

            switch($_POST['type']) {
                case 'delete':

                    foreach($_POST['selected'] as $incident_id) {
                        if($incident = db()->where('incident_id', $incident_id)->getOne('incidents', ['incident_id', 'monitor_id', 'heartbeat_id'])) {
                            /* Delete the resource */
                            db()->where('incident_id', $incident->incident_id)->delete('incidents');

                            /* Clear the cache */
                            cache()->deleteItemsByTag('heartbeat_id=' . $incident->heartbeat_id);
                            cache()->deleteItemsByTag('monitor_id=' . $incident->monitor_id);
                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/incidents');
    }

    public function delete() {

        $incident_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$incident = db()->where('incident_id', $incident_id)->getOne('incidents', ['incident_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the resource */
            db()->where('incident_id', $incident_id)->delete('incidents');

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.delete2'));

            /* Clear the cache */
            if($incident->heartbeat_id) {
                cache()->deleteItemsByTag('heartbeat_id=' . $incident->heartbeat_id);
            }
            if($incident->monitor_id) {
                cache()->deleteItemsByTag('monitor_id=' . $incident->monitor_id);
            }

        }

        redirect('admin/incidents');
    }

}
