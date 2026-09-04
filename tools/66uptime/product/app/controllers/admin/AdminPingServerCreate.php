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

class AdminPingServerCreate extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_installed('ping-servers') && !\Altum\Plugin::is_active('ping-servers')) {
            Alerts::add_error(sprintf(l('admin_plugins.no_access'), \Altum\Plugin::get('ping-servers')->name ?? 'ping-servers'));
            redirect('admin/ping-servers');
        }

        if(!empty($_POST)) {
            /* Clean some posted variables */
            $_POST['name'] = input_clean($_POST['name']);
            $_POST['url'] = input_clean($_POST['url']);
            $_POST['country_code'] = array_key_exists($_POST['country_code'], get_countries_array()) ? input_clean($_POST['country_code']) : 'US';
            $_POST['city_name'] = input_clean($_POST['city_name']);
            $_POST['is_enabled'] = (int) isset($_POST['is_enabled']);
            $_POST['api_key'] = input_clean($_POST['api_key'], 128);

            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

            /* Check for any errors */
            $required_fields = ['name', 'url', 'city_name'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Update the row of the database */
                db()->insert('ping_servers', [
                    'name' => $_POST['name'],
                    'url' => $_POST['url'],
                    'country_code' => $_POST['country_code'],
                    'city_name' => $_POST['city_name'],
                    'api_key' => $_POST['api_key'],
                    'is_enabled' => $_POST['is_enabled'],
                    'datetime' => get_date(),
                ]);

                /* Clear the cache */
                cache()->deleteItem('ping_servers');

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Request the data from outside source */
                try {
                    \Unirest\Request::timeout(5 + 3);
                    $response = \Unirest\Request::post($_POST['url'], [], [
                        'user_agent' => settings()->monitors_heartbeats->user_agent,
                        'ping_method' => settings()->monitors_heartbeats->monitors_ping_method,
                        'type' => 'website',
                        'target' => 'https://example.com/',
                        'port' => 0,
                        'settings' => json_encode([
                            'timeout_seconds' => 5,
                            'request_method' => 'get',
                        ]),
                        'api_key' => $_POST['api_key'],
                        'debug' => 1
                    ]);
                } catch (\Exception $exception) {
                    $exception = true;
                    Alerts::add_error(l('admin_ping_servers.error_message') . '<br />' . $exception->getMessage());
                }

                if(!isset($exception)) {

                    /* Make sure the values wer got are the proper ones */
                    if(!isset($response->body->is_ok)) {
                        Alerts::add_error(l('admin_ping_servers.error_message') . '<br />' . $response->raw_body);
                    }

                    else {
                        Alerts::add_success(l('admin_ping_servers.success_message'));
                        Alerts::add_success(sprintf(l('admin_ping_servers.success_message2'), '<strong>' . 'https://example.com/' . '</strong>', '<strong>' . $_POST['url'] . '</strong>', $response->raw_body));
                    }

                }

                redirect('admin/ping-servers');
            }

        }

        /* Main View */
        $data = [];

        $view = new \Altum\View('admin/ping-server-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
