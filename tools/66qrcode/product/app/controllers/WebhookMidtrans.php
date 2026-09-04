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

use Altum\Models\Payments;

defined('ALTUMCODE') || die();

class WebhookMidtrans extends Controller {

    public function index() {

        /* Make sure no cache is being used on the endpoint */
		header('Cache-Control: no-store');

        if(!in_array(settings()->license->type, ['Extended License', 'extended'])) {
            throw_404();
        }

        if((strtoupper($_SERVER['REQUEST_METHOD']) != 'POST')) {
            throw_404();
        }

        /* Get the headers */
        $headers = getallheaders();

        /* Get the payload */
        $payload = trim(@file_get_contents('php://input'));

        /* Log for debugging purposes */
        debug_log('[' . \Altum\Router::$controller . '] ' . print_r(['headers' => $headers, 'payload' => $payload], true));

        $data = json_decode($payload, true);

        if(!$data) {
            die('0');
        }

        if(!in_array($data['transaction_status'], ['capture', 'settlement'])) {
            die('1');
        }

        if(isset($data['fraud_status']) && $data['fraud_status'] != 'accept') {
            die('2');
        }

        if($data['signature_key'] !== hash('sha512', $data['order_id'] . $data['status_code'] . $data['gross_amount'] . settings()->midtrans->server_key)) {
            die('3');
        }

        /* Get payment data */
        $external_payment_id = $data['transaction_id'];
        $payment_subscription_id = null;

        /* Start getting the payment details */
        $payment_total = $data['gross_amount'];
        $payment_currency = $data['currency'];
        $payment_type = $payment_subscription_id ? 'recurring' : 'one_time';

        /* Payment payer details */
        $payer_email = '';
        $payer_name = '';

        /* Process meta data */
        $metadata = explode('&', $data['custom_field1']);
        $user_id = (int) $metadata[0];
        $plan_id = (int) $metadata[1];
        $payment_frequency = $metadata[2];
        $base_amount = $metadata[3];
        $code = $metadata[4];
        $discount_amount = $metadata[5] ? $metadata[5] : 0;
        $taxes_ids = $metadata[6] ?: null;

        (new Payments())->webhook_process_payment(
            'midtrans',
            $external_payment_id,
            $payment_total,
            $payment_currency,
            $user_id,
            $plan_id,
            $payment_frequency,
            $code,
            $discount_amount,
            $base_amount,
            $taxes_ids,
            $payment_type,
            $payment_subscription_id,
            $payer_email,
            $payer_name
        );

        echo 'successful';

    }

}
