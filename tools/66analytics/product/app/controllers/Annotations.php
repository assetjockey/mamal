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

class Annotations extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!$this->website || !settings()->analytics->annotations_is_enabled) {
            redirect('websites');
        }

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', 'website_id', 'annotation_id'], ['name'], ['annotation_id', 'last_datetime', 'datetime', 'name', 'chart_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'chart_datetime']));
        $filters->set_default_order_by($this->user->preferences->annotations_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `annotations` WHERE `user_id` = {$this->user->user_id} AND `website_id` = {$this->website->website_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('annotations?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the annotations list for the user */
        $annotations = [];
        $annotations_result = database()->query("
            SELECT 
                *
            FROM 
                 `annotations`
            WHERE 
                `user_id` = {$this->user->user_id}
                AND `website_id` = {$this->website->website_id}
                {$filters->get_sql_where()}
                    
            {$filters->get_sql_order_by()}
            {$paginator->get_sql_limit()}
        ");
        while($row = $annotations_result->fetch_object()) $annotations[] = $row;

        /* Export handler */
        process_export_csv($annotations, ['user_id', 'website_id', 'annotation_id', 'name', 'chart_datetime', 'last_datetime','datetime'], sprintf(l('annotations.title')));
        process_export_json($annotations, ['user_id', 'website_id', 'annotation_id', 'name', 'chart_datetime', 'last_datetime','datetime'], sprintf(l('annotations.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'total_annotations' => $total_rows,
            'annotations' => $annotations,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('annotations/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Make sure its not a request from a team member */
        if($this->team) {
            redirect('websites');
        }

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('annotations');
        }

        if(!isset($_POST['type'])) {
            redirect('annotations');
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

                    foreach($_POST['selected'] as $annotation_id) {
                        if(!$annotation = db()->where('annotation_id', $annotation_id)->where('website_id', $this->website->website_id)->where('user_id', $this->user->user_id)->getOne('annotations', ['annotation_id', 'website_id'])) {
                            continue;
                        }

                        /* Delete */
                        db()->where('annotation_id', $annotation_id)->where('website_id', $annotation->website_id)->where('user_id', $this->user->user_id)->delete('annotations');

                        /* Clear the cache */
                        cache()->deleteItemsByTag('annotations?website_id=' . $annotation->website_id);
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('annotations');
    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Make sure its not a request from a team member */
        if($this->team) {
            redirect('websites');
        }

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        $annotation_id = (int) $_POST['annotation_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$annotation = db()->where('annotation_id', $annotation_id)->where('website_id', $this->website->website_id)->where('user_id', $this->user->user_id)->getOne('annotations', ['annotation_id', 'website_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Database query */
            db()->where('annotation_id', $annotation_id)->where('website_id', $annotation->website_id)->where('user_id', $this->user->user_id)->delete('annotations');

            /* Clear the cache */
            cache()->deleteItemsByTag('annotations?website_id=' . $annotation->website_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $annotation->name . '</strong>'));

            redirect('annotations');
        }

        redirect('annotations');
    }

}
