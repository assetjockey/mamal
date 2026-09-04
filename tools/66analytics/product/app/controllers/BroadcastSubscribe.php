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

use Altum\Alerts;

defined('ALTUMCODE') || die();

class BroadcastSubscribe extends Controller {

    public function index() {

        if(!settings()->content->broadcasts_is_enabled || (!is_logged_in() && !settings()->content->broadcasts_guests_is_enabled)) {
            throw_404();
        }

        if(!empty($_POST)) {
            $_POST['name'] = input_clean(isset($_POST['name']) ? $_POST['name'] : '', 64);
            $_POST['email'] = input_clean_email(isset($_POST['email']) ? $_POST['email'] : '');
            $_POST['email'] = mb_strtolower($_POST['email']);

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'email'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(isset($_POST['email']) && $_POST['email'] && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                Alerts::add_field_error('email', l('global.error_message.invalid_email'));
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Check existing subscriber */
            $broadcast_subscriber = null;
            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $broadcast_subscriber = db()->where('email', $_POST['email'])->getOne('broadcast_subscribers');
            }

            /* Limit confirmation email requests */
            $cooldown_seconds = 10 * 60;
            if(!Alerts::has_field_errors() && !Alerts::has_errors() && (!is_logged_in() || $_POST['email'] != $this->user->email) && (!$broadcast_subscriber || $broadcast_subscriber->status != 1)) {
                $cooldown_ip_cache = cache()->getItem('broadcast_subscribe_cooldown?ip=' . md5(get_ip()));
                $cooldown_email_cache = cache()->getItem('broadcast_subscribe_cooldown?email=' . md5($_POST['email']));

                if($cooldown_ip_cache->isHit() || $cooldown_email_cache->isHit()) {
                    Alerts::add_error(l('broadcast_subscribe.error_message.cooldown'));
                }
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $is_owned_email = is_logged_in() && $_POST['email'] == $this->user->email;
                $user_id = $is_owned_email ? $this->user->user_id : null;
                $status = $is_owned_email ? 1 : 0;
                $email_activation_code = $status ? null : md5(uniqid('', true) . random_bytes(16));
                $broadcast_subscriber_id = null;
                $is_new_subscription = false;

                /* Already subscribed */
                if($broadcast_subscriber && $broadcast_subscriber->status == 1) {
                    if($is_owned_email) {
                        /* Keep subscriber details fresh */
                        $update_data = [
                            'user_id' => $this->user->user_id,
                            'name' => $_POST['name'],
                            'last_datetime' => get_date(),
                        ];

                        if(!$broadcast_subscriber->unsubscribe_code) {
                            $update_data['unsubscribe_code'] = md5(uniqid('', true) . random_bytes(16));
                        }

                        db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->update('broadcast_subscribers', $update_data);

                        db()->where('user_id', $this->user->user_id)->update('users', [
                            'is_broadcast_subscribed' => 1,
                        ]);
                    }

                    Alerts::add_info(l('broadcast_subscribe.info_message.confirmation'));

                    redirect('broadcast-subscribe');
                }

                /* Update old subscriber */
                if($broadcast_subscriber) {
                    $broadcast_subscriber_id = $broadcast_subscriber->broadcast_subscriber_id;

                    $update_data = [
                        'name' => $_POST['name'],
                        'source' => 'index',
                        'language' => \Altum\Language::$name,
                        'status' => $status,
                        'email_activation_code' => $email_activation_code,
                        'unsubscribed_datetime' => null,
                        'last_datetime' => get_date(),
                    ];

                    if($user_id) {
                        $update_data['user_id'] = $user_id;
                    }

                    if(!$broadcast_subscriber->unsubscribe_code) {
                        $update_data['unsubscribe_code'] = md5(uniqid('', true) . random_bytes(16));
                    }

                    db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->update('broadcast_subscribers', $update_data);

                    $is_new_subscription = $status == 1;
                }

                /* Create new subscriber */
                else {

                    /* Detect the location */
                    try {
                        $maxmind = (get_maxmind_reader_city())->get(get_ip());
                    } catch(\Exception $exception) {
                        /* :) */
                    }
                    $continent_code = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['code'] : null;
                    $country_code = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
                    $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;

                    /* Detect extra details */
                    $whichbrowser = get_whichbrowser();
                    $browser_name = isset($whichbrowser->browser->name) ? $whichbrowser->browser->name : null;
                    $os_name = isset($whichbrowser->os->name) ? $whichbrowser->os->name : null;
                    $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
                    $device_type = get_this_device_type();

                    $broadcast_subscriber_id = db()->insert('broadcast_subscribers', [
                        'user_id' => $user_id,
                        'email' => $_POST['email'],
                        'name' => $_POST['name'],
                        'source' => 'index',
                        'language' => \Altum\Language::$name,
                        'ip' => get_ip(),
                        'continent_code' => $continent_code,
                        'country_code' => $country_code,
                        'city_name' => $city_name,
                        'device_type' => $device_type,
                        'browser_language' => $browser_language,
                        'browser_name' => $browser_name,
                        'os_name' => $os_name,
                        'status' => $status,
                        'email_activation_code' => $email_activation_code,
                        'unsubscribe_code' => md5(uniqid('', true) . random_bytes(16)),
                        'last_datetime' => get_date(),
                        'datetime' => get_date(),
                    ]);

                    $is_new_subscription = $status == 1;
                }

                /* Sync account status */
                if($is_owned_email) {
                    db()->where('user_id', $this->user->user_id)->update('users', [
                        'is_broadcast_subscribed' => 1,
                    ]);
                }

                /* Confirm email ownership */
                if($status == 0) {
                    $email_template = get_email_template(
                        [],
                        l('global.emails.broadcast_subscriber_activation.subject'),
                        [
                            '{{NAME}}' => $_POST['name'],
                            '{{ACTIVATION_LINK}}' => url('broadcast-subscribe/confirm?broadcast_subscriber_id=' . $broadcast_subscriber_id . '&email=' . md5($_POST['email']) . '&email_activation_code=' . $email_activation_code),
                        ],
                        l('global.emails.broadcast_subscriber_activation.body')
                    );

                    send_mail($_POST['email'], $email_template->subject, $email_template->body, ['language' => \Altum\Language::$name]);

                    /* Store confirmation cooldown */
                    if(isset($cooldown_ip_cache) && isset($cooldown_email_cache)) {
                        $cooldown_ip_cache->set(1)->expiresAfter($cooldown_seconds);
                        $cooldown_email_cache->set(1)->expiresAfter($cooldown_seconds);
                        cache()->save($cooldown_ip_cache);
                        cache()->save($cooldown_email_cache);
                    }

                    Alerts::add_info(l('broadcast_subscribe.info_message.confirmation'));
                }

                /* Confirm subscription */
                else {
                    Alerts::add_success(l('broadcast_subscribe.success_message'));

                    if($is_new_subscription && settings()->internal_notifications->admins_is_enabled && settings()->internal_notifications->new_newsletter_subscriber) {
                        db()->insert('internal_notifications', [
                            'for_who' => 'admin',
                            'from_who' => 'system',
                            'icon' => 'fas fa-newspaper',
                            'title' => l('global.notifications.new_newsletter_subscriber.title'),
                            'description' => sprintf(l('global.notifications.new_newsletter_subscriber.description'), $_POST['name'], $_POST['email']),
                            'url' => 'admin/broadcasts',
                            'datetime' => get_date(),
                        ]);
                    }

                    /* Send webhook notification if needed */
                    if($is_new_subscription && settings()->webhooks->broadcast_subscriber_new) {
                        $broadcast_subscriber = db()->where('broadcast_subscriber_id', $broadcast_subscriber_id)->getOne('broadcast_subscribers');

                        if($broadcast_subscriber) {
                            fire_and_forget('post', settings()->webhooks->broadcast_subscriber_new, [
                                'broadcast_subscriber_id' => $broadcast_subscriber->broadcast_subscriber_id,
                                'user_id' => $broadcast_subscriber->user_id,
                                'email' => $broadcast_subscriber->email,
                                'name' => $broadcast_subscriber->name,
                                'source' => $broadcast_subscriber->source,
                                'status' => $broadcast_subscriber->status,
                                'language' => $broadcast_subscriber->language,
                                'ip' => $broadcast_subscriber->ip,
                                'country_code' => $broadcast_subscriber->country_code,
                                'city_name' => $broadcast_subscriber->city_name,
                                'unsubscribed_datetime' => $broadcast_subscriber->unsubscribed_datetime,
                                'last_datetime' => $broadcast_subscriber->last_datetime,
                                'datetime' => $broadcast_subscriber->datetime,
                            ], signature: true);
                        }
                    }
                }

                redirect('broadcast-subscribe');
            }
        }

        $values = [
            'name' => is_logged_in() ? $this->user->name : (isset($_POST['name']) ? $_POST['name'] : ''),
            'email' => is_logged_in() ? $this->user->email : (isset($_POST['email']) ? $_POST['email'] : ''),
        ];

        /* Prepare the view */
        $data = [
            'values' => $values,
        ];

        $view = new \Altum\View('broadcast-subscribe/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }

    public function confirm() {

        if(!settings()->content->broadcasts_is_enabled) {
            throw_404();
        }

        /* Check for any errors */
        $required_fields = ['broadcast_subscriber_id', 'email', 'email_activation_code'];
        foreach($required_fields as $field) {
            if(!isset($_GET[$field]) || trim($_GET[$field]) === '') {
                throw_404();
            }
        }

        $broadcast_subscriber_id = (int) $_GET['broadcast_subscriber_id'];
        $email = input_clean($_GET['email'], 32);
        $email_activation_code = input_clean($_GET['email_activation_code'], 32);

        if(!$broadcast_subscriber = db()->where('broadcast_subscriber_id', $broadcast_subscriber_id)->where('email_activation_code', $email_activation_code)->getOne('broadcast_subscribers')) {
            throw_404();
        }

        if(md5($broadcast_subscriber->email) != $email) {
            throw_404();
        }

        /* Do not bypass account confirmation */
        if($broadcast_subscriber->user_id) {
            $user = db()->where('user_id', $broadcast_subscriber->user_id)->getOne('users', ['user_id', 'status']);

            if($user && $user->status == 0) {
                throw_404();
            }
        }

        /* Activate subscription */
        $update_data = [
            'status' => 1,
            'email_activation_code' => null,
            'unsubscribed_datetime' => null,
            'last_datetime' => get_date(),
        ];

        if(!$broadcast_subscriber->unsubscribe_code) {
            $update_data['unsubscribe_code'] = md5(uniqid('', true) . random_bytes(16));
        }

        db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->update('broadcast_subscribers', $update_data);

        if($broadcast_subscriber->user_id) {
            db()->where('user_id', $broadcast_subscriber->user_id)->update('users', [
                'is_broadcast_subscribed' => 1,
            ]);
        }

        if(settings()->internal_notifications->admins_is_enabled && settings()->internal_notifications->new_newsletter_subscriber) {
            db()->insert('internal_notifications', [
                'for_who' => 'admin',
                'from_who' => 'system',
                'icon' => 'fas fa-newspaper',
                'title' => l('global.notifications.new_newsletter_subscriber.title'),
                'description' => sprintf(l('global.notifications.new_newsletter_subscriber.description'), $broadcast_subscriber->name, $broadcast_subscriber->email),
                'url' => 'admin/broadcasts',
                'datetime' => get_date(),
            ]);
        }

        /* Send webhook notification if needed */
        if(settings()->webhooks->broadcast_subscriber_new) {
            $broadcast_subscriber = db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->getOne('broadcast_subscribers');

            if($broadcast_subscriber) {
                fire_and_forget('post', settings()->webhooks->broadcast_subscriber_new, [
                    'broadcast_subscriber_id' => $broadcast_subscriber->broadcast_subscriber_id,
                    'user_id' => $broadcast_subscriber->user_id,
                    'email' => $broadcast_subscriber->email,
                    'name' => $broadcast_subscriber->name,
                    'source' => $broadcast_subscriber->source,
                    'status' => $broadcast_subscriber->status,
                    'language' => $broadcast_subscriber->language,
                    'ip' => $broadcast_subscriber->ip,
                    'country_code' => $broadcast_subscriber->country_code,
                    'city_name' => $broadcast_subscriber->city_name,
                    'unsubscribed_datetime' => $broadcast_subscriber->unsubscribed_datetime,
                    'last_datetime' => $broadcast_subscriber->last_datetime,
                    'datetime' => $broadcast_subscriber->datetime,
                ], signature: true);
            }
        }

        Alerts::add_success(l('broadcast_subscribe.success_message_confirmed'));

        redirect('broadcast-subscribe');
    }

}
