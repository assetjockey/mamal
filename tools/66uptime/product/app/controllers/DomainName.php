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

use Altum\Title;

defined('ALTUMCODE') || die();

class DomainName extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->domain_names_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        $domain_name_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$domain_name = db()->where('domain_name_id', $domain_name_id)->where('user_id', $this->user->user_id)->getOne('domain_names')) {
            redirect('domain-names');
        }

        $domain_name->whois = json_decode($domain_name->whois ?? '');
        $domain_name->ssl = json_decode($domain_name->ssl ?? '');

        /* Set a custom title */
        Title::set(sprintf(l('domain_name.title'), $domain_name->name));

        /* Prepare the view */
        $data = [
            'domain_name' => $domain_name,
        ];

        $view = new \Altum\View('domain-name/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
