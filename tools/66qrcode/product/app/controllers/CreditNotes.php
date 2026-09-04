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

use Altum\Models\Plan;
use Altum\Title;

defined('ALTUMCODE') || die();

class CreditNotes extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Make sure the campaign exists and is accessible to the user */
        if(!$payment = db()->where('id', $id)->getOne('payments')) {
            throw_404();
        }

        if($payment->user_id != $this->user->user_id) {
            throw_404();
        }

        if($payment->status != 'refunded') {
            throw_404();
        }

        /* Try to see if we get details from the billing */
        $payment->billing = json_decode($payment->billing ?? '');
        $payment->business = json_decode($payment->business ?? '');
        $payment->plan = json_decode($payment->plan ?? '');
        $payment->refunds = (array) json_decode($payment->refunds ?? '[]');

        /* Set a custom title */
        Title::set(sprintf(l('credit_notes.title'), 'CN-' . $payment->business->invoice_nr_prefix . $payment->id));

        /* Prepare the view */
        $data = [
            'payment' => $payment,
        ];

        $view = new \Altum\View('credit-notes/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
