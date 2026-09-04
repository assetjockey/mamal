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

class AdminTeams extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['team_id', 'user_id'], ['name'], ['last_datetime', 'datetime', 'name', 'team_id'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by('team_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `teams` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/teams?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $teams = [];
        $teams_result = database()->query("
            SELECT
                `teams`.*, 
                COUNT(`teams_members`.`team_member_id`) AS `members`,
                `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `teams`
            LEFT JOIN 
                `teams_members` ON `teams`.`team_id` = `teams_members`.`team_id` 
            LEFT JOIN
                `users` ON `teams`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('teams')}
            
            GROUP BY `teams`.`team_id`
                
            {$filters->get_sql_order_by('teams')}
            {$paginator->get_sql_limit()}
        ");
        while($row = $teams_result->fetch_object()) {
            $teams[] = $row;
        }

        /* Export handler */
        process_export_json($teams, ['team_id', 'user_id', 'name', 'members', 'datetime', 'last_datetime'], sprintf(l('teams.title')));
        process_export_csv($teams, ['team_id', 'user_id', 'name', 'members', 'datetime', 'last_datetime'], sprintf(l('teams.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'teams' => $teams,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/teams/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/teams');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/teams');
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

                    foreach($_POST['selected'] as $team_id) {

                        /* Delete the team */
                        db()->where('team_id', $team_id)->delete('teams');

                        /* Clear the cache */
                        cache()->deleteItemsByTag('team_id=' . $team_id);
                        cache()->deleteItem('team?team_id=' . $team_id);

                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/teams');
    }

    public function delete() {

        $team_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$team = db()->where('team_id', $team_id)->getOne('teams', ['team_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the team */
            db()->where('team_id', $team_id)->delete('teams');

            /* Clear the cache */
            cache()->deleteItemsByTag('team_id=' . $team_id);
            cache()->deleteItem('team?team_id=' . $team_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $team->name . '</strong>'));

        }

        redirect('admin/teams');
    }

}
