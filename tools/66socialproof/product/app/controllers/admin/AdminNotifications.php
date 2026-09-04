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

class AdminNotifications extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['notification_id', 'user_id', 'campaign_id', 'type', 'is_enabled'], ['name'], ['notification_id', 'last_datetime', 'datetime', 'name', 'impressions', 'hovers', 'clicks', 'form_submissions'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->notifications_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `notifications` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/notifications?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $notifications = [];
        $notifications_result = database()->query("
            SELECT
                `notifications`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `notifications`
            LEFT JOIN
                `users` ON `notifications`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('notifications')}
                {$filters->get_sql_order_by('notifications')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $notifications_result->fetch_object()) {
            $notifications[] = $row;
        }

        /* Export handler */
        process_export_csv($notifications, ['notification_id', 'campaign_id', 'user_id', 'name', 'type', 'impressions', 'hovers', 'clicks', 'form_submissions', 'is_enabled', 'last_datetime', 'datetime'], sprintf(l('admin_notifications.title')));
        process_export_json($notifications, ['notification_id', 'campaign_id', 'user_id', 'name', 'type', 'impressions', 'hovers', 'clicks', 'form_submissions', 'is_enabled', 'last_datetime', 'datetime'], sprintf(l('admin_notifications.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'notifications' => $notifications,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/notifications/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/notifications');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/notifications');
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

                    (new \Altum\Models\Notification())->bulk_delete($_POST['selected'], null, true);

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/notifications');
    }

    public function delete() {

        $notification_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$notification = db()->where('notification_id', $notification_id)->getOne('notifications', ['user_id', 'notification_id'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the notification */
            (new \Altum\Models\Notification())->delete($notification->notification_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $notification->name . '</strong>'));

        }

        redirect('admin/notifications');
    }

}
