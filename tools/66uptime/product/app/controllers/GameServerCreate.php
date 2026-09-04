<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

namespace Altum\Controllers;

use Altum\Alerts;

defined('ALTUMCODE') || die();

class GameServerCreate extends Controller {

	public function index() {

		if(!settings()->monitors_heartbeats->game_servers_is_enabled) {
			throw_404();
		}

		\Altum\Authentication::guard();

		/* Team checks */
		if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.game_servers')) {
			Alerts::add_error(l('global.info_message.team_no_access'));
			redirect('game-servers');
		}

		/* Check for the plan limit */
		$total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `game_servers` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

		if($this->user->plan_settings->game_servers_limit != -1 && $total_rows >= $this->user->plan_settings->game_servers_limit) {
			Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
			redirect('game-servers');
		}

		/* Get available projects */
		$projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

		/* Monitors vars */
        $game_server_check_intervals = require APP_PATH . 'includes/game_server_check_intervals.php';
        $game_server_types = require APP_PATH . 'includes/game_server_types.php';
		$monitor_timeouts = require APP_PATH . 'includes/monitor_timeouts.php';

		if(!empty($_POST)) {
			$_POST['name'] = input_clean($_POST['name'], 64);
			$_POST['target'] = input_clean($_POST['target']);
            $_POST['port'] = isset($_POST['port']) ? (int) $_POST['port'] : 0;
            $_POST['query_port'] = isset($_POST['query_port']) ? (int) $_POST['query_port'] : 0;
            $_POST['type'] = isset($_POST['type']) && array_key_exists($_POST['type'], $game_server_types) ? $_POST['type'] : array_key_first($game_server_types);
            $_POST['check_interval_seconds'] = in_array($_POST['check_interval_seconds'], $this->user->plan_settings->game_servers_check_intervals ?? []) ? (int) $_POST['check_interval_seconds'] : reset($this->user->plan_settings->game_servers_check_intervals);
			$_POST['timeout_seconds'] = array_key_exists($_POST['timeout_seconds'], $monitor_timeouts) ? (int) $_POST['timeout_seconds'] : 5;
			$_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

			//ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

			/* Check for any errors */
			$required_fields = ['name', 'target', 'port', 'query_port'];
			foreach($required_fields as $field) {
				if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
					Alerts::add_field_error($field, l('global.error_message.empty_field'));
				}
			}

			if(!\Altum\Csrf::check()) {
				Alerts::add_error(l('global.error_message.invalid_csrf_token'));
			}

			if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
				$settings = json_encode([
					'check_interval_seconds' => $_POST['check_interval_seconds'],
					'timeout_seconds' => $_POST['timeout_seconds'],
				]);

				/* Database query */
				$game_server_id = db()->insert('game_servers', [
					'project_id' => $_POST['project_id'],
					'user_id' => $this->user->user_id,
					'name' => $_POST['name'],
					'type' => $_POST['type'],
					'target' => $_POST['target'],
                    'port' => $_POST['port'],
                    'query_port' => $_POST['query_port'],
					'settings' => $settings,
					'next_check_datetime' => get_date(),
					'datetime' => get_date(),
				]);

				/* Set a nice success message */
				Alerts::add_success(l('game_server_create.success_message'));

				redirect('game-server/' . $game_server_id);
			}

		}

		/* Set default values */
		$values = [
            'name' => $_POST['name'] ?? '',
			'target' => $_POST['target'] ?? '',
            'port' => $_POST['port'] ?? 25565,
            'query_port' => $_POST['query_port'] ?? 25565,
            'type' => $_POST['type'] ?? array_key_first($game_server_types),
            'check_interval_seconds' => $_POST['check_interval_seconds'] ?? reset($this->user->plan_settings->game_servers_check_intervals),
			'timeout_seconds' => $_POST['timeout_seconds'] ?? 5,
			'project_id' => $_POST['project_id'] ?? '',
		];

		/* Prepare the view */
		$data = [
			'projects' => $projects,
            'game_server_check_intervals' => $game_server_check_intervals,
            'game_server_types' => $game_server_types,
			'monitor_timeouts' => $monitor_timeouts,
			'values' => $values
		];

		$view = new \Altum\View('game-server-create/index', (array) $this);

		$this->add_view_content('content', $view->run($data));

	}

}
