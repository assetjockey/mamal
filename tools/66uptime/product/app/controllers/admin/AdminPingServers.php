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

class AdminPingServers extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'country_code'], ['name'], ['ping_server_id', 'last_datetime', 'datetime', 'name'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by('ping_server_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `ping_servers` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/ping-servers?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $ping_servers = [];
        $ping_servers_result = database()->query("
            SELECT
                *
            FROM
                `ping_servers`
            WHERE
                1 = 1
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
            
            {$paginator->get_sql_limit()}
        ");
        while($row = $ping_servers_result->fetch_object()) {
            $row->monitors = database()->query("SELECT COUNT(`monitor_id`) AS `total` FROM `monitors` WHERE JSON_CONTAINS(`ping_servers_ids`, '{$row->ping_server_id}', '$');")->fetch_object()->total ?? 0;
            $ping_servers[] = $row;
        }

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'ping_servers' => $ping_servers,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/ping-servers/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }


    public function delete() {

        $ping_server_id = (isset($this->params[0])) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if($ping_server_id == 1) {
            redirect('admin/ping-servers');
        }

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$ping_server = db()->where('ping_server_id', $ping_server_id)->getOne('ping_servers', ['ping_server_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the ping server */
            db()->where('ping_server_id', $ping_server_id)->delete('ping_servers');

            /* Clear the cache */
            cache()->deleteItem('ping_servers');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $ping_server->name . '</strong>'));

        }

        redirect('admin/ping-servers');
    }

}
