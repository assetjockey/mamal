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

class TransferQr extends Controller {

    public function index() {

        //\Altum\Authentication::guard();

        $transfer_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Get transfer details */
        if(!$transfer = db()->where('transfer_id', $transfer_id)->getOne('transfers')) {
            throw_404();
        }

        /* Make sure the current user has access */
        if(($transfer->uploader_id != md5(get_ip())) && (!$transfer->user_id || $transfer->user_id != $this->user->user_id)) {
            throw_404();
        }

        /* Check for plan access */
        if(!$this->user->plan_settings->qr_is_enabled) {
            Alerts::add_error(l('global.info_message.plan_feature_no_access'));
            redirect('transfer/' . $transfer->transfer_id);
        }

        /* Generate the transfer full URL base */
        $transfer->full_url = (new \Altum\Models\Transfers())->get_transfer_full_url($transfer, $this->user);

        /* Set a custom title */
        Title::set(sprintf(l('transfer_qr.title'), $transfer->name));

        /* Prepare the view */
        $data = [
            'transfer' => $transfer
        ];

        $view = new \Altum\View('transfer-qr/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
