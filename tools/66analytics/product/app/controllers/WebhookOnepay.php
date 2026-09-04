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
namespace Altum\controllers;

use Altum\Models\Payments;

defined('ALTUMCODE') || die();

class WebhookOnepay extends Controller {

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

        /* Get the 1pay webhook signature */
        $signature = null;
        foreach($headers as $key => $value) {
            if(mb_strtolower($key) == 'x-webhook-signature') {
                $signature = $value;
            }
        }

        /* Validate the webhook signature */
        if(!$signature || empty(settings()->onepay->webhook_signing_key)) {
            http_response_code(400); die();
        }

        $expected_signature = hash_hmac('sha256', $payload, settings()->onepay->webhook_signing_key);
        $expected_signature_base64 = base64_encode(hash_hmac('sha256', $payload, settings()->onepay->webhook_signing_key, true));

        if(!hash_equals($expected_signature, $signature) && !hash_equals($expected_signature_base64, $signature)) {
            http_response_code(400); die();
        }

        /* Decode JSON */
        $data = json_decode($payload);

        if(!$data) {
            die();
        }

        /* Transaction webhook */
        $transaction = $data->transaction ?? $data;

        if(isset($transaction->id, $transaction->status, $transaction->invoice)) {

            if($transaction->status != 'confirmed') {
                die();
            }

            /* Process reference data */
            $reference_id = $transaction->invoice->referenceId ?? ($transaction->referenceId ?? null);

            if(!$reference_id || !str_starts_with($reference_id, 'op|')) {
                die();
            }

            $metadata = explode('|', $reference_id);

            if(count($metadata) < 9) {
                die();
            }

            $payment_frequency = [
                'm' => 'monthly',
                'q' => 'quarterly',
                'b' => 'biannual',
                'a' => 'annual',
                'l' => 'lifetime',
            ][$metadata[3]] ?? $metadata[3];

            $taxes_ids = base64_decode($metadata[8]);

            /* Start getting the payment details */
            $payment_subscription_id = isset($transaction->subscription->id) ? (string) $transaction->subscription->id : null;
            $external_payment_id = $transaction->uuid ?? $transaction->id;
            $payment_currency = $transaction->invoice->currency ?? settings()->payment->default_currency;
            $payment_amount = $transaction->amount ?? ($transaction->invoice->originalAmount ?? 0);
            $payment_total = in_array($payment_currency, get_zero_decimal_currencies_array()) ? $payment_amount : $payment_amount / 100;
            $payment_type = $payment_subscription_id ? 'recurring' : 'one_time';

            /* Payment payer details */
            $payer_email = $transaction->contact->email ?? '';
            $payer_name = trim(($transaction->contact->firstname ?? '') . ' ' . ($transaction->contact->lastname ?? ''));
            $payer_name = $payer_name ?: ($transaction->contact->company ?? '');

            /* Process payment */
            (new Payments())->webhook_process_payment(
                'onepay',
                $external_payment_id,
                $payment_total,
                $payment_currency,
                (int) $metadata[1],
                (int) $metadata[2],
                $payment_frequency,
                rawurldecode($metadata[6]),
                $metadata[7] ?? 0,
                $metadata[5] ?? 0,
                $taxes_ids ?: null,
                $payment_type,
                $payment_subscription_id,
                $payer_email,
                $payer_name
            );

            die('successful');
        }

        /* Subscription webhook */
        $subscription = $data->subscription ?? $data;

        if(isset($subscription->id, $subscription->status, $subscription->invoice, $subscription->paymentInterval)) {

            /* Process reference data */
            $reference_id = $subscription->invoice->referenceId ?? null;

            if(!$reference_id || !str_starts_with($reference_id, 'op|')) {
                die();
            }

            $metadata = explode('|', $reference_id);

            if(count($metadata) < 9) {
                die();
            }

            if(in_array($subscription->status, ['cancelled', 'failed'])) {
                /* Clear cancelled subscriptions */
                db()->where('user_id', (int) $metadata[1])->where('payment_subscription_id', (string) $subscription->id)->update('users', ['payment_subscription_id' => '']);
                cache()->deleteItemsByTag('user_id=' . (int) $metadata[1]);

                die('successful');
            }

            if($subscription->status != 'active') {
                die();
            }

            /* Payment is handled by the transaction webhook */
            die('successful');
        }

        die();

    }

}
