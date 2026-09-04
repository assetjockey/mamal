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

class NewsletterSubscribersImport extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.newsletter_subscribers')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('newsletter-subscribers');
        }

        if(!empty($_POST)) {
            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

            if(!isset($_FILES['file'])) {
                Alerts::add_error(l('global.error_message.empty_field'));
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Uploaded file */
            \Altum\Uploads::validate_upload('newsletter_subscribers_csv', 'file', get_max_upload());

            /* Parse csv */
            $csv_contents = file_get_contents($_FILES['file']['tmp_name']);
			$csv_contents = preg_replace('/^\xEF\xBB\xBF/', '', $csv_contents);

			$csv_array = array_map(function($csv_line) {
				return str_getcsv($csv_line, ',', '"', '\\');
			}, preg_split('/\r\n|\r|\n/', $csv_contents));

            if(!$csv_array || !is_array($csv_array)) {
                Alerts::add_error(l('global.error_message.invalid_file_type'));
            }

            $headers_array = array_map(function($header) {
                return trim($header);
            }, $csv_array[0] ?? []);
            unset($csv_array[0]);
            reset($csv_array);

            if(!in_array('email', $headers_array)) {
                Alerts::add_error(l('global.error_message.invalid_file_type'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

				set_time_limit(0);

				session_write_close();

                $imported_subscribers = 0;
                $newsletter_subscribers_limit = (int) ($this->user->plan_settings->newsletter_subscribers_limit ?? -1);
                $total_newsletter_subscribers = $newsletter_subscribers_limit == -1 ? 0 : db()->where('user_id', $this->user->user_id)->getValue('newsletter_subscribers', 'COUNT(*)');

                /* Go over each row */
                foreach($csv_array as $key => $csv_row) {
                    if(count($headers_array) != count($csv_row)) {
                        continue;
                    }

                    /* Email */
                    $array_key = array_search('email', $headers_array);
                    if($array_key === false) continue;
                    $email = mb_substr(mb_strtolower(input_clean_email($csv_row[$array_key] ?? '')), 0, 320);

                    if(filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
                        continue;
                    }

                    /* Name */
                    $array_key = array_search('name', $headers_array);
                    $name = $array_key === false ? '' : input_clean($csv_row[$array_key] ?? '', 64);

                    /* Status */
                    $array_key = array_search('status', $headers_array);
                    $status = $array_key === false ? 'subscribed' : $csv_row[$array_key];
                    $status = in_array($status, ['subscribed', 'unsubscribed']) ? input_clean($status) : 'subscribed';

                    /* Check if the subscriber already exists */
                    if($newsletter_subscriber = db()->where('user_id', $this->user->user_id)->where('email', $email)->getOne('newsletter_subscribers')) {
                        db()->where('newsletter_subscriber_id', $newsletter_subscriber->newsletter_subscriber_id)->update('newsletter_subscribers', [
                            'name' => $name ?: $newsletter_subscriber->name,
                            'status' => $status,
                            'source' => $newsletter_subscriber->source ?: 'manual',
                            'ip' => get_ip(),
                            'unsubscribed_datetime' => $status == 'unsubscribed' ? get_date() : null,
                            'last_datetime' => get_date(),
                        ]);

                        $imported_subscribers++;
                        continue;
                    }

                    /* Check the subscriber plan limit */
                    if($newsletter_subscribers_limit != -1 && $total_newsletter_subscribers >= $newsletter_subscribers_limit) {
                        continue;
                    }

                    /* Insert the subscriber */
                    db()->insert('newsletter_subscribers', [
                        'user_id' => $this->user->user_id,
                        'email' => $email,
                        'name' => $name,
                        'status' => $status,
                        'source' => 'manual',
                        'ip' => get_ip(),
                        'unsubscribed_datetime' => $status == 'unsubscribed' ? get_date() : null,
                        'datetime' => get_date(),
                        'last_datetime' => get_date(),
                    ]);

                    $imported_subscribers++;
                    $total_newsletter_subscribers++;
                }

				session_start();

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.csv_imported'), '<strong>' . $imported_subscribers . '</strong>', mb_strtolower(l('newsletter_subscribers.title'))));

                redirect('newsletter-subscribers');
            }
        }

        /* Main View */
        $data = [];

        $view = new \Altum\View(\Altum\Plugin::get('newsletters')->path . 'views/newsletter-subscribers-import/index', (array) $this, true);

        $this->add_view_content('content', $view->run($data));

    }

    public function example() {

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Download the plugin sample file */
        $file_path = \Altum\Plugin::get('newsletters')->path . 'assets/csv/newsletter_subscribers_example.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="newsletter_subscribers_example.csv"');
        header('Content-Length: ' . filesize($file_path));

        readfile($file_path);
        die();

    }

}
