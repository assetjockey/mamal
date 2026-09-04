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

use Altum\Title;

defined('ALTUMCODE') || die();

class ApiDocumentation extends Controller {

    public function index() {

        if(!settings()->main->api_is_enabled) {
            throw_404();
        }

        $endpoint = isset($this->params[0]) ? query_clean(str_replace('-', '_', $this->params[0])) : null;

        if($endpoint) {
            if(!file_exists(THEME_PATH . 'views/api-documentation/' . $endpoint . '.php')) {
                throw_404();
            }

            $title = match($endpoint) {
                'statistics' => l('links_statistics.title'),
                'users_logs' => l('account_logs.title'),
                'payments' => l('account_payments.title'),
                'user' => l('api_documentation.user'),
                'team_members' => l('api_documentation.team_members'),
                'teams_member' => l('api_documentation.teams_member'),
                default => l($endpoint . '.title')
            };

            Title::set(sprintf(l('api_documentation.title_dynamic'), $title));

            /* Prepare the view */
            $view = new \Altum\View('api-documentation/' . $endpoint, (array) $this);
        } else {
            /* Prepare the view */
            $view = new \Altum\View('api-documentation/index', (array) $this);
        }



        $this->add_view_content('content', $view->run());

    }
}


