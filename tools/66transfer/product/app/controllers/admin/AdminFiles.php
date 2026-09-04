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
use Altum\Uploads;

defined('ALTUMCODE') || die();

class AdminFiles extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', 'transfer_id', 'transfer_request_id'], ['original_name'], ['file_id', 'datetime', 'original_name'], allowed_datetime_fields: ['datetime']));
        $filters->set_default_order_by('file_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `files` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/files?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $files = [];
        $files_result = database()->query("
            SELECT
                `files`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `files`
            LEFT JOIN
                `users` ON `files`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('files')}
                {$filters->get_sql_order_by('files')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $files_result->fetch_object()) {
            $files[] = $row;
        }

        /* Export handler */
        process_export_csv($files, ['file_id', 'transfer_id', 'user_id', 'name', 'original_name', 'size', 'is_encrypted', 'datetime'], sprintf(l('files.title')));
        process_export_json($files, ['file_id', 'transfer_id', 'user_id', 'name', 'original_name', 'size', 'is_encrypted', 'datetime'], sprintf(l('files.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'files' => $files,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/files/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/files');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/files');
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

                    foreach($_POST['selected'] as $file_id) {

                        $file = db()->where('file_id', $file_id)->getOne('files', ['file_id', 'name', 'offload_id', 'user_id']);

                        /* Delete uploaded file */
                        Uploads::delete_uploaded_file_and_potential_residue($file->name, 'files', $file->offload_id);

                        /* Delete the resource */
                        db()->where('file_id', $file->file_id)->delete('files');

                        /* Update the user */
                        (new \Altum\Models\Files())->calculate_and_update_file_usage($file->user_id);
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/files');
    }

    public function delete() {

        $file_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$file = db()->where('file_id', $file_id)->getOne('files', ['file_id', 'user_id', 'name', 'original_name', 'offload_id'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete uploaded file */
            Uploads::delete_uploaded_file_and_potential_residue($file->name, 'files', $file->offload_id);

            /* Delete the resource */
            db()->where('file_id', $file->file_id)->delete('files');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $file->original_name . '</strong>'));

        }

        redirect('admin/files');
    }

}
