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

class Help extends Controller {

    public function index() {

        $page = isset($this->params[0]) ? query_clean(get_slug($this->params[0],'_')) : 'introduction';
        $page = preg_replace('/' . '-' . '+/', '_', $page);

        /* Check if page exists */
        if(!file_exists(THEME_PATH . 'views/help/' . $page . '.php')) {
            redirect('help');
        }

        $view = new \Altum\View('help/' . $page, (array) $this);
        $this->add_view_content('page', $view->run());

        /* Set a custom title */
        Title::set(sprintf(l('help.title'), l('help.' . $page . '.title')));

        /* Prepare the view */
        $data = [
            'page' => $page
        ];

        $view = new \Altum\View('help/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
