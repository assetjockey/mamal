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

class AdminBroadcastSubscribers extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['broadcast_subscriber_id', 'user_id', 'status', 'source', 'country_code', 'continent_code', 'device_type', 'language'], ['email', 'name', 'city_name', 'ip'], ['broadcast_subscriber_id', 'user_id', 'email', 'name', 'status', 'source', 'datetime', 'last_datetime', 'unsubscribed_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'unsubscribed_datetime']));
        $filters->set_default_order_by('broadcast_subscriber_id', isset($this->user->preferences->default_order_type) ? $this->user->preferences->default_order_type : settings()->main->default_order_type);
        $filters->set_default_results_per_page(isset($this->user->preferences->default_results_per_page) ? $this->user->preferences->default_results_per_page : settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `broadcast_subscribers` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), isset($_GET['page']) ? $_GET['page'] : 1, url('admin/broadcast-subscribers?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $broadcast_subscribers = [];
        $broadcast_subscribers_result = database()->query("
            SELECT
                `broadcast_subscribers`.*,
                `users`.`name` AS `user_name`,
                `users`.`email` AS `user_email`,
                `users`.`avatar` AS `user_avatar`
            FROM
                `broadcast_subscribers`
            LEFT JOIN
                `users` ON `broadcast_subscribers`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('broadcast_subscribers')}
                {$filters->get_sql_order_by('broadcast_subscribers')}
            {$paginator->get_sql_limit()}
        ");
        while($row = $broadcast_subscribers_result->fetch_object()) {
            $broadcast_subscribers[] = $row;
        }

        /* Export handler */
        process_export_json($broadcast_subscribers, ['broadcast_subscriber_id', 'user_id', 'email', 'name', 'status', 'source', 'language', 'ip', 'continent_code', 'country_code', 'city_name', 'device_type', 'browser_language', 'browser_name', 'os_name', 'unsubscribed_datetime', 'last_datetime', 'datetime'], l('admin_broadcast_subscribers.title'));
        process_export_csv($broadcast_subscribers, ['broadcast_subscriber_id', 'user_id', 'email', 'name', 'status', 'source', 'language', 'ip', 'continent_code', 'country_code', 'city_name', 'device_type', 'browser_language', 'browser_name', 'os_name', 'unsubscribed_datetime', 'last_datetime', 'datetime'], l('admin_broadcast_subscribers.title'));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'broadcast_subscribers' => $broadcast_subscribers,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('admin/broadcast-subscribers/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function update_status() {

        if(empty($_POST)) {
            throw_404();
        }

        $broadcast_subscriber_id = (int) $_POST['broadcast_subscriber_id'];

        if(!isset($_POST['status']) || !in_array((int) $_POST['status'], [1, 2])) {
            throw_404();
        }

        $_POST['status'] = (int) $_POST['status'];

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$broadcast_subscriber = db()->where('broadcast_subscriber_id', $broadcast_subscriber_id)->getOne('broadcast_subscribers')) {
            redirect('admin/broadcast-subscribers');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Update the subscriber status */
            $old_status = $broadcast_subscriber->status;
            $datetime = get_date();

            if($_POST['status'] == 1 && $broadcast_subscriber->user_id && db()->where('user_id', $broadcast_subscriber->user_id)->where('status', 0)->has('users')) {
                Alerts::add_error(l('admin_broadcast_subscribers.error_message.resend_confirmation_account_pending'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $update_data = [
                    'status' => $_POST['status'],
                    'email_activation_code' => null,
                    'last_datetime' => $datetime,
                ];

                if($_POST['status'] == 1) {
                    $update_data['unsubscribed_datetime'] = null;
                } else {
                    $update_data['unsubscribed_datetime'] = $datetime;
                }

                if(!$broadcast_subscriber->unsubscribe_code) {
                    $update_data['unsubscribe_code'] = md5(uniqid('', true) . random_bytes(16));
                }

                db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->update('broadcast_subscribers', $update_data);

                /* Sync linked user */
                if($broadcast_subscriber->user_id) {
                    db()->where('user_id', $broadcast_subscriber->user_id)->update('users', [
                        'is_broadcast_subscribed' => $_POST['status'] == 1 ? 1 : 0,
                    ]);
                }

                $broadcast_subscriber->status = $_POST['status'];
                $broadcast_subscriber->email_activation_code = null;
                $broadcast_subscriber->unsubscribed_datetime = $_POST['status'] == 1 ? null : $datetime;
                $broadcast_subscriber->last_datetime = $datetime;

                /* Send webhook notification if needed */
                if($old_status != 1 && $_POST['status'] == 1 && settings()->webhooks->broadcast_subscriber_new) {
                    fire_and_forget('post', settings()->webhooks->broadcast_subscriber_new, [
                        'broadcast_subscriber_id' => $broadcast_subscriber->broadcast_subscriber_id,
                        'user_id' => $broadcast_subscriber->user_id,
                        'email' => $broadcast_subscriber->email,
                        'name' => $broadcast_subscriber->name,
                        'source' => $broadcast_subscriber->source,
                        'status' => $broadcast_subscriber->status,
                        'language' => $broadcast_subscriber->language,
                        'ip' => $broadcast_subscriber->ip,
                        'country_code' => $broadcast_subscriber->country_code,
                        'city_name' => $broadcast_subscriber->city_name,
                        'unsubscribed_datetime' => $broadcast_subscriber->unsubscribed_datetime,
                        'last_datetime' => $broadcast_subscriber->last_datetime,
                        'datetime' => $broadcast_subscriber->datetime,
                    ], signature: true);
                }

                /* Send webhook notification if needed */
                if($old_status == 1 && $_POST['status'] == 2 && settings()->webhooks->broadcast_subscriber_unsubscribe) {
                    fire_and_forget('post', settings()->webhooks->broadcast_subscriber_unsubscribe, [
                        'broadcast_subscriber_id' => $broadcast_subscriber->broadcast_subscriber_id,
                        'user_id' => $broadcast_subscriber->user_id,
                        'email' => $broadcast_subscriber->email,
                        'name' => $broadcast_subscriber->name,
                        'source' => $broadcast_subscriber->source,
                        'status' => 2,
                        'language' => $broadcast_subscriber->language,
                        'ip' => $broadcast_subscriber->ip,
                        'country_code' => $broadcast_subscriber->country_code,
                        'city_name' => $broadcast_subscriber->city_name,
                        'unsubscribed_datetime' => $datetime,
                        'last_datetime' => $datetime,
                        'datetime' => $broadcast_subscriber->datetime,
                    ], signature: true);
                }

                Alerts::add_success(l('global.success_message.update2'));
            }

        }

        redirect('admin/broadcast-subscribers');
    }

    public function resend_confirmation() {

        if(empty($_POST)) {
            throw_404();
        }

        $broadcast_subscriber_id = (int) $_POST['broadcast_subscriber_id'];

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$broadcast_subscriber = db()->where('broadcast_subscriber_id', $broadcast_subscriber_id)->getOne('broadcast_subscribers')) {
            redirect('admin/broadcast-subscribers');
        }

        if($broadcast_subscriber->status != 0) {
            Alerts::add_error(l('admin_broadcast_subscribers.error_message.resend_confirmation_status'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Do not bypass account confirmation */
            if($broadcast_subscriber->user_id && db()->where('user_id', $broadcast_subscriber->user_id)->where('status', 0)->has('users')) {
                Alerts::add_error(l('admin_broadcast_subscribers.error_message.resend_confirmation_account_pending'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $email_activation_code = md5(uniqid('', true) . random_bytes(16));

                /* Refresh activation code */
                $update_data = [
                    'email_activation_code' => $email_activation_code,
                    'last_datetime' => get_date(),
                ];

                if(!$broadcast_subscriber->unsubscribe_code) {
                    $update_data['unsubscribe_code'] = md5(uniqid('', true) . random_bytes(16));
                }

                db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->update('broadcast_subscribers', $update_data);

                /* Send confirmation email */
                $email_template = get_email_template(
                    [],
                    l('global.emails.broadcast_subscriber_activation.subject'),
                    [
                        '{{NAME}}' => $broadcast_subscriber->name,
                        '{{ACTIVATION_LINK}}' => url('broadcast-subscribe/confirm?broadcast_subscriber_id=' . $broadcast_subscriber->broadcast_subscriber_id . '&email=' . md5($broadcast_subscriber->email) . '&email_activation_code=' . $email_activation_code),
                    ],
                    l('global.emails.broadcast_subscriber_activation.body')
                );

                send_mail($broadcast_subscriber->email, $email_template->subject, $email_template->body, ['language' => $broadcast_subscriber->language]);

                Alerts::add_success(l('admin_broadcast_subscribers.success_message.resend_confirmation'));
            }

        }

        redirect('admin/broadcast-subscribers');
    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/broadcast-subscribers');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/broadcast-subscribers');
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
                case 'subscribe':

                    foreach($_POST['selected'] as $broadcast_subscriber_id) {
                        if($broadcast_subscriber = db()->where('broadcast_subscriber_id', $broadcast_subscriber_id)->getOne('broadcast_subscribers')) {
                            if($broadcast_subscriber->user_id && db()->where('user_id', $broadcast_subscriber->user_id)->where('status', 0)->has('users')) {
                                continue;
                            }

                            $old_status = $broadcast_subscriber->status;
                            $datetime = get_date();

                            /* Update subscriber status */
                            $update_data = [
                                'status' => 1,
                                'email_activation_code' => null,
                                'unsubscribed_datetime' => null,
                                'last_datetime' => $datetime,
                            ];

                            if(!$broadcast_subscriber->unsubscribe_code) {
                                $update_data['unsubscribe_code'] = md5(uniqid('', true) . random_bytes(16));
                            }

                            db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->update('broadcast_subscribers', $update_data);

                            /* Sync linked user */
                            if($broadcast_subscriber->user_id) {
                                db()->where('user_id', $broadcast_subscriber->user_id)->update('users', [
                                    'is_broadcast_subscribed' => 1,
                                ]);
                            }

                            /* Send webhook notification if needed */
                            if($old_status != 1 && settings()->webhooks->broadcast_subscriber_new) {
                                fire_and_forget('post', settings()->webhooks->broadcast_subscriber_new, [
                                    'broadcast_subscriber_id' => $broadcast_subscriber->broadcast_subscriber_id,
                                    'user_id' => $broadcast_subscriber->user_id,
                                    'email' => $broadcast_subscriber->email,
                                    'name' => $broadcast_subscriber->name,
                                    'source' => $broadcast_subscriber->source,
                                    'status' => 1,
                                    'language' => $broadcast_subscriber->language,
                                    'ip' => $broadcast_subscriber->ip,
                                    'country_code' => $broadcast_subscriber->country_code,
                                    'city_name' => $broadcast_subscriber->city_name,
                                    'unsubscribed_datetime' => null,
                                    'last_datetime' => $datetime,
                                    'datetime' => $broadcast_subscriber->datetime,
                                ], signature: true);
                            }
                        }
                    }

                    break;

                case 'unsubscribe':

                    foreach($_POST['selected'] as $broadcast_subscriber_id) {
                        if($broadcast_subscriber = db()->where('broadcast_subscriber_id', $broadcast_subscriber_id)->getOne('broadcast_subscribers')) {
                            $old_status = $broadcast_subscriber->status;
                            $datetime = get_date();

                            /* Update subscriber status */
                            $update_data = [
                                'status' => 2,
                                'email_activation_code' => null,
                                'unsubscribed_datetime' => $datetime,
                                'last_datetime' => $datetime,
                            ];

                            if(!$broadcast_subscriber->unsubscribe_code) {
                                $update_data['unsubscribe_code'] = md5(uniqid('', true) . random_bytes(16));
                            }

                            db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->update('broadcast_subscribers', $update_data);

                            /* Sync linked user */
                            if($broadcast_subscriber->user_id) {
                                db()->where('user_id', $broadcast_subscriber->user_id)->update('users', [
                                    'is_broadcast_subscribed' => 0,
                                ]);
                            }

                            /* Send webhook notification if needed */
                            if($old_status == 1 && settings()->webhooks->broadcast_subscriber_unsubscribe) {
                                fire_and_forget('post', settings()->webhooks->broadcast_subscriber_unsubscribe, [
                                    'broadcast_subscriber_id' => $broadcast_subscriber->broadcast_subscriber_id,
                                    'user_id' => $broadcast_subscriber->user_id,
                                    'email' => $broadcast_subscriber->email,
                                    'name' => $broadcast_subscriber->name,
                                    'source' => $broadcast_subscriber->source,
                                    'status' => 2,
                                    'language' => $broadcast_subscriber->language,
                                    'ip' => $broadcast_subscriber->ip,
                                    'country_code' => $broadcast_subscriber->country_code,
                                    'city_name' => $broadcast_subscriber->city_name,
                                    'unsubscribed_datetime' => $datetime,
                                    'last_datetime' => $datetime,
                                    'datetime' => $broadcast_subscriber->datetime,
                                ], signature: true);
                            }
                        }
                    }

                    break;

                case 'resend_confirmation':

                    foreach($_POST['selected'] as $broadcast_subscriber_id) {
                        if($broadcast_subscriber = db()->where('broadcast_subscriber_id', $broadcast_subscriber_id)->where('status', 0)->getOne('broadcast_subscribers')) {
                            if($broadcast_subscriber->user_id && db()->where('user_id', $broadcast_subscriber->user_id)->where('status', 0)->has('users')) {
                                continue;
                            }

                            $email_activation_code = md5(uniqid('', true) . random_bytes(16));

                            /* Refresh activation code */
                            $update_data = [
                                'email_activation_code' => $email_activation_code,
                                'last_datetime' => get_date(),
                            ];

                            if(!$broadcast_subscriber->unsubscribe_code) {
                                $update_data['unsubscribe_code'] = md5(uniqid('', true) . random_bytes(16));
                            }

                            db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->update('broadcast_subscribers', $update_data);

                            /* Send confirmation email */
                            $email_template = get_email_template(
                                [],
                                l('global.emails.broadcast_subscriber_activation.subject'),
                                [
                                    '{{NAME}}' => $broadcast_subscriber->name,
                                    '{{ACTIVATION_LINK}}' => url('broadcast-subscribe/confirm?broadcast_subscriber_id=' . $broadcast_subscriber->broadcast_subscriber_id . '&email=' . md5($broadcast_subscriber->email) . '&email_activation_code=' . $email_activation_code),
                                ],
                                l('global.emails.broadcast_subscriber_activation.body')
                            );

                            send_mail($broadcast_subscriber->email, $email_template->subject, $email_template->body, ['language' => $broadcast_subscriber->language]);
                        }
                    }

                    break;

                case 'delete':

                    foreach($_POST['selected'] as $broadcast_subscriber_id) {
                        if($broadcast_subscriber = db()->where('broadcast_subscriber_id', $broadcast_subscriber_id)->getOne('broadcast_subscribers')) {
                            $datetime = get_date();

                            /* Send webhook notification if needed */
                            if(settings()->webhooks->broadcast_subscriber_delete) {
                                fire_and_forget('post', settings()->webhooks->broadcast_subscriber_delete, [
                                    'broadcast_subscriber_id' => $broadcast_subscriber->broadcast_subscriber_id,
                                    'user_id' => $broadcast_subscriber->user_id,
                                    'email' => $broadcast_subscriber->email,
                                    'name' => $broadcast_subscriber->name,
                                    'source' => $broadcast_subscriber->source,
                                    'status' => $broadcast_subscriber->status,
                                    'language' => $broadcast_subscriber->language,
                                    'ip' => $broadcast_subscriber->ip,
                                    'country_code' => $broadcast_subscriber->country_code,
                                    'city_name' => $broadcast_subscriber->city_name,
                                    'unsubscribed_datetime' => $broadcast_subscriber->unsubscribed_datetime,
                                    'last_datetime' => $broadcast_subscriber->last_datetime,
                                    'datetime' => $broadcast_subscriber->datetime,
                                    'deleted_datetime' => $datetime,
                                ], signature: true);
                            }

                            /* Sync linked user */
                            if($broadcast_subscriber->user_id) {
                                db()->where('user_id', $broadcast_subscriber->user_id)->update('users', [
                                    'is_broadcast_subscribed' => 0,
                                ]);
                            }

                            db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->delete('broadcast_subscribers');
                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            if($_POST['type'] == 'delete') {
                Alerts::add_success(l('bulk_delete_modal.success_message'));
            } else {
                Alerts::add_success(l('global.success_message.update2'));
            }

        }

        redirect('admin/broadcast-subscribers');
    }

    public function delete() {

        $broadcast_subscriber_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$broadcast_subscriber = db()->where('broadcast_subscriber_id', $broadcast_subscriber_id)->getOne('broadcast_subscribers')) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the subscriber */
            $datetime = get_date();

            /* Send webhook notification if needed */
            if(settings()->webhooks->broadcast_subscriber_delete) {
                fire_and_forget('post', settings()->webhooks->broadcast_subscriber_delete, [
                    'broadcast_subscriber_id' => $broadcast_subscriber->broadcast_subscriber_id,
                    'user_id' => $broadcast_subscriber->user_id,
                    'email' => $broadcast_subscriber->email,
                    'name' => $broadcast_subscriber->name,
                    'source' => $broadcast_subscriber->source,
                    'status' => $broadcast_subscriber->status,
                    'language' => $broadcast_subscriber->language,
                    'ip' => $broadcast_subscriber->ip,
                    'country_code' => $broadcast_subscriber->country_code,
                    'city_name' => $broadcast_subscriber->city_name,
                    'unsubscribed_datetime' => $broadcast_subscriber->unsubscribed_datetime,
                    'last_datetime' => $broadcast_subscriber->last_datetime,
                    'datetime' => $broadcast_subscriber->datetime,
                    'deleted_datetime' => $datetime,
                ], signature: true);
            }

            /* Sync linked user */
            if($broadcast_subscriber->user_id) {
                db()->where('user_id', $broadcast_subscriber->user_id)->update('users', [
                    'is_broadcast_subscribed' => 0,
                ]);
            }

            db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->delete('broadcast_subscribers');

            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $broadcast_subscriber->email . '</strong>'));

        }

        redirect('admin/broadcast-subscribers');
    }

}
