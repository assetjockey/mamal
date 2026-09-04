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

class AdminStatusPages extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'user_id', 'domain_id', 'project_id'], ['name'], ['status_page_id', 'pageviews', 'last_datetime', 'datetime', 'name', 'pageviews'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->status_pages_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `status_pages` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/status-pages?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $status_pages = [];
        $status_pages_result = database()->query("
            SELECT
                `status_pages`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `status_pages`
            LEFT JOIN
                `users` ON `status_pages`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('status_pages')}
                {$filters->get_sql_order_by('status_pages')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $status_pages_result->fetch_object()) {
            $status_pages[] = $row;
        }

        /* Export handler */
        process_export_csv($status_pages, ['status_page_id', 'user_id', 'domain_id', 'project_id', 'url', 'name', 'description', 'pageviews', 'is_se_visible', 'is_removed_branding', 'is_enabled', 'datetime'], sprintf(l('status_pages.title')));
        process_export_json($status_pages, ['status_page_id', 'user_id', 'domain_id', 'project_id', 'url', 'name', 'description', 'pageviews', 'is_se_visible', 'is_removed_branding', 'is_enabled', 'datetime'], sprintf(l('status_pages.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'status_pages' => $status_pages,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/status-pages/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if (empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/status-pages');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/status-pages');
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

                    foreach($_POST['selected'] as $status_page_id) {

                        /* Delete the domain_name */
                        db()->where('status_page_id', $status_page_id)->delete('status_pages');

                        /* Clear the cache */
                        cache()->deleteItemsByTag('status_page_id=' . $status_page_id);

                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/status-pages');
    }

    public function delete() {

        $status_page_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$status_page = db()->where('status_page_id', $status_page_id)->getOne('status_pages', ['status_page_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new \Altum\Models\StatusPage())->delete($status_page->status_page_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $status_page->name . '</strong>'));

        }

        redirect('admin/status-pages');
    }

}
