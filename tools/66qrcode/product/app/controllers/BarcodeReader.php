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

use Altum\Alerts;

defined('ALTUMCODE') || die();

class BarcodeReader extends Controller {

    public function index() {

        if(!settings()->codes->barcode_reader_is_enabled) {
            throw_404();
        }

        if(!$this->user->plan_settings->barcode_reader_is_enabled) {
            Alerts::add_error(l('global.info_message.plan_feature_no_access'));
            redirect();
        }

        /* Main View */
        $data = [];

        $view = new \Altum\View('barcode-reader/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
