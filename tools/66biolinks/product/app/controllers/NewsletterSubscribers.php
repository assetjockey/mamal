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

class NewsletterSubscribers extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['newsletter_subscriber_id', 'status', 'source', 'link_id', 'project_id', 'biolink_block_id'], ['email', 'name'], ['newsletter_subscriber_id', 'email', 'name', 'status', 'source', 'datetime', 'last_datetime', 'unsubscribed_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'unsubscribed_datetime']));
        $filters->set_default_order_by($this->user->preferences->newsletter_subscribers_default_order_by ?? 'newsletter_subscriber_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `newsletter_subscribers` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('newsletter-subscribers?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the subscribers list */
        $newsletter_subscribers = [];
        $newsletter_subscribers_result = database()->query("
            SELECT
                `newsletter_subscribers`.*
            FROM
                `newsletter_subscribers`
            WHERE
                `newsletter_subscribers`.`user_id` = {$this->user->user_id}
                {$filters->get_sql_where('newsletter_subscribers')}
                {$filters->get_sql_order_by('newsletter_subscribers')}
            {$paginator->get_sql_limit()}
        ");
        while($row = $newsletter_subscribers_result->fetch_object()) {
            $newsletter_subscribers[] = $row;
        }

        /* Export handler */
        process_export_json($newsletter_subscribers, ['newsletter_subscriber_id', 'user_id', 'datum_id', 'biolink_block_id', 'link_id', 'project_id', 'email', 'name', 'status', 'source', 'ip', 'unsubscribed_datetime', 'last_datetime', 'datetime'], sprintf(l('newsletter_subscribers.title')));
        process_export_csv($newsletter_subscribers, ['newsletter_subscriber_id', 'email', 'name', 'status', 'source', 'ip', 'datum_id', 'biolink_block_id', 'link_id', 'project_id', 'unsubscribed_datetime', 'last_datetime', 'datetime'], sprintf(l('newsletter_subscribers.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'newsletter_subscribers' => $newsletter_subscribers,
            'total_newsletter_subscribers' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
            'biolink_blocks' => require APP_PATH . 'includes/biolink_blocks.php',
        ];

        $view = new \Altum\View(\Altum\Plugin::get('newsletters')->path . 'views/newsletter-subscribers/index', (array) $this, true);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        /* Check for any errors */
        if(empty($_POST)) {
            throw_404();
        }

        if(empty($_POST['selected'])) {
            redirect('newsletter-subscribers');
        }

        if(!isset($_POST['type'])) {
            redirect('newsletter-subscribers');
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
                case 'subscribe':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.newsletter_subscribers')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('newsletter-subscribers');
                    }

                    foreach($_POST['selected'] as $newsletter_subscriber_id) {
                        db()->where('newsletter_subscriber_id', $newsletter_subscriber_id)->where('user_id', $this->user->user_id)->update('newsletter_subscribers', [
                            'status' => 'subscribed',
                            'unsubscribed_datetime' => null,
                            'last_datetime' => get_date(),
                        ]);
                    }

                    break;

                case 'unsubscribe':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.newsletter_subscribers')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('newsletter-subscribers');
                    }

                    foreach($_POST['selected'] as $newsletter_subscriber_id) {
                        db()->where('newsletter_subscriber_id', $newsletter_subscriber_id)->where('user_id', $this->user->user_id)->update('newsletter_subscribers', [
                            'status' => 'unsubscribed',
                            'unsubscribed_datetime' => get_date(),
                            'last_datetime' => get_date(),
                        ]);
                    }

                    break;

                case 'delete':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.newsletter_subscribers')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('newsletter-subscribers');
                    }

                    foreach($_POST['selected'] as $newsletter_subscriber_id) {
                        db()->where('newsletter_subscriber_id', $newsletter_subscriber_id)->where('user_id', $this->user->user_id)->delete('newsletter_subscribers');
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.update2'));

        }

        redirect('newsletter-subscribers');
    }

    public function update_status() {

        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.newsletter_subscribers')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('newsletter-subscribers');
        }

        if(empty($_POST)) {
            throw_404();
        }

        $newsletter_subscriber_id = (int) $_POST['newsletter_subscriber_id'];
        $_POST['status'] = in_array($_POST['status'], ['subscribed', 'unsubscribed']) ? input_clean($_POST['status']) : 'subscribed';

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$newsletter_subscriber = db()->where('newsletter_subscriber_id', $newsletter_subscriber_id)->where('user_id', $this->user->user_id)->getOne('newsletter_subscribers', ['newsletter_subscriber_id'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Update the subscriber */
            db()->where('newsletter_subscriber_id', $newsletter_subscriber_id)->where('user_id', $this->user->user_id)->update('newsletter_subscribers', [
                'status' => $_POST['status'],
                'unsubscribed_datetime' => $_POST['status'] == 'unsubscribed' ? get_date() : null,
                'last_datetime' => get_date(),
            ]);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.update2'));

        }

        redirect('newsletter-subscribers');
    }

    public function delete() {

        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.newsletter_subscribers')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('newsletter-subscribers');
        }

        if(empty($_POST)) {
            throw_404();
        }

        $newsletter_subscriber_id = (int) $_POST['newsletter_subscriber_id'];

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$newsletter_subscriber = db()->where('newsletter_subscriber_id', $newsletter_subscriber_id)->where('user_id', $this->user->user_id)->getOne('newsletter_subscribers', ['newsletter_subscriber_id', 'email'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the subscriber */
            db()->where('newsletter_subscriber_id', $newsletter_subscriber_id)->delete('newsletter_subscribers');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $newsletter_subscriber->email . '</strong>'));

        }

        redirect('newsletter-subscribers');
    }

}
