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
use Altum\Title;

defined('ALTUMCODE') || die();

class Campaign extends Controller {

	public function index() {

		\Altum\Authentication::guard();

		$campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;
		$method = isset($this->params[1]) && in_array($this->params[1], ['settings', 'statistics']) ? $this->params[1] : 'settings';

		/* Make sure the campaign exists and is accessible to the user */
		if(!$campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->user->user_id)->getOne('campaigns')) {
			redirect('campaigns');
		}

		/* Get the custom branding details */
		$campaign->branding = json_decode($campaign->branding);

		/* Set a custom title */
		Title::set(sprintf(l('campaign.title'), $campaign->name));

		/* Get available custom domains */
		$domains = (new \Altum\Models\Domain())->get_available_domains_by_user_id($this->user->user_id);

		/* Handle code for different parts of the page */
		switch($method) {
			case 'settings':

				/* Prepare the filtering system */
				$filters = (new \Altum\Filters(['is_enabled', 'type'], ['name'], ['notification_id', 'last_datetime', 'datetime', 'name', 'impressions', 'hovers', 'clicks', 'form_submissions'], allowed_datetime_fields: ['datetime', 'last_datetime']));
				$filters->set_default_order_by($this->user->preferences->notifications_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
				$filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

				/* Prepare the paginator */
				$total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `notifications` WHERE `campaign_id` = {$campaign->campaign_id} AND `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
				$paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('campaign/' . $campaign->campaign_id . '?' . $filters->get_get() . '&page={{PAGE}}')));

				/* Get the notifications list for the user */
				$notifications = [];
				$notifications_result = database()->query("SELECT * FROM `notifications` WHERE `campaign_id` = {$campaign->campaign_id} AND `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
				while($row = $notifications_result->fetch_object()) {
					$row->settings = json_decode($row->settings ?? '');
					$notifications[] = $row;
				}

				/* Export handler */
				process_export_csv_new($notifications, ['notification_id', 'campaign_id', 'user_id', 'name', 'type', 'impressions', 'hovers', 'clicks', 'form_submissions', 'is_enabled', 'settings', 'last_datetime', 'datetime'], ['settings'], sprintf(l('notifications.title')));
				process_export_json($notifications, ['notification_id', 'campaign_id', 'user_id', 'name', 'type', 'impressions', 'hovers', 'clicks', 'form_submissions', 'is_enabled', 'settings', 'last_datetime', 'datetime'], sprintf(l('notifications.title')));

				/* Prepare the pagination view */
				$pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

				/* Prepare the method View */
				$data = [
					'campaign'              => $campaign,
					'notifications'         => $notifications,
					'notifications_total'   => $total_rows,
					'pagination'            => $pagination,
					'filters'               => $filters,
					'domains'               => $domains,
					'notifications_config'  => require APP_PATH . 'includes/enabled_notifications.php'
				];

				$view = new \Altum\View('campaign/' . $method . '.method', (array) $this);

				$this->add_view_content('method', $view->run($data));

				break;

			case 'statistics':

				$action = isset($this->params[2]) && in_array($this->params[2], ['reset']) ? $this->params[2] : null;

				if($action) {
					switch($action) {
						case 'reset':

							if (empty($_POST)) {
								throw_404();
							}

							/* Team checks */
							if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.campaigns')) {
								Alerts::add_error(l('global.info_message.team_no_access'));
								redirect('campaign/' . $campaign->campaign_id . '/statistics');
							}

							//ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

							if(!\Altum\Csrf::check()) {
								Alerts::add_error(l('global.error_message.invalid_csrf_token'));
								redirect('campaign/' . $campaign->campaign_id . '/statistics');
							}

							$datetime = \Altum\Date::get_start_end_dates_new($_POST['start_date'], $_POST['end_date']);

							if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

								/* Clear statistics data */
								database()->query("DELETE FROM `track_notifications` WHERE `campaign_id` = {$campaign->campaign_id} AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')");

								/* Set a nice success message */
								Alerts::add_success(l('global.success_message.update2'));

								redirect('campaign/' . $campaign->campaign_id . '/statistics');

							}

							redirect('campaign/' . $campaign->campaign_id . '/statistics');

							break;
					}
				}

				$datetime = \Altum\Date::get_start_end_dates_new();

				/* Query for the statistics of the notification */
				$logs_chart = [];
				$logs_total = [
					'impression'        => 0,
					'hover'             => 0,
					'click'             => 0,
					'ctr'               => 0,
					'form_submission'   => 0,
					'conversions'       => 0,
				];

				$convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

				/* Logs for the charts */
				$logs_result = database()->query("
                    SELECT
                         `type`,
                         COUNT(`id`) AS `total`,
                         DATE_FORMAT({$convert_tz_sql}, '{$datetime['query_date_format']}') AS `formatted_date`
                    FROM
                         `track_notifications`
                    WHERE
                        `campaign_id` = {$campaign->campaign_id}
                        AND ({$convert_tz_sql} BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    GROUP BY
                        `formatted_date`,
                        `type`
                    ORDER BY
                        `formatted_date`
                ");

				$has_data = $logs_result->num_rows;

				/* Generate the raw chart data and save logs for later usage */
				while($row = $logs_result->fetch_object()) {
					$logs[] = $row;

					$row->formatted_date = $datetime['process']($row->formatted_date, true);

					/* Handle if the date key is not already set */
					if(!array_key_exists($row->formatted_date, $logs_chart)) {
						$logs_chart[$row->formatted_date] = [
							'impression'        => 0,
							'hover'             => 0,
							'click'             => 0,
							'form_submission'   => 0,
						];
					}

					$logs_chart[$row->formatted_date][$row->type] = $row->total;

					/* Count totals */
					if(in_array($row->type, ['impression', 'hover', 'click', 'form_submission'])) {
						$logs_total[$row->type] += $row->total;
					}
				}

				/* CTR on mouse clicks */
				$logs_total['ctr'] = $logs_total['impression'] && $logs_total['click'] ? ($logs_total['click'] / $logs_total['impression']) * 100 : 0;

				/* Calculate form submissions conversions */
				$logs_total['conversions'] = $logs_total['impression'] && $logs_total['form_submission'] ? ($logs_total['form_submission'] / $logs_total['impression']) * 100 : 0;

				$logs_chart = get_chart_data($logs_chart);

				/* Get page performance data */
				$top_pages_result = database()->query("
                    SELECT 
                        `path`,
                        SUM(CASE WHEN `type` = 'impression' THEN 1 ELSE 0 END) AS `impressions`,
                        SUM(CASE WHEN `type` = 'hover' THEN 1 ELSE 0 END) AS `hovers`,
                        SUM(CASE WHEN `type` = 'click' THEN 1 ELSE 0 END) AS `clicks`,
                        SUM(CASE WHEN `type` = 'form_submission' THEN 1 ELSE 0 END) AS `form_submissions`,
                        COUNT(`id`) AS `total`
                    FROM 
                        `track_notifications`
                    WHERE
                        `campaign_id` = {$campaign->campaign_id}
                        AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    GROUP BY 
                        `path`
                    ORDER BY 
                        `total` DESC 
                    LIMIT 25
                ");

				/* Get notifications performance data */
				$notifications_performance_result = database()->query("
                    SELECT
                        `notifications`.`notification_id`,
                        `notifications`.`name`,
                        `notifications`.`type`,
                        SUM(CASE WHEN `track_notifications`.`type` = 'impression' THEN 1 ELSE 0 END) AS `impressions`,
                        SUM(CASE WHEN `track_notifications`.`type` = 'click' THEN 1 ELSE 0 END) AS `clicks`,
                        SUM(CASE WHEN `track_notifications`.`type` = 'form_submission' THEN 1 ELSE 0 END) AS `form_submissions`,
                        COUNT(`track_notifications`.`id`) AS `total`
                    FROM
                        `track_notifications`
                    LEFT JOIN
                        `notifications` ON `track_notifications`.`notification_id` = `notifications`.`notification_id`
                    WHERE
                        `track_notifications`.`campaign_id` = {$campaign->campaign_id}
                        AND (`track_notifications`.`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    GROUP BY
                        `track_notifications`.`notification_id`,
                        `notifications`.`name`,
                        `notifications`.`type`
                    ORDER BY
                        `form_submissions` DESC,
                        `clicks` DESC,
                        `impressions` DESC
                ");

				/* Prepare the method View */
				$data = [
					'top_pages_result'  => $top_pages_result,
					'notifications_performance_result' => $notifications_performance_result,
					'campaign'          => $campaign,
					'has_data'          => $has_data,
					'logs_chart'        => $logs_chart,
					'logs_total'        => $logs_total,
					'datetime'          => $datetime,
					'domains'           => $domains,
				];

				$view = new \Altum\View('campaign/' . $method . '.method', (array) $this);

				$this->add_view_content('method', $view->run($data));

				break;
		}

		/* Get available notification handlers */
		$notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

		/* Prepare the view */
		$data = [
			'campaign' => $campaign,
			'method' => $method,
			'domains' => $domains,
			'notification_handlers' => $notification_handlers,
		];

		$view = new \Altum\View('campaign/index', (array) $this);

		$this->add_view_content('content', $view->run($data));

	}

	public function duplicate() {
		\Altum\Authentication::guard();

		/* Team checks */
		if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.campaigns')) {
			Alerts::add_error(l('global.info_message.team_no_access'));
			redirect('campaigns');
		}

		if (empty($_POST)) {
			throw_404();
		}

		/* Check for the plan limit */
		$account_total_campaigns = database()->query("SELECT COUNT(*) AS `total` FROM `campaigns` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total;
		if($this->user->plan_settings->campaigns_limit != -1 && $account_total_campaigns >= $this->user->plan_settings->campaigns_limit) {
			Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
			redirect('campaigns');
		}

		$campaign_id = (int) $_POST['campaign_id'];

		//ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

		if(!\Altum\Csrf::check()) {
			Alerts::add_error(l('global.error_message.invalid_csrf_token'));
			redirect('campaigns');
		}

		/* Get & make sure the campaign exists */
		if(!$campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->user->user_id)->getOne('campaigns')) {
			redirect('campaigns');
		}

		if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

			/* Determine the default settings */
			$pixel_key = string_generate(32);
			while(db()->where('pixel_key', $pixel_key)->getValue('campaigns', 'pixel_key')) {
				$pixel_key = string_generate(32);
			}

			/* Insert to database */
			$campaign_id = db()->insert('campaigns', [
				'user_id' => $this->user->user_id,
				'pixel_key' => $pixel_key,
				'name' => string_truncate($campaign->name . ' - ' . l('global.duplicated'), 64, null),
				'domain' => $campaign->domain,
				'branding' => $campaign->branding,
				'is_enabled' => 0,
				'datetime' => get_date(),
			]);

			/* Get all notifications of this campaign */
			$notifications = db()->where('campaign_id', $campaign->campaign_id)->get('notifications');

			/* Duplicate all of them */
			foreach($notifications as $notification) {
				$notification_key = md5($this->user->user_id . $notification->notification_id . $campaign_id . time());

				/* Insert to database */
				db()->insert('notifications', [
					'user_id' => $this->user->user_id,
					'campaign_id' => $campaign_id,
					'name' => $notification->name,
					'type' => $notification->type,
					'settings' => $notification->settings,
					'notification_key' => $notification_key,
					'is_enabled' => 0,
					'datetime' => get_date(),
				]);
			}

			/* Set a nice success message */
			Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($campaign->name) . '</strong>'));

			/* Redirect */
			redirect('campaign/' . $campaign_id);

		}

		redirect('campaigns');
	}

	public function bulk() {

		\Altum\Authentication::guard();

		/* Check for any errors */
		if (empty($_POST)) {
			throw_404();
		}

		if(empty($_POST['selected'])) {
			redirect('campaigns');
		}

		if(!isset($_POST['type'])) {
			redirect('campaigns');
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

					/* Team checks */
					if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.campaigns')) {
						Alerts::add_error(l('global.info_message.team_no_access'));
						redirect('campaigns');
					}

					(new \Altum\Models\Campaign())->bulk_delete($_POST['selected'], $this->user->user_id);

					break;
			}

			session_start();

			/* Set a nice success message */
			Alerts::add_success(l('bulk_delete_modal.success_message'));

		}

		redirect('campaigns');
	}

	public function delete() {
		\Altum\Authentication::guard();

		/* Team checks */
		if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.campaigns')) {
			Alerts::add_error(l('global.info_message.team_no_access'));
			redirect('campaigns');
		}

		if (empty($_POST)) {
			throw_404();
		}

		$campaign_id = (int) $_POST['campaign_id'];

		//ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

		if(!\Altum\Csrf::check()) {
			Alerts::add_error(l('global.error_message.invalid_csrf_token'));
			redirect('campaigns');
		}

		if(!$campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->user->user_id)->getOne('campaigns', ['campaign_id', 'name'])) {
			throw_404();
		}

		if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

			(new \Altum\Models\Campaign())->delete($campaign->campaign_id, $this->user->user_id);

			/* Set a nice success message */
			Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $campaign->name . '</strong>'));

			redirect('campaigns');

		}

		redirect('campaigns');
	}
}
