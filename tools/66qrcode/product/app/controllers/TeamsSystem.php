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


defined('ALTUMCODE') || die();

class TeamsSystem extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('teams')) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Teams owned */
        $total_teams = db()->where('user_id', $this->user->user_id)->getValue('teams', 'count(*)');

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters([], [], ['team_id']));
        $filters->set_default_order_by('team_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $paginator = (new \Altum\Paginator($total_teams, $filters->get_results_per_page(), 1, url('teams?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the teams list for the user */
        $teams = [];
        $teams_result = database()->query("
            SELECT `teams`.*, COUNT(`teams_members`.`team_member_id`) AS `members` 
            FROM `teams` 
            LEFT JOIN `teams_members` ON `teams`.`team_id` = `teams_members`.`team_id` 
            WHERE `teams`.`user_id` = {$this->user->user_id} {$filters->get_sql_where('teams')} 
            GROUP BY `teams`.`team_id` 
            {$filters->get_sql_order_by('teams')} 
            {$paginator->get_sql_limit()}
        ");
        while($row = $teams_result->fetch_object()) {
            $row->members_preview = [];

            if($row->members) {
                $row->members_preview = db()->where('team_id', $row->team_id)->get('teams_members', 5, ['team_member_id', 'user_id', 'user_email']);
            }

            $teams[] = $row;
        }

        /* Prepare the pagination view */
        $teams_pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);



        /* Teams member */
        $total_teams_member = db()->where('user_id', $this->user->user_id)->orWhere('user_email', $this->user->email)->getValue('teams_members', 'count(*)');

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters([], [], ['team_member_id']));
        $filters->set_default_order_by('team_member_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $paginator = (new \Altum\Paginator($total_teams_member, $filters->get_results_per_page(), 1, url('teams-member?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the teams list for the user */
        $teams_member = [];
        $teams_member_result = database()->query("
            SELECT `teams`.`name`, `teams_members`.*
            FROM `teams_members` 
            LEFT JOIN `teams` ON `teams`.`team_id` = `teams_members`.`team_id` 
            WHERE 
                  (`teams_members`.`user_id` = {$this->user->user_id} 
                  OR `teams_members`.`user_email` = '{$this->user->email}')
                  {$filters->get_sql_where('teams_members')} 
            {$filters->get_sql_order_by('teams_members')} 
            LIMIT {$filters->results_per_page}
        ");
        while($row = $teams_member_result->fetch_object()) {
            $row->access = json_decode($row->access);
            $teams_member[] = $row;
        }

        /* Prepare the pagination view */
        $teams_member_pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'total_teams' => $total_teams,
            'teams' => $teams,
            'teams_pagination' => $teams_pagination,

            'total_teams_member' => $total_teams_member,
            'teams_member' => $teams_member,
            'teams_member_pagination' => $teams_member_pagination,

            'teams_access' => require APP_PATH . 'includes/teams_access.php',
        ];

        $view = new \Altum\View('teams-system/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
