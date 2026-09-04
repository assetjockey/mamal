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
use Altum\Models\Barcode;

defined('ALTUMCODE') || die();

class Barcodes extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!settings()->codes->barcodes_is_enabled) {
            throw_404();
        }

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['project_id', 'type'], ['name', 'value'], ['barcode_id', 'last_datetime', 'name', 'datetime', 'type'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->barcodes_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `barcodes` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('barcodes?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the barcodes list for the user */
        $barcodes = [];
        $barcodes_result = database()->query("SELECT * FROM `barcodes` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $barcodes_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $barcodes[] = $row;
        }

        /* Export handler */
        process_export_csv_new($barcodes, ['barcode_id', 'user_id', 'project_id', 'type', 'name', 'value', 'embedded_data', 'settings', 'last_datetime', 'datetime'], ['settings'], sprintf(l('barcodes.title')));
        process_export_json($barcodes, ['barcode_id', 'user_id', 'project_id', 'type', 'name', 'value', 'embedded_data', 'settings','last_datetime', 'datetime'], sprintf(l('barcodes.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Existing projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        $available_barcodes = require APP_PATH . 'includes/enabled_barcodes.php';

        /* Prepare the view */
        $data = [
            'barcodes'            => $barcodes,
            'total_barcodes'      => $total_rows,
            'pagination'          => $pagination,
            'filters'             => $filters,
            'projects'            => $projects,
            'available_barcodes'  => $available_barcodes,
        ];

        $view = new \Altum\View('barcodes/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function duplicate() {
        \Altum\Authentication::guard();

        if(!settings()->codes->barcodes_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.barcodes')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('barcodes');
        }

        if (empty($_POST)) {
            throw_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('barcodes', 'COUNT(*)') ?? 0;
        if($this->user->plan_settings->barcodes_limit != -1 && $total_rows >= $this->user->plan_settings->barcodes_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('barcodes');
        }

        $barcode_id = (int) $_POST['barcode_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');
        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('barcodes');
        }

        /* Verify the main resource */
        if(!$barcode = db()->where('barcode_id', $barcode_id)->where('user_id', $this->user->user_id)->getOne('barcodes')) {
            redirect('barcodes');
        }

        /* Check the barcode type access */
        if(!isset($this->user->plan_settings->enabled_barcodes->{$barcode->type}) || !$this->user->plan_settings->enabled_barcodes->{$barcode->type}) {
            Alerts::add_error(l('global.info_message.plan_feature_no_access'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Duplicate the files */
            $barcode_image = \Altum\Uploads::copy_uploaded_file($barcode->barcode, \Altum\Uploads::get_path('barcodes'), \Altum\Uploads::get_path('barcodes'));

            /* Insert to database */
            $barcode_id = db()->insert('barcodes', [
                'user_id' => $this->user->user_id,
                'project_id' => $barcode->project_id,
                'name' => string_truncate($barcode->name . ' - ' . l('global.duplicated'), 64, null),
                'type' => $barcode->type,
                'value' => $barcode->value,
                'barcode' => $barcode_image,
                'settings' => $barcode->settings,
                'embedded_data' => $barcode->embedded_data,
                'datetime' => get_date(),
            ]);

            /* Clear the cache */
            cache()->deleteItem('barcodes_total?user_id=' . $this->user->user_id);
            cache()->deleteItem('barcodes_dashboard?user_id=' . $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($barcode->name) . '</strong>'));

            /* Redirect */
            redirect('barcode-update/' . $barcode_id);

        }

        redirect('barcodes');
    }

    public function bulk() {

        \Altum\Authentication::guard();

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('barcodes');
        }

        if(!isset($_POST['type'])) {
            redirect('barcodes');
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
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.barcodes')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('barcodes');
                    }

                    foreach($_POST['selected'] as $barcode_id) {
                        if($barcode = db()->where('barcode_id', $barcode_id)->where('user_id', $this->user->user_id)->getOne('barcodes', ['barcode'])) {
                            /* Delete the barcode */
                            (new Barcode())->delete($barcode_id);
                        }
                    }

                    break;

                case 'download':

                    $files = [];

                    foreach($_POST['selected'] as $barcode_id) {
                        if($barcode = db()->where('barcode_id', $barcode_id)->where('user_id', $this->user->user_id)->getOne('barcodes', ['barcode'])) {
                            $files[$barcode->barcode] = \Altum\Uploads::get_path('barcodes');
                        }
                    }

                    \Altum\Uploads::download_files_as_zip($files, l('global.download'));

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('barcodes');
    }

    public function delete() {
        \Altum\Authentication::guard();

        if(!settings()->codes->barcodes_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.barcodes')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('barcodes');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $barcode_id = (int) $_POST['barcode_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('barcodes');
        }

        /* Make sure the resource is created by the logged in user */
        if(!$barcode = db()->where('barcode_id', $barcode_id)->where('user_id', $this->user->user_id)->getOne('barcodes', ['barcode_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new Barcode())->delete($barcode->barcode_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $barcode->name . '</strong>'));

            redirect('barcodes');

        }

        redirect('barcodes');
    }

}
