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
namespace Altum\Models;

use Altum\Logger;
use Altum\PaymentGateways\Lemonsqueezy;
use Altum\PaymentGateways\Onepay;
use Altum\PaymentGateways\Paystack;
use Razorpay\Api\Api;

defined('ALTUMCODE') || die();

class User extends Model {

    public function get_user_by_user_id($user_id) {

        /* Try to check if the store posts exists via the cache */
        $cache_instance = cache()->getItem('user?user_id=' . $user_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $data = db()->where('user_id', $user_id)->getOne('users');

            if($data) {

                /* Parse the users plan settings */
                $data->plan_settings = json_decode($data->plan_settings ?? '');

                /* Parse billing details if existing */
                $data->billing = json_decode($data->billing ?? '');

                /* Parse preferences if existing */
                $data->preferences = json_decode($data->preferences ?? '');

                /* Save to cache */
                cache()->save(
                    $cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $data->user_id)
                );
            }

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;
    }



    /* Requires full user variable */
    public function process_user_plan_expiration_by_user($user) {

        if((new \DateTime($user->plan_expiration_date)) < (new \DateTime()) && $user->plan_id != 'free') {

            /* Switch the user to the default plan */
            db()->where('user_id', $user->user_id)->update('users', [
                'plan_id' => 'free',
                'plan_settings' => json_encode(settings()->plan_free->settings),
                'payment_subscription_id' => '',
                'payment_processor' => '',
                'payment_total_amount' => 0,
                'payment_currency' => '',
            ]);

            /* Clear the cache */
            cache()->deleteItemsByTag('user_id=' . $user->user_id);
        }

    }

    public function delete($user_id) {

        $user = db()->where('user_id', $user_id)->getOne('users', ['user_id', 'email', 'name', 'avatar', 'preferences']);

        if(!$user) return;


        $user->preferences = json_decode($user->preferences ?? '');

        /* Cancel his active subscriptions if active */
        try {
            $this->cancel_subscription($user_id);
        } catch (\Exception $exception) {
            // :)
        }

        /* Send webhook notification if needed */
        if(settings()->webhooks->user_delete) {
            fire_and_forget('post', settings()->webhooks->user_delete, [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'name' => $user->name,
                'datetime' => get_date(),
            ], signature: true);
        }

        /* Run potential hooks */
        \Altum\CustomHooks::user_delete(['user' => $user]);

        /* Delete the record from the database */
        db()->where('user_id', $user_id)->delete('users');

        /* Clear the cache */
        cache()->deleteItemsByTag('user_id=' . $user_id);

    }

    public function update_last_activity($user_id) {
        db()->where('user_id', $user_id)->update('users', ['last_activity' => get_date()]);
        /* Clear the cache */
        cache()->deleteItemsByTag('user_id=' . $user_id);
    }

    public function verify_null_password($user_id, $email, $password) {
        if(empty($password)) {
            $lost_password_code = $lost_password_code ?? md5(uniqid('', true) . random_bytes(16));
            db()->where('user_id', $user_id)->update('users', ['lost_password_code' => $lost_password_code]);
            redirect('reset-password/' . md5($email) . '/' . $lost_password_code);
        }

        return;
    }

    public function create(
        $email = '',
        $raw_password = '',
        $name = '',
        $status = 0,
        $source = null,
        $email_activation_code = null,
        $lost_password_code = null,
        $is_broadcast_subscribed = 0,
        $plan_id = 'free',
        $plan_settings = '',
        $plan_expiration_date = null,
        $timezone = 'UTC',
        $extra = '',
        $is_admin_created = false
    ) {

        /* Define some needed variables */
        $password = is_null($raw_password) ? null : password_hash($raw_password, PASSWORD_DEFAULT);
        $total_logins = $status == '1' && !$is_admin_created && !in_array($source, ['admin_create', 'admin_api_create', 'facebook', 'google', 'twitter', 'discord', 'linkedin', 'microsoft', 'apple', 'github']) ? 1 : 0;
        $plan_expiration_date = $plan_expiration_date ?? get_date();
        $plan_trial_done = 0;
        $language = \Altum\Language::$name;
        $api_key = bin2hex(random_bytes(16));
        $referral_key = md5(uniqid('', true) . random_bytes(16));
        $ip = $is_admin_created ? null : get_ip();

        /* Detect the location */
        try {
            $maxmind = $is_admin_created ? null : (get_maxmind_reader_city())->get($ip);
        } catch(\Exception $exception) {
            /* :) */
        }
        $continent_code = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['code'] : null;
        $country_code = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
        $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;
        $latitude = isset($maxmind) && isset($maxmind['location']) ? $maxmind['location']['latitude'] : null;
        $longitude = isset($maxmind) && isset($maxmind['location']) ? $maxmind['location']['longitude'] : null;

        /* Billing */
        $billing = json_encode(['type' => 'personal', 'name' => '', 'address' => '', 'city' => '', 'county' => '', 'zip' => '', 'country' => $country_code, 'phone' => '', 'tax_id' => '', 'notes' => '']);

        /* Detect extra details about the user */
        $whichbrowser = get_whichbrowser();
        $browser_name = $whichbrowser->browser->name ?? null;
        $os_name = $whichbrowser->os->name ?? null;
        $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
        $device_type = get_this_device_type();

        /* Only active users get active subscriptions */
        $is_active_broadcast_subscription = $status == 1 && $is_broadcast_subscribed;

        /* Check for potential referral cookie */
        $referred_by = null;
        if(!$is_admin_created && isset($_COOKIE['referred_by']) && $user = db()->where('referral_key', $_COOKIE['referred_by'])->getOne('users', ['user_id', 'referral_key'])) {
            $referred_by = $user->user_id;
        }

        /* Default preferences */
        $preferences = json_encode(\Altum\CustomHooks::return_default_user_preferences());

        /* Add the user to the database */
        $registered_user_id = db()->insert('users', [
            'password' => $password,
            'email' => $email,
            'name' => $name,
            'billing' => $billing,
            'api_key' => $api_key,
            'email_activation_code' => $email_activation_code,
            'lost_password_code' => $lost_password_code,
            'is_broadcast_subscribed' => (int) $is_active_broadcast_subscription,
            'plan_id' => $plan_id,
            'plan_expiration_date' => $plan_expiration_date,
            'plan_settings' => $plan_settings,
            'plan_trial_done' => $plan_trial_done,
            'referral_key' => $referral_key,
            'referred_by' => $referred_by,
            'language' => $language,
            'timezone' => $timezone,
            'status' => $status,
            'source' => $source,
            'datetime' => get_date(),
            'ip' => $ip,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'continent_code' => $continent_code,
            'country' => $country_code,
            'city_name' => $city_name,
            'device_type' => $device_type,
            'os_name' => $os_name,
            'browser_name' => $browser_name,
            'browser_language' => $browser_language,
            'total_logins' => $total_logins,
            'extra' => json_encode($extra),
            'preferences' => $preferences,
        ]);

        /* Manage broadcast subscription */
        if(
            settings()->content->broadcasts_is_enabled
            && $is_broadcast_subscribed
        ) {
            /* Subscribe */
            $broadcast_subscriber = db()->where('email', $email)->getOne('broadcast_subscribers');
            $broadcast_subscriber_status = $status == 1 || ($broadcast_subscriber && $broadcast_subscriber->status == 1) ? 1 : 0;
            $is_new_subscription = $broadcast_subscriber_status == 1 && (!$broadcast_subscriber || $broadcast_subscriber->status != 1);

            if($broadcast_subscriber) {
                $broadcast_subscriber_id = $broadcast_subscriber->broadcast_subscriber_id;

                /* Update subscriber status */
                $broadcast_subscriber_update_data = [
                    'user_id' => $registered_user_id,
                    'name' => $name,
                    'status' => $broadcast_subscriber_status,
                    'email_activation_code' => null,
                    'unsubscribed_datetime' => null,
                    'last_datetime' => get_date(),
                ];

                if(!$broadcast_subscriber->unsubscribe_code) {
                    $broadcast_subscriber_update_data['unsubscribe_code'] = md5(uniqid('', true) . random_bytes(16));
                }

                db()->where('broadcast_subscriber_id', $broadcast_subscriber->broadcast_subscriber_id)->update('broadcast_subscribers', $broadcast_subscriber_update_data);
            } else {
                $broadcast_subscriber_id = db()->insert('broadcast_subscribers', [
                    'user_id' => $registered_user_id,
                    'email' => $email,
                    'name' => $name,
                    'source' => 'register',
                    'language' => $language,
                    'ip' => $ip,
                    'continent_code' => $continent_code,
                    'country_code' => $country_code,
                    'city_name' => $city_name,
                    'device_type' => $device_type,
                    'browser_language' => $browser_language,
                    'browser_name' => $browser_name,
                    'os_name' => $os_name,
                    'status' => $broadcast_subscriber_status,
                    'email_activation_code' => null,
                    'unsubscribe_code' => md5(uniqid('', true) . random_bytes(16)),
                    'unsubscribed_datetime' => null,
                    'last_datetime' => get_date(),
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

        /* Clear out referral cookie if needed */
        if($referred_by) {
            setcookie('referred_by', '', time()-30, COOKIE_PATH);
        }

        \Altum\CustomHooks::user_finished_registration(['user_id' => $registered_user_id, 'email' => $email, 'plan_settings' => $plan_settings]);

        return [
            'user_id' => $registered_user_id,
            'password' => $password,
            'source' => $source,
            'ip' => $ip,
            'country' => $country_code,
            'city_name' => $city_name,
            'device_type' => $device_type,
            'os_name' => $os_name,
            'browser_name' => $browser_name,
        ];
    }

    /*
    * Function to update a user with more details on a login action
    */
    public function login_aftermath_update($user_id, $method = 'classic') {

        $ip = get_ip();


        setcookie('spotlight_has_results', '', time()-30, COOKIE_PATH);

        /* Detect the location */
        try {
            $maxmind = (get_maxmind_reader_city())->get($ip);
        } catch(\Exception $exception) {
            /* :) */
        }
        $continent_code = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['code'] : null;
        $country_name = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
        $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;
        $latitude = isset($maxmind) && isset($maxmind['location']) ? $maxmind['location']['latitude'] : null;
        $longitude = isset($maxmind) && isset($maxmind['location']) ? $maxmind['location']['longitude'] : null;

        /* Detect extra details about the user */
        $whichbrowser = get_whichbrowser();
        $browser_name = $whichbrowser->browser->name ?? null;
        $os_name = $whichbrowser->os->name ?? null;
        $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
        $device_type = get_this_device_type();

        /* Database query */
        db()->where('user_id', $user_id)->update('users', [
            'ip' => $ip,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'continent_code' => $continent_code,
            'country' => $country_name,
            'city_name' => $city_name,
            'device_type' => $device_type,
            'os_name' => $os_name,
            'browser_name' => $browser_name,
            'browser_language' => $browser_language,
            'total_logins' => db()->inc(),
            'user_deletion_reminder' => 0,
        ]);

        Logger::users($user_id, 'login.' . $method . '.success');

		/* Run potential hooks */
		\Altum\CustomHooks::user_finished_login();

        /* Clear the cache */
        cache()->deleteItemsByTag('user_id=' . $user_id);

    }

    public function cancel_subscription($user_id) {

        $user = db()->where('user_id', $user_id)->getOne('users', ['user_id', 'payment_subscription_id', 'payment_processor']);

        if(empty($user->payment_subscription_id)) {
            return true;
        }

        switch($user->payment_processor) {
            case 'stripe':

                /* Initiate Stripe */
                \Stripe\Stripe::setApiKey(settings()->stripe->secret_key);
                \Stripe\Stripe::setApiVersion('2023-10-16');

                /* Cancel the Stripe Subscription */
                $subscription = \Stripe\Subscription::retrieve($user->payment_subscription_id);
                $subscription->cancel();

                break;

            case 'paypal':

                $paypal_api_url = \Altum\PaymentGateways\Paypal::get_api_url();
                $headers = \Altum\PaymentGateways\Paypal::get_headers();

                $response = \Unirest\Request::post($paypal_api_url . 'v1/billing/subscriptions/' . $user->payment_subscription_id . '/cancel', $headers, \Unirest\Request\Body::json([
                    'reason' => sprintf(l('account_plan.cancel.reason'), settings()->main->title)
                ]));

                /* Check against errors */
                if($response->code >= 400) {
                    throw new \Exception($response->body->name . ':' . $response->body->message);
                }

                break;

            case 'paystack':

                Paystack::$secret_key = settings()->paystack->secret_key;

                $payment_subscription_id = explode('###', $user->payment_subscription_id);
                $code = $payment_subscription_id[0];
                $token = $payment_subscription_id[1];

                $response = \Unirest\Request::post(Paystack::$api_url . 'subscription/disable', Paystack::get_headers(), \Unirest\Request\Body::json([
                    'code' => $code,
                    'token' => $token,
                ]));

                if(!$response->body->status) {
                    throw new \Exception($response->body->message);
                }

                break;

            case 'razorpay':

                $razorpay = new Api(settings()->razorpay->key_id, settings()->razorpay->key_secret);

                $response = $razorpay->subscription->fetch($user->payment_subscription_id)->cancel();

                break;

            case 'mollie':

                $mollie = new \Mollie\Api\MollieApiClient();
                $mollie->setApiKey(settings()->mollie->api_key);

                $payment_subscription_id = explode('###', $user->payment_subscription_id);
                $customer_id = $payment_subscription_id[0];
                $subscription_id = $payment_subscription_id[1];

                $mollie->subscriptions->cancelForId($customer_id, $subscription_id);

                break;

            case 'onepay':

                /* Cancel the 1pay subscription */
                $response = \Unirest\Request::delete(
                    Onepay::$api_url . 'Subscription/' . $user->payment_subscription_id . '/?instance=' . rawurlencode(settings()->onepay->instance_name),
                    Onepay::get_headers()
                );

                /* Check against errors */
                if($response->code >= 400) {
                    throw new \Exception($response->raw_body ?: print_r($response->body, true));
                }

                break;

            case 'flutterwave':

                $response = \Unirest\Request::put(
                    'https://api.flutterwave.com/v3/subscriptions/' . $user->payment_subscription_id . '/cancel',
                    [
                        'Authorization' => 'Bearer ' . settings()->flutterwave->secret_key,
                        'Content-Type' => 'application/json',
                    ],
                );

                /* Check against errors */
                if($response->code >= 400) {
                    throw new \Exception($response->body->message);
                }

                break;

            case 'lemonsqueezy':

                Lemonsqueezy::$api_key = settings()->lemonsqueezy->api_key;

                $response = \Unirest\Request::delete(
                    Lemonsqueezy::$api_url . 'subscriptions/' . $user->payment_subscription_id,
                    Lemonsqueezy::get_headers()
                );

                /* Check against errors */
                if($response->code >= 400) {
                    throw new \Exception($response->body);
                }

                break;

            case 'paddle_billing':

                /* Paddle API setup */
                $paddle_api_url = 'https://' . (settings()->paddle_billing->mode == 'sandbox' ? 'sandbox-api' : 'api') . '.paddle.com/';
                $paddle_headers = [
                    'Authorization' => 'Bearer ' . settings()->paddle_billing->api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ];

                /* Cancel the subscription */
                $response = \Unirest\Request::post(
                    $paddle_api_url . 'subscriptions/' . $user->payment_subscription_id . '/cancel',
                    $paddle_headers,
                    \Unirest\Request\Body::json([
                        'effective_from' => 'next_billing_period',
                    ])
                );

                break;
        }

        /* Database query */
        db()->where('user_id', $user->user_id)->update('users', ['payment_subscription_id' => '']);

        /* Clear the cache */
        cache()->deleteItemsByTag('user_id=' . $user->user_id);

    }

}
