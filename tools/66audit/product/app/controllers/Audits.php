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

class Audits extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', 'website_id', 'domain_id',], ['url', 'host', 'title'], ['audit_id', 'last_datetime', 'datetime', 'last_refresh_datetime', 'next_refresh_datetime', 'url', 'host', 'score', 'total_issues', 'title'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_refresh_datetime', 'next_refresh_datetime']));
        $filters->set_default_order_by($this->user->preferences->audits_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `audits` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('audits?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Generate stats */
        $audits_stats = [
            'total_tests' => 0,
            'major_issues' => 0,
            'moderate_issues' => 0,
            'minor_issues' => 0,
            'passed_tests' => 0,
        ];

        /* Get the audits list for the user */
        $audits = [];
        $audits_result = database()->query("SELECT * FROM `audits` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $audits_result->fetch_object()) {
            foreach(['data', 'issues', 'settings'] as $key) $row->{$key} = json_decode($row->{$key} ?? '');

            $audits_stats['total_tests'] += $row->total_tests;
            $audits_stats['major_issues'] += $row->major_issues;
            $audits_stats['moderate_issues'] += $row->moderate_issues;
            $audits_stats['minor_issues'] += $row->minor_issues;
            $audits_stats['passed_tests'] += $row->passed_tests;

            $audits[] = $row;
        }

        /* Export handler */
        process_export_csv_new($audits, ['audit_id', 'user_id', 'domain_id', 'uploader_id', 'host', 'url', 'resolved_url', 'ttfb', 'response_time', 'average_download_speed', 'page_size', 'http_requests', 'is_https', 'is_ssl_valid', 'http_protocol', 'title', 'meta_description', 'meta_keywords', 'ai_summary', 'score', 'total_issues', 'major_issues', 'moderate_issues', 'minor_issues', 'is_queued', 'refresh_error', 'is_external_redirected', 'total_redirects', 'total_refreshes', 'next_refresh_datetime', 'last_refresh_datetime', 'expiration_datetime', 'last_datetime', 'datetime'], [], sprintf(l('audits.title')));
        process_export_json($audits, ['audit_id', 'user_id', 'domain_id', 'uploader_id', 'host', 'url', 'resolved_url', 'ttfb', 'response_time', 'average_download_speed', 'page_size', 'http_requests', 'is_https', 'is_ssl_valid', 'http_protocol', 'title', 'meta_description', 'meta_keywords', 'ai_summary', 'data', 'issues', 'settings', 'notifications', 'score', 'total_issues', 'major_issues', 'moderate_issues', 'minor_issues', 'is_queued', 'refresh_error', 'is_external_redirected', 'total_redirects', 'total_refreshes', 'next_refresh_datetime', 'last_refresh_datetime', 'expiration_datetime', 'last_datetime', 'datetime'], sprintf(l('audits.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Available */
        $audits_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`audit_audits_current_month`');

        /* Get statistics */
        if(count($audits) && !$filters->has_applied_filters) {
            $start_date_query = (new \DateTime())->modify('-' . (settings()->main->chart_days ?? 30) . ' day')->format('Y-m-d');
            $end_date_query = (new \DateTime())->modify('+1 day')->format('Y-m-d');

            $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

            $audits_result_query = "
                SELECT
                    COUNT(*) AS `total`,
                    DATE_FORMAT({$convert_tz_sql}, '%Y-%m-%d') AS `formatted_date`
                FROM
                    `audits`
                WHERE   
                    `user_id` = {$this->user->user_id} 
                    AND ({$convert_tz_sql} BETWEEN '{$start_date_query}' AND '{$end_date_query}')
                GROUP BY
                    `formatted_date`
                ORDER BY
                    `formatted_date`
            ";

            $audits_chart = \Altum\Cache::cache_function_result('audits_chart?user_id=' . $this->user->user_id, null, function() use ($audits_result_query) {
                $audits_chart= [];

                $audits_result = database()->query($audits_result_query);

                /* Generate the raw chart data and save logs for later usage */
                while($row = $audits_result->fetch_object()) {
                    $label = \Altum\Date::get($row->formatted_date, 5, \Altum\Date::$default_timezone);
                    $audits_chart[$label]['total'] = $row->total;
                }

                return $audits_chart;
            }, 60 * 60 * (settings()->main->chart_cache ?? 12));

            $audits_chart = get_chart_data($audits_chart);
        }

        /* Prepare the view */
        $data = [
            'audits' => $audits,
            'audits_chart' => $audits_chart ?? null,
            'total_audits' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
            'audits_current_month' => $audits_current_month,
            'audits_stats' => $audits_stats,
        ];

        $view = new \Altum\View('audits/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('audits');
        }

        if(!isset($_POST['type'])) {
            redirect('audits');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            set_time_limit(0);

            session_write_close();

            $_POST['selected'] = is_array($_POST['selected']) ? array_unique(array_map('intval', $_POST['selected'])) : [];

            switch($_POST['type']) {
                case 'refresh':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.audits')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('audits');
                    }

                    /* Plan limit check */
                    $audits_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`audit_audits_current_month`');
                    $total_audits = count($_POST['selected']);

                    if($this->user->plan_settings->audits_per_month_limit != -1 && ($audits_current_month + $total_audits) >= $this->user->plan_settings->audits_per_month_limit) {
                        Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
                        redirect('audits');
                    }

                    /* Database query */
                    db()->where('user_id', $this->user->user_id)->where('audit_id', $_POST['selected'], 'IN')->update('audits', [
                        'is_queued' => 1,
                        'next_refresh_datetime' => get_date(),
                    ]);

                    /* Database query */
                    db()->where('user_id', $this->user->user_id)->update('users', [
                        'audit_audits_current_month' => db()->inc($total_audits),
                    ]);

                    /* Clear the cache */
                    cache()->deleteItem('audits_dashboard?user_id=' . $this->user->user_id);

                    session_start();

                    /* Set a nice success message */
                    Alerts::add_success(sprintf(l('audits_bulk_refresh_modal.success_message'), '<strong>' . $total_audits . '</strong>'));

                    break;

                case 'delete':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.audits')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('audits');
                    }

                    $websites_ids = [];

                    foreach($_POST['selected'] as $audit_id) {
                        if($audit = db()->where('audit_id', $audit_id)->getOne('audits', ['user_id', 'audit_id', 'website_id', 'url', 'uploader_id'])) {

                            if($audit->user_id != $this->user->user_id && $audit->uploader_id != md5(get_ip())) {
                                continue;
                            }

                            db()->where('audit_id', $audit_id)->delete('audits');

                            if(!in_array($audit->website_id, $websites_ids)) {
                                $websites_ids[] = $audit->website_id;
                            }

                        }
                    }

                    foreach($websites_ids as $website_id) {
                        (new \Altum\Models\Website())->refresh_stats($website_id);
                    }

                    /* Clear the cache */
                    cache()->deleteItem('audits_total?user_id=' . $this->user->user_id);
                    cache()->deleteItem('audits_dashboard?user_id=' . $this->user->user_id);

                    session_start();

                    /* Set a nice success message */
                    Alerts::add_success(l('bulk_delete_modal.success_message'));

                    break;
            }

        }

        redirect('audits');
    }

    public function delete() {

        $error_redirect = is_logged_in() ? 'audits' : 'seo';

        if(is_logged_in()) {
            \Altum\Authentication::guard();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.audits')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect($error_redirect);
        }

        if(empty($_POST)) {
            redirect($error_redirect);
        }

        $audit_id = (int) $_POST['audit_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }


        if(!$audit = db()->where('audit_id', $audit_id)->getOne('audits', ['user_id', 'audit_id', 'website_id', 'url', 'uploader_id'])) {
            redirect($error_redirect);
        }


        if($audit->user_id != $this->user->user_id && $audit->uploader_id != md5(get_ip())) {
            redirect($error_redirect);
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the resource */
            db()->where('audit_id', $audit->audit_id)->delete('audits');

            (new \Altum\Models\Website())->refresh_stats($audit->website_id);

            /* Clear the cache */
            cache()->deleteItem('audits_total?user_id=' . $this->user->user_id);
            cache()->deleteItem('audits_dashboard?user_id=' . $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . remove_url_protocol_from_url($audit->url) . '</strong>'));

            redirect($error_redirect);
        }

        redirect($error_redirect);
    }
}
