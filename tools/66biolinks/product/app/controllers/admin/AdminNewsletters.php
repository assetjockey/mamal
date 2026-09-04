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

class AdminNewsletters extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['newsletter_id', 'user_id', 'status', 'segment'], ['name', 'subject', 'content'], ['newsletter_id', 'user_id', 'name', 'datetime', 'last_datetime', 'total_emails', 'sent_emails', 'views', 'clicks', 'last_sent_email_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_sent_email_datetime']));
        $filters->set_default_order_by('newsletter_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `newsletters` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/newsletters?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $newsletters = [];
        $newsletters_result = database()->query("
            SELECT
                `newsletters`.*,
                `users`.`name` AS `user_name`,
                `users`.`email` AS `user_email`,
                `users`.`avatar` AS `user_avatar`
            FROM
                `newsletters`
            LEFT JOIN
                `users` ON `newsletters`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('newsletters')}
                {$filters->get_sql_order_by('newsletters')}
            {$paginator->get_sql_limit()}
        ");
        while($row = $newsletters_result->fetch_object()) {
            $row->content_text = input_clean($row->content);
            $newsletters[] = $row;
        }

        /* Export handler */
        process_export_json($newsletters, ['newsletter_id', 'user_id', 'name', 'subject', 'content', 'content_text', 'segment', 'subscribers_ids', 'sent_subscribers_ids', 'sent_emails', 'views', 'clicks', 'total_emails', 'status', 'last_sent_email_datetime', 'datetime', 'last_datetime'], sprintf(l('admin_newsletters.title')));
        process_export_csv($newsletters, ['newsletter_id', 'user_id', 'name', 'subject', 'content_text', 'segment', 'subscribers_ids', 'sent_subscribers_ids', 'sent_emails', 'views', 'clicks', 'total_emails', 'status', 'last_sent_email_datetime', 'datetime', 'last_datetime'], sprintf(l('admin_newsletters.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'newsletters' => $newsletters,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'filters' => $filters
        ];

        $view = new \Altum\View(\Altum\Plugin::get('newsletters')->path . 'views/admin/newsletters/index', (array) $this, true);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        /* Check for any errors */
        if(empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('admin/newsletters');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/newsletters');
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

                    foreach($_POST['selected'] as $newsletter_id) {
                        db()->where('newsletter_id', $newsletter_id)->delete('newsletters');
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/newsletters');
    }

    public function delete() {

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        $newsletter_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$newsletter = db()->where('newsletter_id', $newsletter_id)->getOne('newsletters', ['newsletter_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the newsletter */
            db()->where('newsletter_id', $newsletter_id)->delete('newsletters');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $newsletter->name . '</strong>'));

        }

        redirect('admin/newsletters');
    }

}
