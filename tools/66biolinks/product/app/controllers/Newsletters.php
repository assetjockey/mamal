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
use Altum\Response;

defined('ALTUMCODE') || die();

class Newsletters extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['newsletter_id', 'status', 'segment'], ['name', 'subject', 'content'], ['newsletter_id', 'name', 'datetime', 'last_datetime', 'total_emails', 'sent_emails', 'views', 'clicks', 'last_sent_email_datetime'], allowed_datetime_fields: ['datetime', 'last_datetime', 'last_sent_email_datetime']));
        $filters->set_default_order_by($this->user->preferences->newsletters_default_order_by ?? 'newsletter_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `newsletters` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('newsletters?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the newsletters list */
        $newsletters = [];
        $newsletters_result = database()->query("
            SELECT
                *
            FROM
                `newsletters`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
            {$paginator->get_sql_limit()}
        ");
        while($row = $newsletters_result->fetch_object()) {
            $row->content_text = input_clean($row->content);
            $newsletters[] = $row;
        }

        /* Export handler */
        process_export_json($newsletters, ['newsletter_id', 'user_id', 'name', 'subject', 'content', 'content_text', 'segment', 'subscribers_ids', 'sent_subscribers_ids', 'sent_emails', 'views', 'clicks', 'total_emails', 'status', 'last_sent_email_datetime', 'datetime', 'last_datetime'], sprintf(l('newsletters.title')));
        process_export_csv($newsletters, ['newsletter_id', 'name', 'subject', 'content_text', 'segment', 'subscribers_ids', 'sent_subscribers_ids', 'sent_emails', 'views', 'clicks', 'total_emails', 'status', 'last_sent_email_datetime', 'datetime', 'last_datetime'], sprintf(l('newsletters.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'newsletters' => $newsletters,
            'total_newsletters' => $total_rows,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'filters' => $filters
        ];

        $view = new \Altum\View(\Altum\Plugin::get('newsletters')->path . 'views/newsletters/index', (array) $this, true);

        $this->add_view_content('content', $view->run($data));

    }

    public function get_segment_count() {

        if($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw_404();
        }

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        \Altum\Authentication::guard();

        $segment = isset($_GET['segment']) ? input_clean($_GET['segment']) : 'all';

        /* Segment settings */
        $settings = [
            'project_id' => (int) ($_GET['project_id'] ?? 0),
            'link_id' => (int) ($_GET['link_id'] ?? 0),
            'biolink_block_id' => (int) ($_GET['biolink_block_id'] ?? 0),
        ];

        /* Custom subscribers ids */
        $_GET['subscribers_ids'] = trim($_GET['subscribers_ids'] ?? '');
        $_GET['subscribers_ids'] = array_filter(array_map('intval', explode(',', $_GET['subscribers_ids'])));
        $_GET['subscribers_ids'] = array_values(array_unique($_GET['subscribers_ids']));

        $count = count(newsletters_get_subscribers_ids_by_segment($this->user->user_id, $segment, $settings, $_GET['subscribers_ids']));

        Response::json('', 'success', ['count' => $count]);
    }

    public function duplicate() {

        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        if(empty($_POST)) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.newsletters')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('newsletters');
        }

        $newsletter_id = (int) $_POST['newsletter_id'];

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$newsletter = db()->where('newsletter_id', $newsletter_id)->where('user_id', $this->user->user_id)->getOne('newsletters')) {
            redirect('newsletters');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Check for the plan limit */
            $newsletters_limit = (int) ($this->user->plan_settings->newsletters_limit ?? -1);
            if($newsletters_limit != -1) {
                $total_newsletters = db()->where('user_id', $this->user->user_id)->getValue('newsletters', 'COUNT(*)');

                if($total_newsletters >= $newsletters_limit) {
                    Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
                    redirect('newsletters');
                }
            }

            /* Insert to database */
            $newsletter_id = db()->insert('newsletters', [
                'user_id' => $this->user->user_id,
                'name' => string_truncate($newsletter->name . ' - ' . l('global.duplicated'), 64, null),
                'subject' => $newsletter->subject,
                'content' => json_decode($newsletter->content) ? $newsletter->content : '',
                'segment' => $newsletter->segment,
                'settings' => $newsletter->settings,
                'subscribers_ids' => $newsletter->subscribers_ids,
                'sent_subscribers_ids' => '[]',
                'total_emails' => $newsletter->total_emails,
                'status' => 'draft',
                'datetime' => get_date(),
            ]);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($newsletter->name) . '</strong>'));

            /* Redirect */
            redirect('newsletter-update/' . $newsletter_id);

        }

        redirect('newsletters');
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
            redirect('newsletters');
        }

        if(!isset($_POST['type'])) {
            redirect('newsletters');
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

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.newsletters')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('newsletters');
                    }

                    foreach($_POST['selected'] as $newsletter_id) {
                        db()->where('newsletter_id', $newsletter_id)->where('user_id', $this->user->user_id)->delete('newsletters');
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('newsletters');
    }

    public function delete() {

        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.newsletters')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('newsletters');
        }

        if(empty($_POST)) {
            throw_404();
        }

        $newsletter_id = (int) $_POST['newsletter_id'];

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$newsletter = db()->where('newsletter_id', $newsletter_id)->where('user_id', $this->user->user_id)->getOne('newsletters', ['newsletter_id', 'name'])) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the newsletter */
            db()->where('newsletter_id', $newsletter_id)->delete('newsletters');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $newsletter->name . '</strong>'));

        }

        redirect('newsletters');
    }

}
