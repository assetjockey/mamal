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

use Altum\Models\User;

defined('ALTUMCODE') || die();

class TransferRequestRedirect extends Controller {

    public function index() {

		if(!settings()->transfers->transfer_requests_is_enabled) {
			throw_404();
		}

        $transfer_request_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$transfer_request = db()->where('transfer_request_id', $transfer_request_id)->getOne('transfer_requests', ['transfer_request_id', 'domain_id', 'user_id', 'url'])) {
            throw_404();
        }

        $transfer_request_user = (new User())->get_user_by_user_id($transfer_request->user_id);

        /* Only works if admin or owner of transfer_request */
        if(is_logged_in() && (user()->type == 1 || $transfer_request_user->user_id == $transfer_request->user_id)) {
            /* Generate the transfer request full URL base */
            $transfer_request->full_url = (new \Altum\Models\TransferRequests())->get_transfer_request_full_url($transfer_request, $transfer_request_user);

            header('Location: ' . $transfer_request->full_url);
            die();
        } else {
            throw_404();
        }

    }
}
