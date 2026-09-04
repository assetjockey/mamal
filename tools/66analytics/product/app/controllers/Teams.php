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

class Teams extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Create Modal */
        $view = new \Altum\View('teams/team_create_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Update Modal */
        $view = new \Altum\View('teams/team_update_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Delete Modal */
        $view = new \Altum\View('teams/team_delete_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Delete Modal */
        $view = new \Altum\View('teams/team_association_delete_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Get the teams list for of the owner user */
        $teams_result = database()->query("SELECT `teams`.*, COUNT(`teams_associations`.`team_association_id`) AS `users` FROM `teams` LEFT JOIN `teams_associations` ON `teams_associations`.`team_id` = `teams`.`team_id` WHERE `teams`.`user_id` = {$this->user->user_id} GROUP BY `teams`.`team_id`");

        /* Get the teams that the current user is enrolled into */
        $teams_associations_result = database()->query("SELECT `teams`.`team_id`, `teams`.`name`, `teams`.`websites_ids`, `teams_associations`.* FROM `teams_associations` LEFT JOIN `teams` ON `teams_associations`.`team_id` = `teams`.`team_id` WHERE `teams_associations`.`user_id` = {$this->user->user_id} OR `teams_associations`.`user_email` = '{$this->user->email}'");

        /* Prepare the view */
        $data = [
            'teams_result'              => $teams_result,
            'teams_associations_result' => $teams_associations_result,
        ];

        $view = new \Altum\View('teams/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
