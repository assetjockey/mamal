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

class AdminTaxCreate extends Controller {

    public function index() {

        if(!in_array(settings()->license->type, ['Extended License', 'extended'])) {
            redirect('admin');
        }

        if(!empty($_POST)) {
            /* Filter some of the variables */
            $_POST['name'] = input_clean($_POST['name'], 64);
            $_POST['description'] = input_clean($_POST['description'], 256);
            $_POST['value'] = (float) $_POST['value'];
            $_POST['value_type'] = in_array($_POST['value_type'], ['percentage', 'fixed']) ? input_clean($_POST['value_type']) : 'fixed';
            $_POST['type'] = in_array($_POST['type'], ['inclusive', 'exclusive']) ? input_clean($_POST['type']) : 'inclusive';
            $_POST['billing_type'] = in_array($_POST['billing_type'], ['personal', 'business', 'both']) ? input_clean($_POST['billing_type']) : 'both';
            $_POST['countries'] = isset($_POST['countries']) ? array_query_clean($_POST['countries']) : null;
            $_POST['state'] = input_clean($_POST['state'], 64);
            $_POST['county'] = input_clean($_POST['county'], 64);

            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Database query */
                db()->insert('taxes', [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'value' => $_POST['value'],
                    'value_type' => $_POST['value_type'],
                    'type' => $_POST['type'],
                    'billing_type' => $_POST['billing_type'],
                    'countries' => empty($_POST['countries']) ? null : json_encode($_POST['countries']),
                    'state' => $_POST['state'],
                    'county' => $_POST['county'],
                    'datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('admin/taxes');
            }
        }

        /* Main View */
        $data = [];

        $view = new \Altum\View('admin/tax-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
