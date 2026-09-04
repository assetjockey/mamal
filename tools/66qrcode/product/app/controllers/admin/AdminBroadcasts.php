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

class AdminBroadcasts extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['broadcast_id', 'status', 'segment'], ['name', 'content'], ['broadcast_id', 'name', 'datetime', 'last_datetime', 'total_emails', 'sent_emails', 'views', 'clicks', 'last_sent_email_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_sent_email_datetime']));
        $filters->set_default_order_by('broadcast_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `broadcasts` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/broadcasts?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $broadcasts = [];
        $broadcasts_result = database()->query("
            SELECT
                *
            FROM
                `broadcasts`
            WHERE
                1 = 1
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $broadcasts_result->fetch_object()) {
            $row->content_text = input_clean($row->content);
            $broadcasts[] = $row;
        }

        /* Export handler */
        process_export_json($broadcasts, ['broadcast_id', 'name', 'subject', 'content', 'content_text', 'segment', 'users_ids', 'sent_users_ids', 'sent_emails', 'views', 'clicks', 'total_emails', 'status', 'last_sent_email_datetime', 'datetime', 'last_datetime']);
        process_export_csv($broadcasts, ['broadcast_id', 'name', 'subject', 'content_text', 'segment', 'users_ids', 'sent_users_ids', 'sent_emails', 'views', 'clicks', 'total_emails', 'status', 'last_sent_email_datetime', 'datetime', 'last_datetime']);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'broadcasts' => $broadcasts,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'filters' => $filters
        ];

        $view = new \Altum\View('admin/broadcasts/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function get_segment_count() {

        if($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw_404();
        }

        \Altum\Authentication::guard();

        $segment = isset($_GET['segment']) ? input_clean($_GET['segment']) : 'all';
        $is_system_email = (int) isset($_GET['is_system_email']);

        $table = $is_system_email ? 'users' : 'broadcast_subscribers';
        $id_column = $is_system_email ? 'user_id' : 'broadcast_subscriber_id';

        switch($segment) {
            case 'all':

                $query = db();

                if(!$is_system_email) {
                    $query->where('status', 1);
                }

                $count = $query->getValue($table, 'COUNT(*)');

                break;

            case 'custom':

                $ids = $is_system_email ? ($_GET['users_ids'] ?? '') : ($_GET['broadcast_subscribers_ids'] ?? '');
                $ids = is_array($ids) ? $ids : explode(',', $ids);
                $ids = array_filter(array_unique(array_map('intval', $ids)));

                if(count($ids)) {
                    $query = db();

                    if(!$is_system_email) {
                        $query->where('status', 1);
                    }

                    $count = $query->where($id_column, $ids, 'IN')->getValue($table, 'COUNT(*)');
                } else {
                    $count = 0;
                }

                break;

            case 'filter':

                $query = db();

                $has_filters = false;

                /* Newsletter subscriber filters */
                if(!$is_system_email) {

                    /* Only active subscribers */
                    $query->where('status', 1);

                    /* Subscriber type */
                    if(isset($_GET['filters_subscriber_type'])) {
                        $_GET['filters_subscriber_type'] = array_values(array_intersect($_GET['filters_subscriber_type'], ['registered', 'guest']));

                        if(count($_GET['filters_subscriber_type'])) {
                            $has_filters = true;

                            if(count($_GET['filters_subscriber_type']) == 1) {
                                if(in_array('registered', $_GET['filters_subscriber_type'])) {
                                    $query->where('user_id', null, 'IS NOT');
                                } else {
                                    $query->where('user_id', null, 'IS');
                                }
                            }
                        }
                    }

                    /* Subscription source */
                    if(isset($_GET['filters_broadcast_subscribers_source'])) {
                        $_GET['filters_broadcast_subscribers_source'] = array_values(array_intersect($_GET['filters_broadcast_subscribers_source'], ['index', 'register', 'account']));

                        if(count($_GET['filters_broadcast_subscribers_source'])) {
                            $has_filters = true;
                            $query->where('source', $_GET['filters_broadcast_subscribers_source'], 'IN');
                        }
                    }

                }

                /* System email filters */
                else {

                    /* Plans */
                    if(isset($_GET['filters_plans'])) {
                        $has_filters = true;
                        $query->where('plan_id', $_GET['filters_plans'], 'IN');
                    }

                    /* Status */
                    if(isset($_GET['filters_status'])) {
                        $has_filters = true;
                        $query->where('status', $_GET['filters_status'], 'IN');
                    }

                    /* Registration source */
                    if(isset($_GET['filters_source'])) {
                        $has_filters = true;
                        $query->where('source', $_GET['filters_source'], 'IN');
                    }

                }

                /* Cities */
                if(!empty($_GET['filters_cities'])) {
                    $_GET['filters_cities'] = is_array($_GET['filters_cities']) ? $_GET['filters_cities'] : explode(',', $_GET['filters_cities']);
                    $_GET['filters_cities'] = array_filter(array_unique($_GET['filters_cities']));

                    if(count($_GET['filters_cities'])) {
                        $_GET['filters_cities'] = array_map(function($city) {
                            return query_clean(trim($city));
                        }, $_GET['filters_cities']);

                        $has_filters = true;
                        $query->where('city_name', $_GET['filters_cities'], 'IN');
                    }
                }

                /* Countries */
                if(isset($_GET['filters_countries'])) {
                    $has_filters = true;
                    $query->where($is_system_email ? 'country' : 'country_code', $_GET['filters_countries'], 'IN');
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
                    $query->where('language', $_GET['filters_languages'], 'IN');
                }

                /* Browser languages */
                if(isset($_GET['filters_browser_languages'])) {
                    $_GET['filters_browser_languages'] = array_filter($_GET['filters_browser_languages'], function($locale) {
                        return array_key_exists($locale, get_locale_languages_array());
                    });

                    if(count($_GET['filters_browser_languages'])) {
                        $has_filters = true;
                        $query->where('browser_language', $_GET['filters_browser_languages'], 'IN');
                    }
                }

                /* Operating systems */
                if(isset($_GET['filters_operating_systems'])) {
                    $_GET['filters_operating_systems'] = array_filter($_GET['filters_operating_systems'], function($os_name) {
                        return in_array($os_name, ['iOS', 'Android', 'Windows', 'OS X', 'Linux', 'Ubuntu', 'Chrome OS']);
                    });

                    if(count($_GET['filters_operating_systems'])) {
                        $has_filters = true;
                        $query->where('os_name', $_GET['filters_operating_systems'], 'IN');
                    }
                }

                /* Browsers */
                if(isset($_GET['filters_browsers'])) {
                    $_GET['filters_browsers'] = array_filter($_GET['filters_browsers'], function($browser_name) {
                        return in_array($browser_name, ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Samsung Internet']);
                    });

                    if(count($_GET['filters_browsers'])) {
                        $has_filters = true;
                        $query->where('browser_name', $_GET['filters_browsers'], 'IN');
                    }
                }

                $count = $has_filters ? $query->getValue($table, 'COUNT(*)') : 0;

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

        $broadcast_id = (int) $_POST['broadcast_id'];

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$broadcast = db()->where('broadcast_id', $broadcast_id)->getOne('broadcasts')) {
            redirect('admin/broadcasts');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Insert to database */
            $broadcast_id = db()->insert('broadcasts', [
                'name' => string_truncate($broadcast->name . ' - ' . l('global.duplicated'), 64, null),
                'subject' => $broadcast->subject,
                'content' => json_decode($broadcast->content) ? $broadcast->content : '',
                'segment' => $broadcast->segment,
                'settings' => $broadcast->settings,
                'users_ids' => $broadcast->users_ids,
                'status' => 'draft',
                'datetime' => get_date(),
            ]);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($broadcast->name) . '</strong>'));

            /* Redirect */
            redirect('admin/broadcast-update/' . $broadcast_id);

        }

        redirect('admin/broadcasts');
    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/broadcasts');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/broadcasts');
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
                        db()->where('broadcast_id', $id)->delete('broadcasts');
                    }
                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/broadcasts');
    }

    public function delete() {

        $broadcast_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$broadcast = db()->where('broadcast_id', $broadcast_id)->getOne('broadcasts', ['broadcast_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the broadcast */
            db()->where('broadcast_id', $broadcast_id)->delete('broadcasts');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $broadcast->name . '</strong>'));

        }

        redirect('admin/broadcasts');
    }

}
