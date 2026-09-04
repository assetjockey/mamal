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
use Altum\Models\SessionsReplays;

defined('ALTUMCODE') || die();

class AdminAnnotations extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', 'website_id', 'annotation_id'], ['name'], ['annotation_id', 'last_datetime', 'datetime', 'name', 'chart_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'chart_datetime']));
        $filters->set_default_order_by($this->user->preferences->annotations_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `annotations` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/annotations?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the users */
        $annotations = [];
        $annotations_result = database()->query("
            SELECT
                `annotations`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `annotations`
            LEFT JOIN
                `users` ON `annotations`.`user_id` = `users`.`user_id`
            WHERE 
                1 = 1
                {$filters->get_sql_where('annotations')}
                {$filters->get_sql_order_by('annotations')}
            
            {$paginator->get_sql_limit()}
        ");
        while($row = $annotations_result->fetch_object()) $annotations[] = $row;

        /* Export handler */
        process_export_csv($annotations, ['user_id', 'website_id', 'annotation_id', 'name', 'chart_datetime', 'last_datetime','datetime'], sprintf(l('annotations.title')));
        process_export_json($annotations, ['user_id', 'website_id', 'annotation_id', 'name', 'chart_datetime', 'last_datetime','datetime'], sprintf(l('annotations.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'annotations' => $annotations,
            'pagination' => $pagination,
            'filters' => $filters
        ];

        $view = new \Altum\View('admin/annotations/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/annotations');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/annotations');
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

                    \Altum\Cache::store_initialize();

                    foreach($_POST['selected'] as $annotation_id) {
                        if(!$annotation = db()->where('annotation_id', $annotation_id)->where('user_id', $this->user->user_id)->getOne('annotations', ['annotation_id', 'website_id'])) {
                            continue;
                        }

                        /* Delete */
                        db()->where('annotation_id', $annotation_id)->delete('annotations');

                        /* Clear the cache */
                        cache()->deleteItemsByTag('annotations?website_id=' . $annotation->website_id);
                    }
                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/annotations');
    }

    public function delete() {

        $annotation_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$annotation = db()->where('annotation_id', $annotation_id)->getOne('annotations', ['annotation_id', 'website_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the annotation */
            db()->where('annotation_id', $annotation->annotation_id)->delete('annotations');

            /* Clear the cache */
            cache()->deleteItemsByTag('annotations?website_id=' . $annotation->website_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $annotation->name . '</strong>'));

        }

        redirect('admin/annotations');
    }

}
