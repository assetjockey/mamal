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

defined('ALTUMCODE') || die();

class Heatmaps extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!$this->website || !settings()->analytics->websites_heatmaps_is_enabled || ($this->website && $this->website->tracking_type == 'lightweight')) {
            redirect('websites');
        }

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled'], ['name'], ['heatmap_id', 'name', 'path', 'last_datetime', 'datetime'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->heatmaps_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `websites_heatmaps` WHERE `website_id` = {$this->website->website_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('heatmaps?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the heatmaps list for the website */
        $heatmaps = [];
        $heatmaps_result = database()->query("SELECT * FROM `websites_heatmaps` WHERE `website_id` = {$this->website->website_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $heatmaps_result->fetch_object()) $heatmaps[] = $row;

        /* Export handler */
        process_export_csv($heatmaps, ['heatmap_id', 'user_id', 'website_id', 'snapshot_id_desktop', 'snapshot_id_tablet', 'snapshot_id_mobile', 'name', 'path', 'is_enabled', 'datetime', 'last_datetime'], sprintf(l('heatmaps.title')));
        process_export_json($heatmaps, ['heatmap_id', 'user_id', 'website_id', 'snapshot_id_desktop', 'snapshot_id_tablet', 'snapshot_id_mobile', 'name', 'path', 'is_enabled', 'datetime', 'last_datetime'], sprintf(l('heatmaps.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Create Modal */
        $view = new \Altum\View('heatmap/heatmap_create_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Update Modal */
        $view = new \Altum\View('heatmap/heatmap_update_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Update Modal */
        $view = new \Altum\View('heatmap/heatmap_retake_snapshots_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Prepare the view */
        $data = [
            'total_heatmaps' => $total_rows,
            'heatmaps' => $heatmaps,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('heatmaps/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        if(!$this->website || !settings()->analytics->websites_heatmaps_is_enabled || ($this->website && $this->website->tracking_type == 'lightweight')) {
            redirect('websites');
        }

        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('heatmaps');
        }

        if(!isset($_POST['type'])) {
            redirect('heatmaps');
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

					db()->where('heatmap_id', $_POST['selected'], 'IN')->where('user_id', $this->user->user_id)->delete('websites_heatmaps');

                    break;
            }

            /* Clear the cache */
            cache()->deleteItem('website_heatmaps?website_id=' . $this->website->website_id);

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('heatmaps');
    }

    public function delete() {

        if(!$this->website || ($this->website && $this->website->tracking_type == 'lightweight')) {
            redirect('websites');
        }

        if($this->team) {
            redirect('websites');
        }

        \Altum\Authentication::guard();

        if (empty($_POST)) {
            throw_404();
        }

        $heatmap_id = (int) $_POST['heatmap_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Database query */
            db()->where('heatmap_id', $heatmap_id)->where('website_id', $this->website->website_id)->delete('websites_heatmaps');

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.delete2'));

            redirect('heatmaps');
        }

        redirect('heatmaps');
    }

}
