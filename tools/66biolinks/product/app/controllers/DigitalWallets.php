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

class DigitalWallets extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('digital-wallets') || !settings()->digital_wallets->is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', 'domain_id', 'link_id'], ['name'], ['digital_wallet_id', 'pageviews', 'last_datetime', 'name', 'datetime'], allowed_datetime_fields: ['last_datetime', 'datetime']));
        $filters->set_default_order_by($this->user->preferences->digital_wallets_default_order_by ?? 'digital_wallet_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `digital_wallets` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('digital-wallets?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the wallet cards list for the user */
        $digital_wallets = [];
        $digital_wallets_result = database()->query("SELECT * FROM `digital_wallets` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $digital_wallets_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $digital_wallets[] = $row;
        }

        /* Export handler */
        process_export_csv_new($digital_wallets, ['digital_wallet_id', 'digital_wallet_hash', 'user_id', 'domain_id', 'link_id', 'location_url', 'name', 'pageviews', 'settings', 'last_datetime', 'datetime'], ['settings'], sprintf(l('digital_wallets.title')));
        process_export_json($digital_wallets, ['digital_wallet_id', 'digital_wallet_hash', 'user_id', 'domain_id', 'link_id', 'location_url', 'name', 'pageviews', 'settings', 'last_datetime', 'datetime'], sprintf(l('digital_wallets.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'digital_wallets' => $digital_wallets,
            'total_digital_wallets' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('digital-wallets/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        if(!\Altum\Plugin::is_active('digital-wallets') || !settings()->digital_wallets->is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Check for any errors */
        if(empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('digital-wallets');
        }

        if(!isset($_POST['type'])) {
            redirect('digital-wallets');
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.digital_wallets')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('digital-wallets');
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

                    (new \Altum\Models\DigitalWallets())->bulk_delete($_POST['selected'], $this->user->user_id);

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('digital-wallets');
    }

    public function delete() {

        if(!\Altum\Plugin::is_active('digital-wallets') || !settings()->digital_wallets->is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.digital_wallets')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('digital-wallets');
        }

        if(empty($_POST)) {
            throw_404();
        }

        $digital_wallet_id = (int) $_POST['digital_wallet_id'];

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$digital_wallet = db()->where('digital_wallet_id', $digital_wallet_id)->where('user_id', $this->user->user_id)->getOne('digital_wallets', ['digital_wallet_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new \Altum\Models\DigitalWallets())->delete($digital_wallet->digital_wallet_id, $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $digital_wallet->name . '</strong>'));

        }

        redirect('digital-wallets');
    }

}
