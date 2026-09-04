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
use Altum\Models\SessionsReplays;

defined('ALTUMCODE') || die();

class Websites extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Get available custom domains */
		$domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        /* Prepare the filtering system */
        $allowed_order_by = ['website_id', 'last_datetime', 'datetime', 'name', 'host', 'current_month_sessions_events', 'last_24_hours_pageviews', 'last_7_days_pageviews'];
        $default_order_by = in_array($this->user->preferences->websites_default_order_by, $allowed_order_by) ? $this->user->preferences->websites_default_order_by : 'website_id';
        $data_period = isset($this->user->preferences->websites_data_period) && in_array($this->user->preferences->websites_data_period, ['current_month', 'last_7_days', 'last_24_hours']) ? $this->user->preferences->websites_data_period : 'current_month';

        $filters = (new \Altum\Filters(['is_enabled', 'tracking_type', 'domain_id'], ['name', 'host'], $allowed_order_by, allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `websites` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('websites?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the websites list for the user */
        $websites = [];
        $websites_result = database()->query("
            SELECT 
                `websites`.*, 
                COUNT(DISTINCT `websites_heatmaps`.`heatmap_id`) AS `heatmaps`, 
                COUNT(DISTINCT `websites_goals`.`goal_id`) AS `goals`
            FROM 
                 `websites`
            LEFT JOIN 
                `websites_heatmaps` ON `websites_heatmaps`.`website_id` = `websites`.`website_id` 
            LEFT JOIN 
                `websites_goals` ON `websites_goals`.`website_id` = `websites`.`website_id`
            WHERE 
                  `websites`.`user_id` = {$this->user->user_id}
                  {$filters->get_sql_where('websites')}
            GROUP BY 
                `websites`.`website_id`
                {$filters->get_sql_order_by('websites')}
            
            {$paginator->get_sql_limit()}
        ");
        while($row = $websites_result->fetch_object()) $websites[] = $row;

        /* Export handler */
        $export_fields = ['website_id','domain_id','pixel_key','user_id','name','scheme','host','path','tracking_type','ip_storage_is_enabled','excluded_ips','query_parameters_tracking_is_enabled','bot_exclusion_is_enabled','current_month_sessions_events','last_24_hours_pageviews','last_7_days_pageviews','pageviews_stats_last_datetime','current_month_events_children','current_month_sessions_replays','plan_sessions_events_limit_notice','plan_events_children_limit_notice','plan_sessions_replays_limit_notice','events_children_is_enabled','outbound_clicks_is_enabled','sessions_replays_is_enabled','email_reports_is_enabled','email_reports_last_date','public_statistics_is_enabled','public_statistics_password','is_enabled','last_datetime','datetime'];

        process_export_csv($websites, $export_fields, sprintf(l('websites.title')));
        process_export_json($websites, $export_fields, sprintf(l('websites.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'total_websites' => $total_rows,
            'websites' => $websites,
            'pagination' => $pagination,
            'filters' => $filters,
            'domains' => $domains,
            'data_period' => $data_period,
        ];

        $view = new \Altum\View('websites/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Make sure its not a request from a team member */
        if($this->team) {
            redirect('websites');
        }

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('websites');
        }

        if(!isset($_POST['type'])) {
            redirect('websites');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            set_time_limit(0);

            session_write_close();

            $_POST['selected'] = is_array($_POST['selected']) ? array_filter(array_unique(array_map('intval', $_POST['selected']))) : [];

            \Altum\Cache::store_initialize();

            switch($_POST['type']) {
                case 'delete':

                    foreach($_POST['selected'] as $website_id) {
                        if(!$website = db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->getOne('websites', ['website_id', 'user_id'])) {
                            continue;
                        }

                        /* Get and delete all session replays */
                        $sessions_replays = db()->where('website_id', $website_id)->get('sessions_replays');

                        foreach($sessions_replays as $session_replay) {
                            /* Clear the cache */
                            cache('store_adapter')->deleteItem('session_replay_' . $session_replay->session_id);

                            /* Offload uploading */
                            if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url && $session_replay->is_offloaded) {
                                $file_name = base64_encode($session_replay->session_id . $session_replay->datetime) . '.txt';

                                try {
                                    $s3 = new \Aws\S3\S3Client(get_aws_s3_config());

                                    /* Upload image */
                                    $s3_result = $s3->deleteObject([
                                        'Bucket' => settings()->offload->storage_name,
                                        'Key' => UPLOADS_URL_PATH . 'store/' . $file_name,
                                    ]);
                                } catch (\Exception $exception) {
                                    dil($exception->getMessage());
                                }
                            }
                        }

                        /* Delete the website */
                        db()->where('website_id', $website_id)->delete('websites');

                        /* Clear the cache */
                        cache()->deleteItem('websites_' . $website->user_id);
                        cache()->deleteItemsByTag('website_id=' . $website_id);
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('websites');
    }

    public function reset() {
        \Altum\Authentication::guard();

        /* Make sure its not a request from a team member */
        if($this->team) {
            redirect('websites');
        }

        if (empty($_POST)) {
            throw_404();
        }

        $website_id = (int) $_POST['website_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('websites');
        }

        /* Make sure the link id is created by the logged in user */
        if(!$website = db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->getOne('websites', ['website_id'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete */
            db()->where('website_id', $website_id)->delete('websites_visitors');
            db()->where('website_id', $website_id)->delete('visitors_sessions');
            db()->where('website_id', $website_id)->delete('sessions_events');
            db()->where('website_id', $website_id)->delete('lightweight_events');
            db()->where('website_id', $website_id)->delete('goals_conversions');
            db()->where('website_id', $website_id)->delete('heatmaps_snapshots');
            db()->where('website_id', $website_id)->update('websites', [
                'last_24_hours_pageviews' => 0,
                'last_7_days_pageviews' => 0,
                'pageviews_stats_last_datetime' => null,
            ]);
            db()->where('website_id', $website_id)->update('websites_heatmaps', [
                'snapshot_id_desktop' => null,
                'desktop_size' => null,

                'snapshot_id_tablet' => null,
                'tablet_size' => null,

                'snapshot_id_mobile' => null,
                'mobile_size' => null,
            ]);

            /* Clear the cache */
            cache()->deleteItem('websites_' . $this->user->user_id);
            cache()->deleteItemsByTag('website_id=' . $website_id);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.update2'));

            redirect('websites');

        }

        redirect('websites');
    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Make sure its not a request from a team member */
        if($this->team) {
            redirect('websites');
        }

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        $website_id = (int) $_POST['website_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$website = db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->getOne('websites', ['website_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Get and delete all session replays */
            $sessions_replays = db()->where('website_id', $website_id)->get('sessions_replays', ['replay_id']);

            foreach($sessions_replays as $session_replay) {
                (new SessionsReplays())->delete($session_replay->replay_id);
            }

            /* Database query */
            db()->where('website_id', $website_id)->where('user_id', $this->user->user_id)->delete('websites');

            /* Clear the cache */
            cache()->deleteItem('websites_' . $this->user->user_id);
            cache()->deleteItemsByTag('website_id=' . $website_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $website->name . '</strong>'));

            redirect('websites');
        }

        redirect('websites');
    }

}
