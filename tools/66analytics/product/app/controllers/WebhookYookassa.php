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
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\NotificationEventType;

defined('ALTUMCODE') || die();

class WebhookYookassa extends Controller {

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

        $yookassa = new \YooKassa\Client();
        $yookassa->setAuth(settings()->yookassa->shop_id, settings()->yookassa->secret_key);

        $data = json_decode($payload, true);

        try {
            $notification = new NotificationSucceeded($data);
        } catch (\Exception $exception) {
            http_response_code(400); die($exception->getCode() . ':' . $exception->getMessage());
        }

        if($notification->getEvent() == NotificationEventType::PAYMENT_SUCCEEDED) {
            $payment_subscription_id = null;

            $external_payment_id = $notification->getObject()->getId();
            $payment = $yookassa->getPaymentInfo($external_payment_id);

            if($payment->getStatus() !== 'succeeded') {
                http_response_code(400);
                die('Payment not succeeded');
            }

            $payment_total = $payment->getAmount()->getValue();
            $payment_currency = $payment->getAmount()->getCurrency();
            $payment_type = $payment_subscription_id ? 'recurring' : 'one_time';

            $payer_email = '';
            $payer_name = '';

            $metadata = $payment->getMetadata();
            $user_id = (int) $metadata->user_id;
            $plan_id = (int) $metadata->plan_id;
            $payment_frequency = $metadata->payment_frequency;
            $code = isset($metadata->code) ? $metadata->code : '';
            $discount_amount = isset($metadata->discount_amount) ? $metadata->discount_amount : 0;
            $base_amount = isset($metadata->base_amount) ? $metadata->base_amount : 0;
            $taxes_ids = isset($metadata->taxes_ids) ? $metadata->taxes_ids : null;

            (new Payments())->webhook_process_payment(
                'yookassa',
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

            die('successful');
        }

        die();
    }

}
