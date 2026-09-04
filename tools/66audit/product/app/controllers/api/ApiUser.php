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

use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiUser extends Controller {
    use Apiable;

    public function index() {

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->get();
                break;
        }

        $this->return_404();
    }

    public function get() {

        /* Prepare the data */
        $data = [
            'id' => (int) $this->user->user_id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'language' => $this->user->language,
            'timezone' => $this->user->timezone,
            'anti_phishing_code' => (bool) $this->user->anti_phishing_code,
            'is_newsletter_subscribed' => (bool) $this->user->is_newsletter_subscribed,
            'billing' => $this->user->billing,
            'status' => (bool) $this->user->status,
            'plan_id' => $this->user->plan_id,
            'plan_expiration_date' => $this->user->plan_expiration_date,
            'plan_settings' => $this->user->plan_settings,
            'plan_trial_done' => (bool) $this->user->plan_trial_done,
            'payment_processor' => $this->user->payment_processor,
            'payment_total_amount' => $this->user->payment_total_amount,
            'payment_currency' => $this->user->payment_currency,
            'payment_subscription_id' => $this->user->payment_subscription_id,
            'source' => $this->user->source,
            'ip' => $this->user->ip,
            'latitude' => $this->user->latitude,
            'longitude' => $this->user->longitude,
            'continent_code' => $this->user->continent_code,
            'country' => $this->user->country,
            'city_name' => $this->user->city_name,
            'os_name' => $this->user->os_name,
            'browser_name' => $this->user->browser_name,
            'browser_language' => $this->user->browser_language,
            'device_type' => $this->user->device_type,
            'api_key' => $this->user->api_key,
            'referral_key' => $this->user->referral_key,
            'referred_by' => $this->user->referred_by,
            'last_activity' => $this->user->last_activity,
            'total_logins' => (int) $this->user->total_logins,
            'datetime' => $this->user->datetime,
            'next_cleanup_datetime' => $this->user->next_cleanup_datetime,
        ];

        Response::jsonapi_success($data);
    }
}
