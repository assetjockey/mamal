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
use Altum\Title;

defined('ALTUMCODE') || die();

class StatusPageQr extends Controller {

    public function index() {

        if(!settings()->status_pages->status_pages_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        if(!$this->user->plan_settings->qr_is_enabled) {
            Alerts::add_error(l('global.info_message.plan_feature_no_access'));
            redirect('status-pages');
        }

        $status_page_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$status_page = db()->where('status_page_id', $status_page_id)->where('user_id', $this->user->user_id)->getOne('status_pages')) {
            redirect('status-pages');
        }

        /* Generate the status_page full URL base */
        $status_page->full_url = (new \Altum\Models\StatusPage())->get_status_page_full_url($status_page, $this->user);

        /* Set a custom title */
        Title::set(sprintf(l('status_page_qr.title'), $status_page->name));

        /* Prepare the view */
        $data = [
            'status_page' => $status_page
        ];

        $view = new \Altum\View('status-page-qr/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
