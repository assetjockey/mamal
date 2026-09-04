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

class AdminCampaigns extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['campaign_id', 'user_id', 'is_enabled'], ['name', 'domain', 'domain_id'], ['campaign_id', 'last_datetime', 'datetime', 'name', 'domain'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->campaigns_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `campaigns` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/campaigns?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $campaigns = [];
        $campaigns_result = database()->query("
            SELECT
                `campaigns`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `campaigns`
            LEFT JOIN
                `users` ON `campaigns`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('campaigns')}
                {$filters->get_sql_order_by('campaigns')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $campaigns_result->fetch_object()) {
            $campaigns[] = $row;
        }

        /* Export handler */
        process_export_csv($campaigns, ['campaign_id', 'user_id', 'domain_id', 'pixel_key', 'name', 'domain', 'is_enabled', 'last_datetime', 'datetime'], sprintf(l('admin_campaigns.title')));
        process_export_json($campaigns, ['campaign_id', 'user_id', 'domain_id', 'pixel_key', 'name', 'domain', 'is_enabled', 'last_datetime', 'datetime'], sprintf(l('admin_campaigns.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'campaigns' => $campaigns,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/campaigns/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/campaigns');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/campaigns');
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

                    foreach($_POST['selected'] as $campaign_id) {
                        $user_id = db()->where('campaign_id', $campaign_id)->getValue('campaigns', 'user_id');

                        /* Delete the notification */
                        db()->where('campaign_id', $campaign_id)->delete('campaigns');

                        /* Clear the cache */
                        cache()->deleteItem('campaigns_total?user_id=' . $user_id);
                        cache()->deleteItem('notifications_total?user_id=' . $user_id);
                        cache()->deleteItemsByTag('campaign_id=' . $campaign_id);
                    }
                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/campaigns');
    }

    public function delete() {

        $campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$campaign = db()->where('campaign_id', $campaign_id)->getOne('campaigns', ['user_id', 'campaign_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the campaign */
            db()->where('campaign_id', $campaign->campaign_id)->delete('campaigns');

            /* Clear the cache */
            cache()->deleteItemsByTag('campaign_id=' . $campaign->campaign_id);
            cache()->deleteItem('campaigns_total?user_id=' . $campaign->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $campaign->name . '</strong>'));

        }

        redirect('admin/campaigns');
    }

    public function transfer() {

        if (empty($_POST)) {
            throw_404();
        }

        $campaign_id = (int) $_POST['campaign_id'];
        $_POST['email'] = input_clean_email($_POST['email'] ?? '');

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$campaign = db()->where('campaign_id', $campaign_id)->getOne('campaigns', ['campaign_id', 'user_id', 'name'])) {
            throw_404();
        }

        if(!$current_user = db()->where('user_id', $campaign->user_id)->getOne('users', ['user_id', 'email'])) {
            throw_404();
        }

        if(!$new_user = db()->where('email', $_POST['email'])->getOne('users', ['user_id', 'email'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Update the database */
            db()->where('campaign_id', $campaign->campaign_id)->update('campaigns', ['user_id' => $new_user->user_id]);
            db()->where('campaign_id', $campaign->campaign_id)->update('notifications', ['user_id' => $new_user->user_id]);

            /* Clear the cache */
            cache()->deleteItemsByTag('user_id=' . $current_user->user_id);
            cache()->deleteItemsByTag('user_id=' . $new_user->user_id);
            cache()->deleteItemsByTag('campaign_id=' . $campaign_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('transfer_modal.success_message'), '<strong>' . input_clean($campaign->name) . '</strong>', '<strong>' . input_clean($current_user->email) . '</strong>', '<strong>' . input_clean($new_user->email) . '</strong>'));

            /* Redirect */
            redirect('admin/campaigns');

        }

        redirect('admin/campaigns');
    }

}
