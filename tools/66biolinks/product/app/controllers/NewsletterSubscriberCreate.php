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

class NewsletterSubscriberCreate extends Controller {

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
            $_POST['email'] = mb_substr(mb_strtolower(input_clean_email($_POST['email'] ?? '')), 0, 320);
            $_POST['name'] = mb_substr(input_clean($_POST['name'] ?? '', 64), 0, 64);
            $_POST['status'] = in_array($_POST['status'] ?? null, ['subscribed', 'unsubscribed']) ? input_clean($_POST['status']) : 'subscribed';

            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            $required_fields = ['email'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) == false) {
                Alerts::add_field_error('email', l('global.error_message.invalid_email'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Check if the subscriber already exists */
                if($newsletter_subscriber = db()->where('user_id', $this->user->user_id)->where('email', $_POST['email'])->getOne('newsletter_subscribers')) {
                    db()->where('newsletter_subscriber_id', $newsletter_subscriber->newsletter_subscriber_id)->update('newsletter_subscribers', [
                        'name' => $_POST['name'] ?: $newsletter_subscriber->name,
                        'status' => $_POST['status'],
                        'source' => $newsletter_subscriber->source ?: 'manual',
                        'ip' => get_ip(),
                        'unsubscribed_datetime' => $_POST['status'] == 'unsubscribed' ? get_date() : null,
                        'last_datetime' => get_date(),
                    ]);

                    /* Set a nice success message */
                    Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['email'] . '</strong>'));

                    redirect('newsletter-subscribers');
                } else {
                    /* Check the subscriber plan limit */
                    $newsletter_subscribers_limit = (int) ($this->user->plan_settings->newsletter_subscribers_limit ?? -1);
                    if($newsletter_subscribers_limit != -1) {
                        $total_newsletter_subscribers = db()->where('user_id', $this->user->user_id)->getValue('newsletter_subscribers', 'COUNT(*)');

                        if($total_newsletter_subscribers >= $newsletter_subscribers_limit) {
                            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
                        }
                    }
                }

                if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                    /* Insert the subscriber */
                    db()->insert('newsletter_subscribers', [
                        'user_id' => $this->user->user_id,
                        'email' => $_POST['email'],
                        'name' => $_POST['name'],
                        'status' => $_POST['status'],
                        'source' => 'manual',
                        'ip' => get_ip(),
                        'unsubscribed_datetime' => $_POST['status'] == 'unsubscribed' ? get_date() : null,
                        'datetime' => get_date(),
                        'last_datetime' => get_date(),
                    ]);

                    /* Set a nice success message */
                    Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['email'] . '</strong>'));

                    redirect('newsletter-subscribers');
                }
            }
        }

        /* Main View */
        $data = [
            'values' => [
                'email' => $_POST['email'] ?? '',
                'name' => $_POST['name'] ?? '',
                'status' => $_POST['status'] ?? 'subscribed',
            ],
        ];

        $view = new \Altum\View(\Altum\Plugin::get('newsletters')->path . 'views/newsletter-subscriber-create/index', (array) $this, true);

        $this->add_view_content('content', $view->run($data));

    }

}
