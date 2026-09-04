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
namespace Altum\controllers;

defined('ALTUMCODE') || die();

class Maintenance extends Controller {

    public function index() {

        if(!settings()->main->maintenance_is_enabled) {
            redirect();
        }

        header('HTTP/1.1 503 Service Unavailable');
        header('Retry-After: 3600');

        /* Prepare the view */
        $data = [];

        $view = new \Altum\View('maintenance/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
