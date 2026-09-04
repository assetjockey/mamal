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
use Altum\Response;

defined('ALTUMCODE') || die();

class AdminPushNotifications extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['push_notification_id', 'status'], ['title', 'description'], ['push_notification_id', 'title', 'datetime', 'last_datetime', 'total_push_notifications', 'sent_push_notifications', 'last_sent_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_sent_datetime']));
        $filters->set_default_order_by('push_notification_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `push_notifications` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/push-notifications?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $push_notifications = [];
        $push_notifications_result = database()->query("
            SELECT
                `push_notifications`.*
            FROM
                `push_notifications`
            WHERE
                1 = 1
                {$filters->get_sql_where('push_notifications')}
                {$filters->get_sql_order_by('push_notifications')}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $push_notifications_result->fetch_object()) {
            $push_notifications[] = $row;
        }

        /* Export handler */
        process_export_json($push_notifications, ['push_notification_id', 'title', 'description', 'url', 'status', 'push_subscribers_ids', 'sent_push_subscribers_ids', 'sent_push_notifications', 'total_push_notifications', 'last_sent_datetime', 'datetime', 'last_datetime',]);
        process_export_csv($push_notifications, ['push_notification_id', 'title', 'description', 'url', 'status', 'push_subscribers_ids', 'sent_push_subscribers_ids', 'sent_push_notifications', 'total_push_notifications', 'last_sent_datetime', 'datetime', 'last_datetime',]);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'push_notifications' => $push_notifications,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'filters' => $filters
        ];

        $view = new \Altum\View('admin/push-notifications/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function get_segment_count() {

        if($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw_404();
        }

        \Altum\Authentication::guard();

        $segment = isset($_GET['segment']) ? input_clean($_GET['segment']) : 'all';

        switch($segment) {
            case 'all':

                $count = db()->getValue('push_subscribers', 'COUNT(*)');

                break;

            case 'filter':

                $query = db();

                $has_filters = false;

                /* Subscriber type */
                if(isset($_GET['filters_subscriber_type'])) {
                    $has_filters = true;

                    if(in_array('registered', $_GET['filters_subscriber_type']) && !in_array('guest', $_GET['filters_subscriber_type'])) {
                        $query->where('user_id', NULL, 'IS NOT');
                    }

                    if(in_array('guest', $_GET['filters_subscriber_type']) && !in_array('registered', $_GET['filters_subscriber_type'])) {
                        $query->where('user_id', NULL, 'IS');
                    }
                }

                /* Countries */
                if(isset($_GET['filters_countries'])) {
                    $has_filters = true;
                    $query->where('country_code', $_GET['filters_countries'], 'IN');
                }

                /* Continents */
                if(isset($_GET['filters_continents'])) {
                    $has_filters = true;
                    $query->where('continent_code', $_GET['filters_continents'], 'IN');
                }

                /* Device type */
				if(isset($_GET['filters_device_type'])) {
                    $has_filters = true;
                    $query->where('device_type', $_GET['filters_device_type'], 'IN');
                }

				/* Languages */
				if(isset($_GET['filters_languages'])) {
					$has_filters = true;
					$query->where('browser_language', $_GET['filters_languages'], 'IN');
				}

				/* Cities */
				if(!empty($_GET['filters_cities'])) {
					$_GET['filters_cities'] = is_array($_GET['filters_cities']) ? $_GET['filters_cities'] : explode(',', $_GET['filters_cities']);

					if(count($_GET['filters_cities'])) {
						$_GET['filters_cities'] = array_map(function($city) {
							return query_clean($city);
						}, $_GET['filters_cities']);
						$_GET['filters_cities'] = array_unique($_GET['filters_cities']);

						$has_filters = true;
						$query->where('city_name', $_GET['filters_cities'], 'IN');
					}
				}

				/* Languages */
				if(isset($_GET['filters_browser_languages'])) {
					$_GET['filters_browser_languages'] = array_filter($_GET['filters_browser_languages'], function($locale) {
						return array_key_exists($locale, get_locale_languages_array());
					});

					$has_filters = true;
					$query->where('browser_language', $_GET['filters_browser_languages'], 'IN');
				}

				/* Filters operating systems */
				if(isset($_GET['filters_operating_systems'])) {
					$_GET['filters_operating_systems'] = array_filter($_GET['filters_operating_systems'], function($os_name) {
						return in_array($os_name, ['iOS', 'Android', 'Windows', 'OS X', 'Linux', 'Ubuntu', 'Chrome OS']);
					});

					$has_filters = true;
					$query->where('os_name', $_GET['filters_operating_systems'], 'IN');
				}

				/* Filters browsers */
				if(isset($_GET['filters_browsers'])) {
					$_GET['filters_browsers'] = array_filter($_GET['filters_browsers'], function($browser_name) {
						return in_array($browser_name, ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Samsung Internet']);
					});

					$has_filters = true;
					$query->where('browser_name', $_GET['filters_browsers'], 'IN');
				}

                $count = $has_filters ? $query->getValue('push_subscribers', 'COUNT(*)') : 0;

                break;

            default:
                $count = null;
                break;
        }

        Response::json('', 'success', ['count' => $count]);
    }

    public function duplicate() {

        if (empty($_POST)) {
            throw_404();
        }

        $push_notification_id = (int) $_POST['push_notification_id'];

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$push_notification = db()->where('push_notification_id', $push_notification_id)->getOne('push_notifications')) {
            redirect('admin/push-notifications');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Insert to database */
            $push_notification_id = db()->insert('push_notifications', [
                'title' => string_truncate($push_notification->title . ' - ' . l('global.duplicated'), 64, null),
                'description' => $push_notification->description,
                'url' => $push_notification->url,
                'segment' => $push_notification->segment,
                'settings' => $push_notification->settings,
                'push_subscribers_ids' => $push_notification->push_subscribers_ids,
                'total_push_notifications' => $push_notification->total_push_notifications,
                'status' => 'draft',
                'datetime' => get_date(),
            ]);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($push_notification->name) . '</strong>'));

            /* Redirect */
            redirect('admin/push-notification-update/' . $push_notification_id);

        }

        redirect('admin/push-notifications');
    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/push-notifications');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/push-notifications');
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

                    foreach($_POST['selected'] as $id) {
                        db()->where('push_notification_id', $id)->delete('push_notifications');
                    }
                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/push-notifications');
    }

    public function delete() {

        $push_notification_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$push_notification = db()->where('push_notification_id', $push_notification_id)->getOne('push_notifications', ['push_notification_id', 'title'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the resource */
            db()->where('push_notification_id', $push_notification_id)->delete('push_notifications');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $push_notification->title . '</strong>'));

        }

        redirect('admin/push-notifications');
    }

}
