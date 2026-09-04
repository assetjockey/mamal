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

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Models\QrCode;

defined('ALTUMCODE') || die();

class QrCodes extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!settings()->codes->qr_codes_is_enabled) {
            throw_404();
        }

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['project_id', 'type'], ['name'], ['qr_code_id', 'type', 'last_datetime', 'name', 'datetime'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->qr_codes_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `qr_codes` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('qr-codes?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the qr_codes list for the user */
        $qr_codes = [];
        $qr_codes_result = database()->query("
            SELECT `qr_codes`.*, `links`.`file_name`, `links`.`file_size`
            FROM `qr_codes`
            LEFT JOIN `links` ON `qr_codes`.`link_id` = `links`.`link_id`
            WHERE `qr_codes`.`user_id` = {$this->user->user_id} {$filters->get_sql_where('qr_codes')} 
            {$filters->get_sql_order_by('qr_codes')} 
            {$paginator->get_sql_limit()}
        ");
        while($row = $qr_codes_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $row->qr_code_url = $row->qr_code ?\Altum\Uploads::get_full_url('qr_code') . $row->qr_code : null;
            $row->qr_code_logo_url = $row->qr_code_logo ?\Altum\Uploads::get_full_url('qr_code_logo') . $row->qr_code_logo : null;
            $row->qr_code_background_url = $row->qr_code_background ?\Altum\Uploads::get_full_url('qr_code_background') . $row->qr_code_background : null;
            $row->qr_code_foreground_url = $row->qr_code_foreground ?\Altum\Uploads::get_full_url('qr_code_foreground') . $row->qr_code_foreground : null;
            $qr_codes[] = $row;
        }

        /* Export handler */
        process_export_csv_new($qr_codes, ['qr_code_id', 'user_id', 'project_id', 'type', 'name', 'qr_code', 'qr_code_url', 'qr_code_logo', 'qr_code_logo_url', 'qr_code_background', 'qr_code_background_url', 'qr_code_foreground', 'qr_code_foreground_url', 'embedded_data', 'settings', 'last_datetime', 'datetime'], ['settings'], sprintf(l('qr_codes.title')));
        process_export_json($qr_codes, ['qr_code_id', 'user_id', 'project_id', 'type', 'name', 'qr_code', 'qr_code_url', 'qr_code_logo', 'qr_code_logo_url', 'qr_code_background', 'qr_code_background_url', 'qr_code_foreground', 'qr_code_foreground_url', 'embedded_data', 'settings','last_datetime', 'datetime'], sprintf(l('qr_codes.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Existing projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        $available_qr_codes = require APP_PATH . 'includes/enabled_qr_codes.php';

        /* Prepare the view */
        $data = [
            'qr_codes'            => $qr_codes,
            'total_qr_codes'      => $total_rows,
            'pagination'          => $pagination,
            'filters'             => $filters,
            'projects'            => $projects,
            'available_qr_codes'    => $available_qr_codes,
        ];

        $view = new \Altum\View('qr-codes/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function duplicate() {
        \Altum\Authentication::guard();

        if(!settings()->codes->qr_codes_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.qr_codes')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('qr-codes');
        }

        if (empty($_POST)) {
            throw_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('qr_codes', 'COUNT(*)') ?? 0;
        if($this->user->plan_settings->qr_codes_limit != -1 && $total_rows >= $this->user->plan_settings->qr_codes_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('qr-codes');
        }

        $qr_code_id = (int) $_POST['qr_code_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');
        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('qr-codes');
        }

        /* Verify the main resource */
        if(!$qr_code = db()->where('qr_code_id', $qr_code_id)->where('user_id', $this->user->user_id)->getOne('qr_codes')) {
            redirect('qr-codes');
        }

        if($qr_code->type == 'file') {
            Alerts::add_error(l('qr_codes.error_message.file_duplicate_not_available'));
            redirect('qr-codes');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Duplicate the files */
            $qr_code_image = \Altum\Uploads::copy_uploaded_file($qr_code->qr_code, \Altum\Uploads::get_path('qr_codes'), \Altum\Uploads::get_path('qr_codes'));
            $qr_code_logo = \Altum\Uploads::copy_uploaded_file($qr_code->qr_code_logo, \Altum\Uploads::get_path('qr_codes/logo'), \Altum\Uploads::get_path('qr_codes/logo'));
            $qr_code_background = \Altum\Uploads::copy_uploaded_file($qr_code->qr_code_background, \Altum\Uploads::get_path('qr_code_background'), \Altum\Uploads::get_path('qr_code_background'));
            $qr_code_foreground = \Altum\Uploads::copy_uploaded_file($qr_code->qr_code_foreground, \Altum\Uploads::get_path('qr_code_foreground'), \Altum\Uploads::get_path('qr_code_foreground'));

            /* Insert to database */
            $qr_code_id = db()->insert('qr_codes', [
                'user_id' => $this->user->user_id,
                'project_id' => $qr_code->project_id,
                'name' => string_truncate($qr_code->name . ' - ' . l('global.duplicated'), 64, null),
                'type' => $qr_code->type,
                'qr_code_logo' => $qr_code_logo,
                'qr_code_background' => $qr_code_background,
                'qr_code_foreground' => $qr_code_foreground,
                'qr_code' => $qr_code_image,
                'settings' => $qr_code->settings,
                'embedded_data' => $qr_code->embedded_data,
                'datetime' => get_date(),
            ]);

            /* Clear the cache */
            cache()->deleteItem('qr_codes_total?user_id=' . $this->user->user_id);
            cache()->deleteItem('qr_codes_dashboard?user_id=' . $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($qr_code->name) . '</strong>'));

            /* Redirect */
            redirect('qr-code-update/' . $qr_code_id);

        }

        redirect('qr-codes');
    }

    public function bulk() {

        \Altum\Authentication::guard();

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('qr-codes');
        }

        if(!isset($_POST['type'])) {
            redirect('qr-codes');
        }

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
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.qr_codes')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('qr-codes');
                    }

                    (new QrCode())->bulk_delete($_POST['selected'], $this->user->user_id);

                    break;

                case 'download':

                    $files = [];

                    foreach($_POST['selected'] as $qr_code_id) {
                        if($qr_code = db()->where('qr_code_id', $qr_code_id)->where('user_id', $this->user->user_id)->getOne('qr_codes', ['qr_code'])) {
                            $files[$qr_code->qr_code] = \Altum\Uploads::get_path('qr_code');
                        }
                    }

                    \Altum\Uploads::download_files_as_zip($files, l('global.download'));

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('qr-codes');
    }

    public function delete() {
        \Altum\Authentication::guard();

        if(!settings()->codes->qr_codes_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.qr_codes')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('qr-codes');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $qr_code_id = (int) $_POST['qr_code_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('qr-codes');
        }

        /* Make sure the resource is created by the logged in user */
        if(!$qr_code = db()->where('qr_code_id', $qr_code_id)->where('user_id', $this->user->user_id)->getOne('qr_codes', ['qr_code_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new QrCode())->delete($qr_code->qr_code_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $qr_code->name . '</strong>'));

            redirect('qr-codes');

        }

        redirect('qr-codes');
    }

}
