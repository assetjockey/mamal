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

class NotificationDataAjax extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!empty($_POST) && (\Altum\Csrf::check('token') || \Altum\Csrf::check('global_token')) && isset($_POST['request_type'])) {

            switch($_POST['request_type']) {

                /* Create */
                case 'create': $this->create(); break;

                /* Delete */
                case 'delete': $this->delete(); break;

            }

        }

        die();
    }

    private function create() {
        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Response::json('Please create an account on the demo to test out this function.', 'error');

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.notifications')) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        $_POST['notification_id'] = (int) $_POST['notification_id'];
        $type = 'imported';

        /* Check for possible errors */
        if(!db()->where('notification_id', $_POST['notification_id'])->where('user_id', $this->user->user_id)->getValue('notifications', 'notification_id')) {
            die();
        }

        /* CSV or Data */
        $csv = !empty($_FILES['csv']['name']);

        if(!$csv && empty($_POST['key']) && empty($_POST['value'])) {
            die();
        }

        if($csv) {
            $file_name = $_FILES['csv']['name'];
            $file_extension = mb_strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if($_FILES['csv']['error'] == UPLOAD_ERR_INI_SIZE) {
                Response::json(sprintf(l('global.error_message.file_size_limit'), get_max_upload()), 'error');
            }

            if($_FILES['csv']['error'] && $_FILES['csv']['error'] != UPLOAD_ERR_INI_SIZE) {
                Response::json(l('global.error_message.file_upload'), 'error');
            }

            if(!in_array($file_extension, ['csv'])) {
                Response::json(l('global.error_message.invalid_file_type'), 'error');
            }

			/* Parse csv */
			$csv_contents = file_get_contents($_FILES['csv']['tmp_name']);
			$csv_contents = preg_replace('/^\xEF\xBB\xBF/', '', $csv_contents);

			$csv_array = array_map(function($csv_line) {
				return str_getcsv($csv_line, ',', '"', '\\');
			}, preg_split('/\r\n|\r|\n/', $csv_contents));

            if(!$csv_array || !is_array($csv_array)) {
                Response::json(l('global.error_message.invalid_file_type'), 'error');
            }

            $headers_array = $csv_array[0];
            unset($csv_array[0]);
            reset($csv_array);

            /* Go over each row */
            foreach($csv_array as $key => $value) {
                if(count($headers_array) != count($value)) {
                    continue;
                }

                /* Date for insertion */
                $datetime = get_date();

                $data = [];
                foreach($headers_array as $header_key => $header_value) {
                    $data[$header_value] = $value[$header_key];

                    /* Check for date type of column */
                    if(in_array($header_value, ['date', 'datetime'])) {
                        try {
                            $datetime = (new \DateTime($value[$header_key]))->format('Y-m-d H:i:s');
                        } catch (\Exception $exception) {
                            // :)
                        }
                    }
                }
                $data = json_encode($data);

                /* Insert in the database */
                db()->insert('track_conversions', [
                    'user_id' => $this->user->user_id,
                    'notification_id' => $_POST['notification_id'],
                    'type' => $type,
                    'data' => $data,
                    'datetime' => $datetime,
                ]);
            }
        }

        else {
            /* Parse the keys and values */
            $data = [];
            foreach($_POST['key'] as $key => $value) {
                if(!empty($_POST['key'][$key]) && isset($_POST['value'][$key])) {
                    $cleaned_value = query_clean($value);

                    $data[$cleaned_value] = query_clean($_POST['value'][$key]);
                }
            }

            $data = json_encode($data);

            /* Insert in the database */
            db()->insert('track_conversions', [
                'user_id' => $this->user->user_id,
                'notification_id' => $_POST['notification_id'],
                'type' => $type,
                'data' => $data,
                'datetime' => get_date()
            ]);
        }

        Response::json(l('global.success_message.create2'), 'success');
    }

    private function delete() {
        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Response::json('Please create an account on the demo to test out this function.', 'error');

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.notifications')) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        $_POST['id'] = (int) $_POST['id'];

        /* Delete from database */
        db()->where('id', $_POST['id'])->where('user_id', $this->user->user_id)->delete('track_conversions');

        Response::json(l('global.success_message.delete2'), 'success');
    }

    public function bulk() {

        \Altum\Authentication::guard();

        $notification_id = (int) $_POST['notification_id'];

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('notification/' . $notification_id . '/data');
        }

        if(empty($_POST['selected'])) {
            redirect('notification/' . $notification_id . '/data');
        }

        if(!isset($_POST['type'])) {
            redirect('notification/' . $notification_id . '/data');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            set_time_limit(0);

            session_write_close();

            $_POST['selected'] = is_array($_POST['selected']) ? array_filter(array_unique(array_map('intval', $_POST['selected']))) : [];

            switch($_POST['type']) {
                case 'delete':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.notifications')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('notifications');
                    }

                    foreach($_POST['selected'] as $id) {
                        db()->where('id', $id)->where('user_id', $this->user->user_id)->delete('track_conversions');
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('notification/' . $notification_id . '/data');
    }

}
