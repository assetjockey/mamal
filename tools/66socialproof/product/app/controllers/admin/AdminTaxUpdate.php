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

class AdminTaxUpdate extends Controller {

    public function index() {

        if(!in_array(settings()->license->type, ['Extended License', 'extended'])) {
            redirect('admin');
        }

        $tax_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$tax = db()->where('tax_id', $tax_id)->getOne('taxes')) {
            redirect('admin/taxes');
        }

        $tax->countries = json_decode($tax->countries);

        /* Main View */
        $data = [
            'tax_id'       => $tax_id,
            'tax'          => $tax,
        ];

        $view = new \Altum\View('admin/tax-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
