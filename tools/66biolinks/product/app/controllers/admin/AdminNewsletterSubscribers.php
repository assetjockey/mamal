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

class AdminNewsletterSubscribers extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['newsletter_subscriber_id', 'user_id', 'status', 'source', 'link_id', 'project_id', 'biolink_block_id'], ['email', 'name'], ['newsletter_subscriber_id', 'user_id', 'email', 'name', 'status', 'source', 'datetime', 'last_datetime', 'unsubscribed_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'unsubscribed_datetime']));
        $filters->set_default_order_by('newsletter_subscriber_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `newsletter_subscribers` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/newsletter-subscribers?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $newsletter_subscribers = [];
        $newsletter_subscribers_result = database()->query("
            SELECT
                `newsletter_subscribers`.*,
                `users`.`name` AS `user_name`,
                `users`.`email` AS `user_email`,
                `users`.`avatar` AS `user_avatar`
            FROM
                `newsletter_subscribers`
            LEFT JOIN
                `users` ON `newsletter_subscribers`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('newsletter_subscribers')}
                {$filters->get_sql_order_by('newsletter_subscribers')}
            {$paginator->get_sql_limit()}
        ");
        while($row = $newsletter_subscribers_result->fetch_object()) {
            $newsletter_subscribers[] = $row;
        }

        /* Export handler */
        process_export_json($newsletter_subscribers, ['newsletter_subscriber_id', 'user_id', 'datum_id', 'biolink_block_id', 'link_id', 'project_id', 'email', 'name', 'status', 'source', 'ip', 'unsubscribed_datetime', 'last_datetime', 'datetime'], sprintf(l('admin_newsletter_subscribers.title')));
        process_export_csv($newsletter_subscribers, ['newsletter_subscriber_id', 'user_id', 'email', 'name', 'status', 'source', 'ip', 'datum_id', 'biolink_block_id', 'link_id', 'project_id', 'unsubscribed_datetime', 'last_datetime', 'datetime'], sprintf(l('admin_newsletter_subscribers.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'newsletter_subscribers' => $newsletter_subscribers,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'filters' => $filters,
            'biolink_blocks' => require APP_PATH . 'includes/biolink_blocks.php',
        ];

        $view = new \Altum\View(\Altum\Plugin::get('newsletters')->path . 'views/admin/newsletter-subscribers/index', (array) $this, true);

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
            redirect('admin/newsletter-subscribers');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/newsletter-subscribers');
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

                    foreach($_POST['selected'] as $newsletter_subscriber_id) {
                        db()->where('newsletter_subscriber_id', $newsletter_subscriber_id)->delete('newsletter_subscribers');
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/newsletter-subscribers');
    }

    public function delete() {

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        $newsletter_subscriber_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$newsletter_subscriber = db()->where('newsletter_subscriber_id', $newsletter_subscriber_id)->getOne('newsletter_subscribers', ['newsletter_subscriber_id', 'email'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the subscriber */
            db()->where('newsletter_subscriber_id', $newsletter_subscriber_id)->delete('newsletter_subscribers');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $newsletter_subscriber->email . '</strong>'));

        }

        redirect('admin/newsletter-subscribers');
    }

}
