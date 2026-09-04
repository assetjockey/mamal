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

defined('ALTUMCODE') || die();

class SSO extends Controller {

    public function index() {
        throw_404();
    }

    public function switch() {

        \Altum\Authentication::guard();

        $to = isset($_GET['to']) && array_key_exists($_GET['to'], (array) settings()->sso->websites) ? input_clean($_GET['to']) : null;
        $redirect = isset($_GET['redirect']) ? input_clean($_GET['redirect']) : 'dashboard';

        if(!$to) {
            throw_404();
        }

        $website = settings()->sso->websites->{$to};

        $response = \Unirest\Request::post(
            $website->url . 'admin-api/sso/login',
            ['Authorization' => 'Bearer ' . $website->api_key],
            \Unirest\Request\Body::form([
                'email' => $this->user->email,
                'name' => $this->user->name,
                'redirect' => $redirect,
            ])
        );

        /* Check against errors */
        if($response->code >= 400) {
            \Altum\Alerts::add_error($response->body->errors[0]->title);
            redirect('dashboard');
        }

        /* Redirect */
        header('Location: ' . $response->body->data->url); die();

    }

}
