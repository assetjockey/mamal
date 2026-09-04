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

class AdminDigitalWallets extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('digital-wallets')) {
            redirect('admin/plugins');
        }

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['digital_wallet_id', 'user_id', 'domain_id', 'link_id'], ['name'], ['digital_wallet_id', 'user_id', 'domain_id', 'link_id', 'pageviews', 'last_datetime', 'datetime', 'name'], allowed_datetime_fields: ['last_datetime', 'datetime']));
        $filters->set_default_order_by($this->user->preferences->digital_wallets_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `digital_wallets` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/digital-wallets?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $digital_wallets = [];
        $digital_wallets_result = database()->query("
            SELECT
                `digital_wallets`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `digital_wallets`
            LEFT JOIN
                `users` ON `digital_wallets`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('digital_wallets')}
                {$filters->get_sql_order_by('digital_wallets')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $digital_wallets_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '') ?: (object) [];
            $digital_wallets[] = $row;
        }

        /* Export handler */
        process_export_csv_new($digital_wallets, ['digital_wallet_id', 'digital_wallet_hash', 'user_id', 'domain_id', 'link_id', 'location_url', 'name', 'pageviews', 'settings', 'last_datetime', 'datetime'], ['settings'], sprintf(l('digital_wallets.title')));
        process_export_json($digital_wallets, ['digital_wallet_id', 'digital_wallet_hash', 'user_id', 'domain_id', 'link_id', 'location_url', 'name', 'pageviews', 'settings', 'last_datetime', 'datetime'], sprintf(l('digital_wallets.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'digital_wallets' => $digital_wallets,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/digital-wallets/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/digital-wallets');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/digital-wallets');
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

                    (new \Altum\Models\DigitalWallets())->bulk_delete($_POST['selected']);

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/digital-wallets');
    }

    public function delete() {

        $digital_wallet_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$digital_wallet = db()->where('digital_wallet_id', $digital_wallet_id)->getOne('digital_wallets', ['digital_wallet_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new \Altum\Models\DigitalWallets())->delete($digital_wallet->digital_wallet_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $digital_wallet->name . '</strong>'));

        }

        redirect('admin/digital-wallets');
    }

}
