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
use Altum\Response;

defined('ALTUMCODE') || die();

class Incidents extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->monitors_is_enabled && !settings()->monitors_heartbeats->heartbeats_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['monitor_id', 'heartbeat_id', 'notification_handlers_ids'], ['comment'], ['incident_id', 'start_datetime', 'end_datetime', 'last_failed_check_datetime', 'failed_checks'], [], ['notification_handlers_ids' => 'json_contains'], allowed_datetime_fields: ['start_datetime', 'end_datetime', 'last_failed_check_datetime']));
        $filters->set_default_order_by($this->user->preferences->incidents_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `incidents` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('incidents?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the monitors */
        $incidents = [];
        $incidents_result = database()->query("
            SELECT
                `incidents`.*,
                `monitors`.`name` as `monitor_name`,
                `heartbeats`.`name` as `heartbeat_name`
            FROM
                `incidents`
            LEFT JOIN `monitors` ON `incidents`.`monitor_id` = `monitors`.`monitor_id`
            LEFT JOIN `heartbeats` ON `incidents`.`heartbeat_id` = `heartbeats`.`heartbeat_id`
            WHERE
                `incidents`.`user_id` = {$this->user->user_id}
                {$filters->get_sql_where('incidents')}
                {$filters->get_sql_order_by('incidents')}
            
            {$paginator->get_sql_limit()}
        ");
        while($row = $incidents_result->fetch_object()) {
            $row->error = json_decode($row->error ?? '');
            $incidents[] = $row;
        }

        /* Export handler */
        process_export_csv_new($incidents, ['incident_id', 'user_id', 'monitor_id', 'start_monitor_log_id', 'end_monitor_log_id', 'heartbeat_id', 'start_heartbeat_log_id', 'end_heartbeat_log_id', 'start_datetime', 'end_datetime', 'last_failed_check_datetime', 'failed_checks', 'notification_handlers_ids', 'comment', 'error'], ['error'], sprintf(l('incidents.title')));
        process_export_json($incidents, ['incident_id', 'user_id', 'monitor_id', 'start_monitor_log_id', 'end_monitor_log_id', 'heartbeat_id', 'start_heartbeat_log_id', 'end_heartbeat_log_id', 'start_datetime', 'end_datetime', 'last_failed_check_datetime', 'failed_checks', 'notification_handlers_ids', 'comment', 'error'], sprintf(l('incidents.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'total_incidents' => $total_rows,
            'incidents' => $incidents,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('incidents/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function update_ajax () {

        if(empty($_POST)) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.monitors')) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        $_POST['comment'] = input_clean($_POST['comment'], 300);
        $_POST['is_solved'] = isset($_POST['is_solved']);
        $_POST['incident_id'] = (int) $_POST['incident_id'];

        /* Check for any errors */
        $required_fields = ['incident_id'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                Response::json(l('global.error_message.empty_fields'), 'error');
            }
        }

        if(!\Altum\Csrf::check('token')) {
            Response::json(l('global.error_message.invalid_csrf_token'), 'error');
        }

        /* Get incident */
        $incident = db()->where('user_id', $this->user->user_id)->where('incident_id', $_POST['incident_id'])->getOne('incidents');

        if(!$incident) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        /* Database query */
        db()->where('incident_id', $_POST['incident_id'])->update('incidents', [
            'comment' => $_POST['comment'],
            'end_datetime' => $_POST['is_solved'] && !$incident->end_datetime ? get_date() : $incident->end_datetime,
        ]);

        /* Clear the cache */
        if($incident->monitor_id) {
            cache()->deleteItemsByTag('monitor_id=' . $incident->monitor_id);
        } else if($incident->heartbeat_id) {
            cache()->deleteItemsByTag('heartbeat_id=' . $incident->heartbeat_id);
        }

        /* Set a nice success message */
        Response::json(l('global.success_message.update2'), 'success', ['comment' => $_POST['comment'], 'incident_id' => $_POST['incident_id']]);

    }

    public function bulk() {

        if(!settings()->monitors_heartbeats->monitors_is_enabled && !settings()->monitors_heartbeats->heartbeats_is_enabled) {
            throw_404();
        }


        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('incidents');
        }

        if(!isset($_POST['type'])) {
            redirect('incidents');
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

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.incidents')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('incidents');
                    }

                    foreach($_POST['selected'] as $incident_id) {
                        if($incident = db()->where('incident_id', $incident_id)->where('user_id', $this->user->user_id)->getOne('incidents', ['incident_id', 'monitor_id', 'heartbeat_id'])) {
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

        redirect('incidents');
    }

    public function delete() {

        if(!settings()->monitors_heartbeats->monitors_is_enabled && !settings()->monitors_heartbeats->heartbeats_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.incidents')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('incidents');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $incident_id = (int) $_POST['incident_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('incidents');
        }

        /* Make sure the resource is created by the logged in user */
        if(!$incident = db()->where('incident_id', $incident_id)->where('user_id', $this->user->user_id)->getOne('incidents')) {
            redirect('incidents');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the resource */
            db()->where('incident_id', $incident->incident_id)->delete('incidents');

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.delete2'));

            /* Clear the cache */
            if($incident->heartbeat_id) {
                cache()->deleteItemsByTag('heartbeat_id=' . $incident->heartbeat_id);
                redirect('heartbeat/' . $incident->heartbeat_id);;
            }
            if($incident->monitor_id) {
                cache()->deleteItemsByTag('monitor_id=' . $incident->monitor_id);
                redirect('monitor/' . $incident->monitor_id);;
            }

            redirect('incidents');

        }

        redirect('incidents');
    }

}
