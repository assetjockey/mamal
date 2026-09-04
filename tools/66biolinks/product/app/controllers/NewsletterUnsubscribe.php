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

use Altum\Meta;
use Altum\Title;

defined('ALTUMCODE') || die();

class NewsletterUnsubscribe extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('newsletters')) {
            throw_404();
        }

        $token = $_POST['token'] ?? $_GET['token'] ?? null;

        if(!$token) {
            throw_404();
        }

        $newsletter_subscriber_id = verify_unsubscribe_token($token, newsletters_get_unsubscribe_secret());

        if(!$newsletter_subscriber_id) {
            throw_404();
        }

        $newsletter_subscriber = db()->where('newsletter_subscriber_id', $newsletter_subscriber_id)->getOne('newsletter_subscribers');

        if(!$newsletter_subscriber) {
            throw_404();
        }

        if(!empty($_POST) && $newsletter_subscriber->status == 'subscribed') {
            /* Unsub the subscriber */
            db()->where('newsletter_subscriber_id', $newsletter_subscriber_id)->update('newsletter_subscribers', [
                'status' => 'unsubscribed',
                'unsubscribed_datetime' => get_date(),
                'last_datetime' => get_date(),
            ]);

            $newsletter_subscriber->status = 'unsubscribed';

            /* Set a custom title */
            Title::set(l('newsletter_unsubscribe.success.title'));
        }

        /* Meta */
        Meta::set_robots('noindex');

        /* Disable OG Image */
        if(\Altum\Plugin::is_active('dynamic-og-images') && settings()->dynamic_og_images->is_enabled) {
            \Altum\Plugin\DynamicOgImages::$should_process = false;
        }

        /* Prepare the view */
        $data = [
            'newsletter_subscriber' => $newsletter_subscriber,
        ];

        $view = new \Altum\View(\Altum\Plugin::get('newsletters')->path . 'views/newsletter-unsubscribe/index', (array) $this, true);

        $this->add_view_content('content', $view->run($data));

    }

}
