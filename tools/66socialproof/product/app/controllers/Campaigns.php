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


defined('ALTUMCODE') || die();

class Campaigns extends Controller {

    public function index() {
        \Altum\Authentication::guard();

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user_id($this->user->user_id);

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled'], ['name', 'domain', 'domain_id'], ['campaign_id', 'last_datetime', 'datetime', 'name', 'domain'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->campaigns_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `campaigns` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('campaigns?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the campaigns list for the user */
        $campaigns = [];
        $campaigns_result = database()->query("
            SELECT campaigns.*, COUNT(notifications.notification_id) AS notifications_count 
            FROM `campaigns` 
            LEFT JOIN `notifications` ON campaigns.campaign_id = notifications.campaign_id 
            WHERE campaigns.user_id = {$this->user->user_id} 
            {$filters->get_sql_where('campaigns')} 
            GROUP BY campaigns.campaign_id 
            {$filters->get_sql_order_by('campaigns')} 
            {$paginator->get_sql_limit()}
        ");
        while($row = $campaigns_result->fetch_object()) {
            $row->branding = json_decode($row->branding);
            $campaigns[] = $row;
        }

        /* Export handler */
        process_export_csv_new($campaigns, ['campaign_id', 'user_id', 'domain_id', 'pixel_key', 'name', 'domain', 'branding', 'is_enabled', 'last_datetime', 'datetime'], ['branding'], sprintf(l('campaigns.title')));
        process_export_json($campaigns, ['campaign_id', 'user_id', 'domain_id', 'pixel_key', 'name', 'domain', 'branding', 'is_enabled', 'last_datetime', 'datetime'], sprintf(l('campaigns.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Prepare the view */
        $data = [
            'campaigns' => $campaigns,
            'campaigns_total' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
            'domains' => $domains,
            'notification_handlers' => $notification_handlers,
        ];

        $view = new \Altum\View('campaigns/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
