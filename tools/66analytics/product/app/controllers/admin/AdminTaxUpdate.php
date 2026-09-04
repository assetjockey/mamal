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
