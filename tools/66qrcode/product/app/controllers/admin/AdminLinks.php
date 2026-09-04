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

class AdminLinks extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['link_id', 'is_enabled', 'project_id', 'domain_id', 'links_ids', 'user_id'], ['url', 'location_url', 'name'], ['link_id', 'last_datetime', 'datetime', 'url', 'location_url', 'pageviews', 'name'], [], ['links_ids' => 'json_contains'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->links_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/links?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $links = [];
        $links_result = database()->query("
            SELECT
                `links`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`, `domains`.`scheme`, `domains`.`host`
            FROM
                `links`
            LEFT JOIN
                `users` ON `links`.`user_id` = `users`.`user_id`
            LEFT JOIN
                `domains` ON `links`.`domain_id` = `domains`.`domain_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('links')}
                {$filters->get_sql_order_by('links')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $links_result->fetch_object()) {
            $links[] = $row;
        }

        /* Export handler */
        process_export_csv_new($links, ['link_id', 'user_id', 'domain_id', 'project_id', 'links_ids', 'url', 'location_url', 'type', 'file_name', 'file_size', 'file_mime_type', 'name', 'settings', 'password', 'pageviews', 'is_enabled', 'datetime'], ['settings'], sprintf(l('links.title')));
        process_export_json($links, ['link_id', 'user_id', 'domain_id', 'project_id', 'links_ids', 'url', 'location_url', 'type', 'file_name', 'file_size', 'file_mime_type', 'name', 'settings', 'password', 'pageviews', 'is_enabled', 'datetime'], sprintf(l('links.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'links' => $links,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/links/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/links');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/links');
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

                    (new \Altum\Models\Link())->bulk_delete($_POST['selected']);

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/links');
    }

    public function delete() {

        $link_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$link = db()->where('link_id', $link_id)->getOne('links', ['link_id', 'url'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new \Altum\Models\Link())->delete($link->link_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $link->url . '</strong>'));

        }

        redirect('admin/links');
    }

    public function transfer() {

        if (empty($_POST)) {
            throw_404();
        }

        $link_id = (int) $_POST['link_id'];
        $_POST['email'] = input_clean_email($_POST['email'] ?? '');

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$link = db()->where('link_id', $link_id)->getOne('links', ['link_id', 'user_id', 'url', 'type'])) {
            throw_404();
        }

        if(!$current_user = db()->where('user_id', $link->user_id)->getOne('users', ['user_id', 'email'])) {
            throw_404();
        }

        if(!$new_user = db()->where('email', $_POST['email'])->getOne('users', ['user_id', 'email'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Update the database */
            db()->where('link_id', $link->link_id)->update('links', [
                'user_id' => $new_user->user_id,
            ]);

            if($link->type == 'file') {
                db()->where('link_id', $link->link_id)->where('type', 'file')->update('qr_codes', [
                    'user_id' => $new_user->user_id,
                ]);
            }

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('transfer_modal.success_message'), '<strong>' . input_clean($link->url) . '</strong>', '<strong>' . input_clean($current_user->email) . '</strong>', '<strong>' . input_clean($new_user->email) . '</strong>'));

            /* Clear the cache */
            cache()->deleteItemsByTag('user_id=' . $current_user->user_id);
            cache()->deleteItemsByTag('user_id=' . $new_user->user_id);

            /* Redirect */
            redirect('admin/links');

        }

        redirect('admin/links');
    }

}
