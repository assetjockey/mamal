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

defined('ALTUMCODE') || die();

class WebhookAuditAi extends Controller {

    public function index() {

        if(!settings()->audits->ai_is_enabled || !settings()->audits->openai_api_key || !settings()->audits->openai_webhook_secret_key) {
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

        /* Get signature header */
        $signature_header = $_SERVER['HTTP_WEBHOOK_SIGNATURE'] ?? null;
        $timestamp = $_SERVER['HTTP_WEBHOOK_TIMESTAMP'] ?? null;
        $webhook_id = $_SERVER['HTTP_WEBHOOK_ID'] ?? null;

        if(!$signature_header || !$timestamp || !$webhook_id) {
            http_response_code(400);
            die('Missing required webhook headers');
        }

        [$version, $received_signature] = explode(',', $signature_header, 2);

        if(!$received_signature) {
            http_response_code(400);
            die('Invalid signature format');
        }

        /* Prepare webhook secret */
        $webhook_secret = base64_decode(substr(settings()->audits->openai_webhook_secret_key, 6));

        /* Prepare signed payload for verification */
        $signed_payload = $webhook_id . '.' . $timestamp . '.' . $payload;

        $expected_signature = base64_encode(
            hash_hmac(
                'sha256',
                $signed_payload,
                $webhook_secret,
                true
            )
        );

        if(!hash_equals($expected_signature, $received_signature)) {
            http_response_code(400);
            exit('Invalid signature');
        }

        /* Decode JSON */
        $event = json_decode($payload);

        if(!$event || !isset($event->type)) {
            http_response_code(400);
            die('Invalid event');
        }

        /* Allow only relevant events */
        $allowed_events = [
            'response.completed',
        ];

        if(!in_array($event->type, $allowed_events)) {
            die('Event not needed');
        }

        /* Get audit */
        $audit = db()->where('ai_summary_id', $event->data->id)->getOne('audits', ['audit_id']);

        if(!$audit) {
            http_response_code(400);
            die('Audit does not exist');
        }

        /* Get the result from OpenAi */
        try {
            $response = \Unirest\Request::get(
                settings()->audits->openai_api_url . 'v1/responses/' . $event->data->id,
                [
                    'Authorization' => 'Bearer ' . settings()->audits->openai_api_key,
                    'Content-Type' => 'application/json'
                ]
            );

        } catch (\Exception $exception) {
            error_log('[GET AI AUDIT SUMMARY ERROR]: ' . $exception->getMessage());
            return null;
        }

        if($response->code >= 400) {
            error_log('[GET AI AUDIT SUMMARY ERROR]: ' . $response->raw_body);
            return null;
        }

        /* get response */
        $ai_summary = null;

        foreach($response->body->output as $item) {
            if(($item->type ?? null) !== 'message') {
                continue;
            }

            foreach ($item->content ?? [] as $content) {
                if(($content->type ?? null) === 'output_text') {
                    $ai_summary = $content->text;
                }
            }
        }

        /* Database query */
        db()->where('audit_id', $audit->audit_id)->update('audits', [
            'ai_summary' => $ai_summary,
        ]);

        echo 'successful';

    }
}
