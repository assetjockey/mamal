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

class Unsubscribe extends Controller {

    public function index() {

        $token = $_POST['token'] ?? $_GET['token'] ?? null;

        if(!$token) {
            throw_404();
        }

        $secret = hash(
            'sha256',
            settings()->license->license . '|' . settings()->cron->key . '|list-unsubscribe|v1',
            true
        );

        $user_id = verify_unsubscribe_token($token, $secret);

        if(!$user_id) {
            throw_404();
        }

        $user = db()->where('user_id', $user_id)->getOne('users', ['is_newsletter_subscribed']);

        if(!$user) {
            throw_404();
        }

        if(!empty($_POST) && $user->is_newsletter_subscribed) {
            /* Unsub the user */
            db()->where('user_id', $user_id)->update('users', ['is_newsletter_subscribed' => 0]);

            /* Set a custom title */
            Title::set(l('unsubscribe.success.title'));
        }

        /* Meta */
        Meta::set_robots('noindex');

        /* Disable OG Image */
        if(\Altum\Plugin::is_active('dynamic-og-images') && settings()->dynamic_og_images->is_enabled) {
            \Altum\Plugin\DynamicOgImages::$should_process = false;
        }

        /* Prepare the view */
        $data = [
            'user' => $user,
        ];

        $view = new \Altum\View('unsubscribe/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }


}
