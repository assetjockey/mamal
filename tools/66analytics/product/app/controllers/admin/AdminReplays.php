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

class AdminReplays extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['replay_id', 'website_id', 'user_id', 'is_offloaded'], [], ['replay_id', 'size', 'events', 'expiration_date', 'last_datetime', 'datetime'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by('session_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `sessions_replays` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/replays?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the users */
        $replays = [];
        $replays_result = database()->query("
            SELECT
                `sessions_replays`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `sessions_replays`
            LEFT JOIN
                `users` ON `sessions_replays`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('sessions_replays')}
                {$filters->get_sql_order_by('sessions_replays')}
            
            {$paginator->get_sql_limit()}
        ");
        while($row = $replays_result->fetch_object()) {
            $row->duration = (new \DateTime($row->last_datetime))->getTimestamp() - (new \DateTime($row->datetime))->getTimestamp();
            $replays[] = $row;
        }

        /* Export handler */
        process_export_csv($replays, ['replay_id', 'session_id', 'visitor_id', 'website_id', 'user_id', 'events', 'size', 'is_offloaded', 'last_datetime', 'datetime', 'expiration_date'], sprintf(l('replays.title')));
        process_export_json($replays, ['replay_id', 'session_id', 'visitor_id', 'website_id', 'user_id', 'events', 'size', 'is_offloaded', 'last_datetime', 'datetime', 'expiration_date'], sprintf(l('replays.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'replays' => $replays,
            'pagination' => $pagination,
            'filters' => $filters
        ];

        $view = new \Altum\View('admin/replays/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/replays');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/replays');
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

                    foreach($_POST['selected'] as $replay_id) {
                        (new SessionsReplays())->delete($replay_id);
                    }
                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/replays');
    }

    public function delete() {

        $replay_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$replay = db()->where('replay_id', $replay_id)->has('sessions_replays')) {
            redirect('admin/replays');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new SessionsReplays())->delete($replay_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $replay->name . '</strong>'));

        }

        redirect('admin/replays');
    }

}
