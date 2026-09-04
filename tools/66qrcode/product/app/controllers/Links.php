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

class Links extends Controller {

    public function index() {
        \Altum\Authentication::guard();

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id', 'domain_id', 'links_ids'], ['url', 'location_url', 'name'], ['link_id', 'last_datetime', 'datetime', 'url', 'location_url', 'pageviews', 'name'], [], ['links_ids' => 'json_contains'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->links_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('links?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the links */
        $links = [];
        $links_result = database()->query("
            SELECT `links`.*, `qr_codes`.`qr_code_id`
            FROM `links`
            LEFT JOIN `qr_codes` ON `links`.`link_id` = `qr_codes`.`link_id` AND `qr_codes`.`type` = 'file'
            WHERE
                `links`.`user_id` = {$this->user->user_id}
                {$filters->get_sql_where('links')}
                {$filters->get_sql_order_by('links')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $links_result->fetch_object()) {
            $row->full_url = (new \Altum\Models\Link())->get_link_full_url($row, $this->user, $domains);
            $row->settings = json_decode($row->settings ?? '');
            $links[] = $row;
        }

        /* Export handler */
        process_export_csv_new($links, ['link_id', 'user_id', 'domain_id', 'project_id', 'links_ids', 'url', 'location_url', 'type', 'file_name', 'file_size', 'file_mime_type', 'name', 'settings', 'password', 'pageviews', 'is_enabled', 'datetime'], ['settings'], sprintf(l('links.title')));
        process_export_json($links, ['link_id', 'user_id', 'domain_id', 'project_id', 'links_ids', 'url', 'location_url', 'type', 'file_name', 'file_size', 'file_mime_type', 'name', 'settings', 'password', 'pageviews', 'is_enabled', 'datetime'], sprintf(l('links.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Existing projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get statistics */
        if(count($links) && !$filters->has_applied_filters) {
            $start_date_query = (new \DateTime())->modify('-' . (settings()->main->chart_days ?? 30) . ' day')->format('Y-m-d');            $end_date_query = (new \DateTime())->modify('+1 day')->format('Y-m-d');

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
                    AND (`datetime` >= '{$start_date_query}' AND `datetime` < '{$end_date_query}')
                GROUP BY
                    `formatted_date`
                ORDER BY
                    `formatted_date`
            ";

            $links_chart = \Altum\Cache::cache_function_result('statistics?user_id=' . $this->user->user_id, null, function() use ($statistics_result_query) {
                $links_chart = [];

                $statistics_result = database()->query($statistics_result_query);

                /* Generate the raw chart data and save logs for later usage */
                while($row = $statistics_result->fetch_object()) {
                    $label = \Altum\Date::get($row->formatted_date, 5, \Altum\Date::$default_timezone);

                    $links_chart[$label] = [
                        'pageviews' => $row->pageviews,
                        'visitors' => $row->visitors
                    ];
                }

                return $links_chart;
            }, 60 * 60 * (settings()->main->chart_cache ?? 12));

            $links_chart = get_chart_data($links_chart);
        }

        /* Prepare the view */
        $data = [
            'links_chart' => $links_chart ?? null,
            'projects' => $projects,
            'links' => $links,
            'total_links' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
            'domains' => $domains,
        ];

        $view = new \Altum\View('links/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }

    public function duplicate() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.links')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('links');
        }

        if(empty($_POST)) {
            throw_404();
        }

        $link_id = (int) $_POST['link_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('links');
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('links', 'COUNT(*)') ?? 0;

        if($this->user->plan_settings->links_limit != -1 && $total_rows >= $this->user->plan_settings->links_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('links');
        }

        /* Verify the main resource */
        if(!$link = db()->where('link_id', $link_id)->where('user_id', $this->user->user_id)->getOne('links')) {
            redirect('links');
        }

        if($link->type == 'file') {
            redirect('links');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Email reports */
            $email_reports_is_enabled = settings()->links->email_reports_is_enabled && $this->user->plan_settings->email_reports_is_enabled ? $link->email_reports_is_enabled : 0;
            $email_reports_last_datetime = $email_reports_is_enabled ? get_date() : null;

            /* Generate a random alias */
            $is_existing_link = true;
            $url = null;

            /* Generate random url if not specified */
            while($is_existing_link) {
                $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));

                $domain_id_where = $link->domain_id ? "AND `domain_id` = {$link->domain_id}" : "AND `domain_id` IS NULL";
                $is_existing_link = database()->query("SELECT `link_id` FROM `links` WHERE `url` = '{$url}' {$domain_id_where}")->num_rows;
            }

            /* Insert to database */
            $new_link_id = db()->insert('links', [
                'user_id' => $this->user->user_id,
                'domain_id' => $link->domain_id,
                'project_id' => $link->project_id,
                'pixels_ids' => $link->pixels_ids,
                'email_reports_is_enabled' => $email_reports_is_enabled,
                'email_reports_last_datetime' => $email_reports_last_datetime,
                'url' => $url,
                'location_url' => $link->location_url,
                'name' => string_truncate($link->name . ' - ' . l('global.duplicated'), 64, null),
                'settings' => $link->settings,
                'is_enabled' => $link->is_enabled,
                'datetime' => get_date(),
            ]);

            /* Send webhook notification if needed */
            if(settings()->webhooks->link_new) {
                $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

                fire_and_forget('post', settings()->webhooks->link_new, [
                    'user_id' => $this->user->user_id,
                    'link_id' => $new_link_id,
                    'domain_id' => $link->domain_id,
                    'url' => $url,
                    'location_url' => $link->location_url,
                    'name' => string_truncate($link->name . ' - ' . l('global.duplicated'), 64, null),
                    'full_url' => $link->domain_id && isset($domains[$link->domain_id]) ? $domains[$link->domain_id]->url . $url : SITE_URL . $url,
                    'datetime' => get_date(),
                ], signature: true);
            }

            /* Clear the cache */
            cache()->deleteItem('links?user_id=' . $this->user->user_id);
            cache()->deleteItem('links_total?user_id=' . $this->user->user_id);
            cache()->deleteItem('links_dashboard?user_id=' . $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($url) . '</strong>'));

            redirect('link-update/' . $new_link_id);

        }

        redirect('links');
    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('links');
        }

        if(!isset($_POST['type'])) {
            redirect('links');
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
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.links')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('links');
                    }

                    /* Delete all valid links */
                    (new \Altum\Models\Link())->bulk_delete($_POST['selected'], $this->user->user_id);

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('links');
    }

    public function reset() {
        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.links')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('links');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $link_id = (int) $_POST['link_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('links');
        }

        /* Make sure the link id is created by the logged in user */
        if(!$link = db()->where('link_id', $link_id)->where('user_id', $this->user->user_id)->getOne('links', ['link_id'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Reset data */
            db()->where('link_id', $link_id)->update('links', [
                'pageviews' => 0,
            ]);

            /* Remove data */
            db()->where('link_id', $link_id)->delete('statistics');

            /* Clear the cache */
            cache()->deleteItem('links?user_id=' . $this->user->user_id);
            cache()->deleteItem('links_total?user_id=' . $this->user->user_id);
            cache()->deleteItem('links_dashboard?user_id=' . $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.update2'));

            redirect('links');

        }

        redirect('links');
    }

    public function delete() {
        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.links')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('links');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $link_id = (int) $_POST['link_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('links');
        }

        /* Make sure the link id is created by the logged in user */
        if(!$link = db()->where('link_id', $link_id)->where('user_id', $this->user->user_id)->getOne('links', ['link_id', 'url'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new \Altum\Models\Link())->delete($link->link_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $link->url . '</strong>'));

            redirect('links');

        }

        redirect('links');
    }

}
