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

use Altum\Meta;

defined('ALTUMCODE') || die();

class SentActivation extends Controller {

    public function index() {

        \Altum\Authentication::guard('guest');

        if(!settings()->users->email_confirmation) {
            throw_404();
        }

        $email = session_get('sent_activation_email');

        if(!$email) {
            redirect('resend-activation');
        }

        /* Clear email session */
        session_unset_key('sent_activation_email');

        /* Meta */
        Meta::set_robots('noindex');

        /* Disable OG Image */
        if(\Altum\Plugin::is_active('dynamic-og-images') && settings()->dynamic_og_images->is_enabled) {
            \Altum\Plugin\DynamicOgImages::$should_process = false;
        }

        /* Prepare the view */
        $data = [
            'email' => $email,
        ];

        $view = new \Altum\View('sent-activation/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
