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

class AdminHeatmaps extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['heatmap_id', 'is_enabled', 'website_id', 'user_id'], ['name', 'path'], ['heatmap_id', 'mobile_size', 'tablet_size', 'desktop_size', 'path', 'name', 'last_datetime', 'datetime'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->heatmaps_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `websites_heatmaps` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/heatmaps?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the users */
        $heatmaps = [];
        $heatmaps_result = database()->query("
            SELECT
                `websites_heatmaps`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `websites_heatmaps`
            LEFT JOIN
                `users` ON `websites_heatmaps`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('websites_heatmaps')}
                {$filters->get_sql_order_by('websites_heatmaps')}
            
            {$paginator->get_sql_limit()}
        ");
        while($row = $heatmaps_result->fetch_object()) {
            $heatmaps[] = $row;
        }

        /* Export handler */
        process_export_csv($heatmaps, ['heatmap_id', 'website_id', 'user_id', 'snapshot_id_desktop', 'desktop_size', 'snapshot_id_tablet', 'tablet_size', 'snapshot_id_mobile', 'mobile_size', 'name', 'path', 'is_enabled', 'last_datetime', 'datetime'], sprintf(l('heatmaps.title')));
        process_export_json($heatmaps, ['heatmap_id', 'website_id', 'user_id', 'snapshot_id_desktop', 'desktop_size', 'snapshot_id_tablet', 'tablet_size', 'snapshot_id_mobile', 'mobile_size', 'name', 'path', 'is_enabled', 'last_datetime', 'datetime'], sprintf(l('heatmaps.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'heatmaps' => $heatmaps,
            'pagination' => $pagination,
            'filters' => $filters
        ];

        $view = new \Altum\View('admin/heatmaps/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/heatmaps');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/heatmaps');
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

					$websites_ids = db()->where('heatmap_id', $_POST['selected'], 'IN')->get('websites_heatmaps', null, 'website_id');

					/* Clear the cache */
					foreach($websites_ids as $website_id) {
						cache()->deleteItem('website_heatmaps?website_id=' . $website_id);
					}

					/* Database query */
					db()->where('heatmap_id', $_POST['selected'], 'IN')->delete('websites_heatmaps');

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/heatmaps');
    }

    public function delete() {

        $heatmap_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$heatmap = db()->where('heatmap_id', $heatmap_id)->getOne('websites_heatmaps', ['website_id', 'user_id', 'heatmap_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Database query */
            db()->where('heatmap_id', $heatmap_id)->delete('websites_heatmaps');

            /* Clear the cache */
            cache()->deleteItem('website_heatmaps?website_id=' . $heatmap->website_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $heatmap->name . '</strong>'));

        }

        redirect('admin/heatmaps');
    }

}
