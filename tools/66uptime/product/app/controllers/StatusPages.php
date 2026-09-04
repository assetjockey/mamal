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

class StatusPages extends Controller {

    public function index() {

        if(!settings()->status_pages->status_pages_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user, false);

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id', 'domain_id', 'pixels_ids'], ['name'], ['status_page_id', 'pageviews', 'last_datetime', 'datetime', 'name'], [], ['pixels_ids' => 'json_contains'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->status_pages_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `status_pages` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('status-pages?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the status_pages */
        $status_pages = [];
        $status_pages_result = database()->query("
            SELECT
                *
            FROM
                `status_pages`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

            {$paginator->get_sql_limit()}
        ");
        while($row = $status_pages_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');

            /* Generate the status page full URL base */
            $row->full_url = (new \Altum\Models\StatusPage())->get_status_page_full_url($row, $this->user, $domains);

            $status_pages[] = $row;
        }

        /* Export handler */
        process_export_csv_new($status_pages, ['status_page_id', 'domain_id', 'project_id', 'url', 'name', 'description', 'settings', 'pageviews', 'is_se_visible', 'is_removed_branding', 'is_enabled', 'datetime'], ['settings'], sprintf(l('status_pages.title')));
        process_export_json($status_pages, ['status_page_id', 'domain_id', 'project_id', 'url', 'name', 'description', 'settings', 'pageviews', 'is_se_visible', 'is_removed_branding', 'is_enabled', 'datetime'], sprintf(l('status_pages.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get statistics */
        if(count($status_pages) && !$filters->has_applied_filters) {
            $start_date_query = (new \DateTime())->modify('-' . (settings()->main->chart_days ?? 30) . ' day')->format('Y-m-d');
            $end_date_query = (new \DateTime())->modify('+1 day')->format('Y-m-d');

            $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

            $statistics_result_query = "
                SELECT
                    COUNT(`id`) AS `pageviews`,
                    SUM(`is_unique`) AS `visitors`,
                    DATE_FORMAT({$convert_tz_sql}, '%Y-%m-%d') AS `formatted_date`
                FROM
                    `statistics`
                WHERE   
                    `user_id` = {$this->user->user_id} 
                    AND ({$convert_tz_sql} BETWEEN '{$start_date_query}' AND '{$end_date_query}')
                GROUP BY
                    `formatted_date`
                ORDER BY
                    `formatted_date`
            ";

            $status_pages_chart = \Altum\Cache::cache_function_result('statistics?user_id=' . $this->user->user_id, null, function() use ($statistics_result_query) {
                $status_pages_chart = [];

                $statistics_result = database()->query($statistics_result_query);

                /* Generate the raw chart data and save logs for later usage */
                while($row = $statistics_result->fetch_object()) {
                    $label = \Altum\Date::get($row->formatted_date, 5, \Altum\Date::$default_timezone);

                    $status_pages_chart[$label] = [
                        'pageviews' => $row->pageviews,
                        'visitors' => $row->visitors
                    ];
                }

                return $status_pages_chart;
            }, 60 * 60 * (settings()->main->chart_cache ?? 12));

            $status_pages_chart = get_chart_data($status_pages_chart);
        }

        /* Prepare the view */
        $data = [
            'status_pages_chart' => $status_pages_chart ?? null,
            'projects' => $projects,
            'domains' => $domains,
            'status_pages' => $status_pages,
            'total_status_pages' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('status-pages/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function duplicate() {
        \Altum\Authentication::guard();

        if(!settings()->status_pages->status_pages_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.status_pages')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('status-pages');
        }

        if (empty($_POST)) {
            throw_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('status_pages', 'COUNT(*)') ?? 0;
        if($this->user->plan_settings->status_pages_limit != -1 && $total_rows >= $this->user->plan_settings->status_pages_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('status-pages');
        }

        $status_page_id = (int) $_POST['status_page_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');
        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('status-pages');
        }

        /* Verify the main resource */
        if(!$status_page = db()->where('status_page_id', $status_page_id)->where('user_id', $this->user->user_id)->getOne('status_pages')) {
            redirect('status-pages');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Duplicate the files */
            $logo = \Altum\Uploads::copy_uploaded_file($status_page->logo, \Altum\Uploads::get_path('status_pages_logos'), \Altum\Uploads::get_path('status_pages_logos'));
            $favicon = \Altum\Uploads::copy_uploaded_file($status_page->favicon, \Altum\Uploads::get_path('status_pages_favicons'), \Altum\Uploads::get_path('status_pages_favicons'));
            $opengraph = \Altum\Uploads::copy_uploaded_file($status_page->opengraph, \Altum\Uploads::get_path('status_pages_opengraph'), \Altum\Uploads::get_path('status_pages_opengraph'));

            /* Generate random url if not specified */
            $url = mb_strtolower(string_generate(settings()->status_pages->random_url_length ?? 7));
            while (db()->where('url', $url)->getValue('status_pages', 'status_page_id')) {
                $url = mb_strtolower(string_generate(settings()->status_pages->random_url_length ?? 7));
            }

            /* Insert to database */
            $status_page_id = db()->insert('status_pages', [
                'user_id' => $this->user->user_id,
                'domain_id' => null,
                'project_id' => $status_page->project_id,
                'monitors_ids' => $status_page->monitors_ids,
                'heartbeats_ids' => $status_page->heartbeats_ids,
                'name' => string_truncate($status_page->name . ' - ' . l('global.duplicated'), 64, null),
                'url' => $url,
                'description' => $status_page->description,
                'settings' => $status_page->settings,
                'timezone' => $status_page->timezone,
                'password' => $status_page->password,
                'is_se_visible' => $status_page->is_se_visible,
                'is_removed_branding' => $status_page->is_removed_branding,
                'socials' => $status_page->socials,
                'custom_css' => $status_page->custom_css,
                'custom_js' => $status_page->custom_js,
                'theme' => $status_page->theme,
                'logo' => $logo,
                'favicon' => $favicon,
                'opengraph' => $opengraph,
                'is_enabled' => $status_page->is_enabled,
                'datetime' => get_date(),
            ]);

            /* Clear the cache */
            cache()->deleteItem('status_pages_dashboard?user_id=' . $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($status_page->name) . '</strong>'));

            /* Redirect */
            redirect('status-page-update/' . $status_page_id);

        }

        redirect('status-pages');
    }

    public function bulk() {

        if(!settings()->status_pages->status_pages_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('status-pages');
        }

        if(!isset($_POST['type'])) {
            redirect('status-pages');
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
                case 'delete':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.status_pages')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('status-pages');
                    }

                    foreach($_POST['selected'] as $status_page_id) {
                        if($status_page = db()->where('status_page_id', $status_page_id)->where('user_id', $this->user->user_id)->getOne('status_pages', ['status_page_id'])) {
                            (new \Altum\Models\StatusPage())->delete($status_page_id);
                        }
                    }

                    break;
            }

            /* Clear the cache */
            cache()->deleteItem('status_pages?user_id=' . $this->user->user_id);

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('status-pages');
    }

    public function reset() {

        if(!settings()->status_pages->status_pages_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.status_pages')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('status-pages');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $status_page_id = (int) $_POST['status_page_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('status-pages');
        }

        /* Make sure the link id is created by the logged in user */
        if(!$status_page = db()->where('status_page_id', $status_page_id)->where('user_id', $this->user->user_id)->getOne('status_pages', ['status_page_id'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Reset data */
            db()->where('status_page_id', $status_page_id)->update('status_pages', [
                'pageviews' => 0,
            ]);

            /* Remove data */
            db()->where('status_page_id', $status_page_id)->delete('statistics');

            /* Clear the cache */
            cache()->deleteItemsByTag('status_page_id=' . $status_page_id);
            cache()->deleteItem('status_pages_dashboard?user_id=' . $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.update2'));

            redirect('status-pages');

        }

        redirect('status-pages');
    }

    public function delete() {

        if(!settings()->status_pages->status_pages_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.status_pages')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('status-pages');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $status_page_id = (int) $_POST['status_page_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('status-pages');
        }

        /* Make sure the status_page id is created by the logged in user */
        if(!$status_page = db()->where('status_page_id', $status_page_id)->where('user_id', $this->user->user_id)->getOne('status_pages', ['status_page_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new \Altum\Models\StatusPage())->delete($status_page->status_page_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $status_page->name . '</strong>'));

            redirect('status-pages');

        }

        redirect('status-pages');
    }

}
