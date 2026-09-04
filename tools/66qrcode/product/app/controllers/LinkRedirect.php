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

use Altum\Models\User;

defined('ALTUMCODE') || die();

class LinkRedirect extends Controller {

    public function index() {

        $link_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$link = db()->where('link_id', $link_id)->getOne('links', ['link_id', 'domain_id', 'user_id', 'url'])) {
            throw_404();
        }

        $this->link_user = (new User())->get_user_by_user_id($link->user_id);

        /* Generate the link full URL base */
        $link->full_url = (new \Altum\Models\Link())->get_link_full_url($link, $this->link_user);

        header('Location: ' . $link->full_url);

        die();

    }
}
