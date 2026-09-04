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

namespace Altum\Traits;

use Altum\Response;

defined('ALTUMCODE') || die();

trait Apiable {
    public $user = null;

    /* Function to check the request authentication */
    private function verify_request($require_to_be_admin = false, $require_to_have_plan_setting = true, $require_to_have_api_enabled = true) {

        if(!settings()->main->api_is_enabled && !$require_to_be_admin && $require_to_have_api_enabled) {
            throw_404();
        }

        /* Define the return content to be treated as JSON */
        header('Content-Type: application/json');

        /* Make sure to check for the Auth header */
        $api_key = \Altum\Authentication::get_authorization_bearer();

        if(!$api_key) {
            Response::jsonapi_error([[
                'title' => l('api.error_message.no_bearer'),
                'status' => '401'
            ]], null, 401);
        }

        /* Get the user data of the API key owner, if any */
        $this->user = \Altum\Cache::cache_function_result('user?api_key=' . $api_key, null, function() use ($api_key) {
            return db()->where('api_key', $api_key)->where('status', 1)->getOne('users');
        });

        if(!$this->user) {
            $this->response_error(l('api.error_message.no_access'), 401);
        }

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) $this->response_error('Please create an account on the demo to test out this function.', 400);

        if($require_to_be_admin && $this->user->type != 1) {
            $this->response_error(l('api.error_message.no_access'), 401);
        }

        $this->user->plan_settings = json_decode($this->user->plan_settings ?? '');
        $this->user->billing = json_decode($this->user->billing ?? '');
        $this->user->preferences = json_decode($this->user->preferences ?? '');

        if($require_to_have_plan_setting && !$require_to_be_admin && !$this->user->plan_settings->api_is_enabled) {
            $this->response_error(l('api.error_message.no_access'), 401);
        }

        /* Rate limiting */
        if($this->user->type != 1) {
            $rate_limit_limit = 60;
            $rate_limit_per_seconds = 60;

            /* Verify the limitation of the bearer */
            $cache_instance = cache()->getItem('api-' . $api_key);

            /* Set cache if not existing */
            if (is_null($cache_instance->get())) {

                /* Initial save */
                $cache_instance->set($rate_limit_limit)->expiresAfter($rate_limit_per_seconds);

            }

            /* Decrement */
            $cache_instance->decrement();

            /* Get the actual value */
            $rate_limit_remaining = $cache_instance->get();

            /* Get the reset time */
            $rate_limit_reset = $cache_instance->getTtl();

            /* Save it */
            cache()->save($cache_instance);

            /* Set the rate limit headers */
            header('X-RateLimit-Limit: ' . $rate_limit_limit);

            if ($rate_limit_remaining >= 0) {
                header('X-RateLimit-Remaining: ' . $rate_limit_remaining);
            }

            if ($rate_limit_remaining < 0) {
                header('X-RateLimit-Reset: ' . $rate_limit_reset);
                $this->response_error(l('api.error_message.rate_limit'), 429);
            }
        }
    }

    private function return_404() {
        $this->response_error(l('api.error_message.not_found'), 404);
    }

    private function response_error($title = '', $response_code = 400) {
        Response::jsonapi_error([[
            'title' => $title,
            'status' => $response_code
        ]], null, $response_code);
    }

}
