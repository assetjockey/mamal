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

use Altum\Meta;
use Altum\Title;

defined('ALTUMCODE') || die();

class Unsubscribe extends Controller {

    public function index() {

        $token = isset($_POST['token']) ? $_POST['token'] : (isset($_GET['token']) ? $_GET['token'] : null);
        $type = isset($_POST['type']) && $_POST['type'] == 'broadcast_subscriber' ? 'broadcast_subscriber' : (isset($_GET['type']) && $_GET['type'] == 'broadcast_subscriber' ? 'broadcast_subscriber' : 'user');
        $unsubscribe_code = isset($_POST['unsubscribe_code']) && $type == 'broadcast_subscriber' ? input_clean($_POST['unsubscribe_code'], 32) : (isset($_GET['unsubscribe_code']) && $type == 'broadcast_subscriber' ? input_clean($_GET['unsubscribe_code'], 32) : null);

        if(!$token && !$unsubscribe_code) {
            throw_404();
        }

        $broadcast_subscriber = null;

        /* Find subscriber by permanent code */
        if($type == 'broadcast_subscriber' && $unsubscribe_code) {
            $broadcast_subscriber = db()->where('unsubscribe_code', $unsubscribe_code)->getOne('broadcast_subscribers');

            if(!$broadcast_subscriber) {
                throw_404();
            }

            $identifier = $broadcast_subscriber->broadcast_subscriber_id;
            $is_subscribed = $broadcast_subscriber->status == 1;
        }

        else {
            if($type == 'broadcast_subscriber') {
                $secret = hash(
                    'sha256',
                    settings()->license->license . '|' . settings()->cron->key . '|broadcast-subscriber-unsubscribe|v1',
                    true
                );
            }

            else {
                $secret = hash(
                    'sha256',
                    settings()->license->license . '|' . settings()->cron->key . '|list-unsubscribe|v1',
                    true
                );
            }

            $identifier = verify_unsubscribe_token($token, $secret);

            if(!$identifier) {
                throw_404();
            }

            if($type == 'broadcast_subscriber') {
                $broadcast_subscriber = db()->where('broadcast_subscriber_id', $identifier)->getOne('broadcast_subscribers');

                if(!$broadcast_subscriber) {
                    throw_404();
                }

                $is_subscribed = $broadcast_subscriber->status == 1;
            }

            else {
                $user = db()->where('user_id', $identifier)->getOne('users', ['user_id', 'is_broadcast_subscribed']);

                if(!$user) {
                    throw_404();
                }

                $is_subscribed = $user->is_broadcast_subscribed;
                $broadcast_subscriber = db()->where('user_id', $identifier)->getOne('broadcast_subscribers');
            }
        }

        if(!empty($_POST) && $is_subscribed) {
            $datetime = get_date();

            if($type == 'broadcast_subscriber') {
                /* Unsub the subscriber */
                db()->where('broadcast_subscriber_id', $identifier)->update('broadcast_subscribers', [
                    'status' => 2,
                    'unsubscribed_datetime' => $datetime,
                    'last_datetime' => $datetime,
                ]);

                if($broadcast_subscriber->user_id) {
                    db()->where('user_id', $broadcast_subscriber->user_id)->update('users', [
                        'is_broadcast_subscribed' => 0,
                    ]);
                }
            }

            else {
                /* Unsub the user */
                db()->where('user_id', $identifier)->update('users', ['is_broadcast_subscribed' => 0]);

                db()->where('user_id', $identifier)->update('broadcast_subscribers', [
                    'status' => 2,
                    'unsubscribed_datetime' => $datetime,
                    'last_datetime' => $datetime,
                ]);
            }

            /* Send webhook notification if needed */
            if($broadcast_subscriber && $broadcast_subscriber->status == 1 && settings()->webhooks->broadcast_subscriber_unsubscribe) {
                fire_and_forget('post', settings()->webhooks->broadcast_subscriber_unsubscribe, [
                    'broadcast_subscriber_id' => $broadcast_subscriber->broadcast_subscriber_id,
                    'user_id' => $broadcast_subscriber->user_id,
                    'email' => $broadcast_subscriber->email,
                    'name' => $broadcast_subscriber->name,
                    'source' => $broadcast_subscriber->source,
                    'status' => 2,
                    'language' => $broadcast_subscriber->language,
                    'ip' => $broadcast_subscriber->ip,
                    'country_code' => $broadcast_subscriber->country_code,
                    'city_name' => $broadcast_subscriber->city_name,
                    'unsubscribed_datetime' => $datetime,
                    'last_datetime' => $datetime,
                    'datetime' => $broadcast_subscriber->datetime,
                ], signature: true);
            }

            /* Set a custom title */
            Title::set(l('unsubscribe.success.title'));

            $is_subscribed = false;
        }

        /* Meta */
        Meta::set_robots('noindex');

        /* Disable OG Image */
        if(\Altum\Plugin::is_active('dynamic-og-images') && settings()->dynamic_og_images->is_enabled) {
            \Altum\Plugin\DynamicOgImages::$should_process = false;
        }

        /* Prepare the view */
        $data = [
            'user' => (object) ['is_broadcast_subscribed' => $is_subscribed],
            'token' => $token,
            'type' => $type,
            'unsubscribe_code' => $unsubscribe_code,
        ];

        $view = new \Altum\View('unsubscribe/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }


}
