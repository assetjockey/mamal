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

use Altum\Alerts;
use Altum\Captcha;
use Altum\Meta;
use Altum\Models\User;
use Altum\Title;
use Altum\Uploads;

defined('ALTUMCODE') || die();

class TransferRequest extends Controller {
    public $transfer_request = null;
    public $transfer_request_user = null;

    public function index() {

        if(!settings()->transfers->transfer_requests_is_enabled) {
            throw_404();
        }

        /* Get the URL */
        $transfer_request_url = isset($this->params[0]) ? $this->params[0] : null;

        if(!$transfer_request_url) {
            throw_404();
        }

        /* Make sure there are no extra URL additions */
        if(isset($this->params[1])) {
            throw_404();
        }

        /* Try to check if the link exists via the cache */
        $cache_instance = cache()->getItem('r_transfer_request?url=' . md5($transfer_request_url) . (isset(\Altum\Router::$data['domain']) ? '&domain_id=' . \Altum\Router::$data['domain']->domain_id : null));

        /* Set cache if not existing */
        if(!$cache_instance->get()) {

            /* Get data from the database */
            if (isset(\Altum\Router::$data['domain'])) {
                $transfer_request = db()->where('url', $transfer_request_url)->where('domain_id', \Altum\Router::$data['domain']->domain_id)->getOne('transfer_requests');
                if ($transfer_request) $transfer_request->full_url = \Altum\Router::$data['domain']->scheme . \Altum\Router::$data['domain']->host . '/r/' . $transfer_request_url . '/';
            } else {
                $transfer_request = db()->where('url', $transfer_request_url)->where('domain_id', NULL, 'IS')->getOne('transfer_requests');
                if ($transfer_request) $transfer_request->full_url = SITE_URL . 'r/' . $transfer_request_url . '/';
            }

            /* Save cache */
            if ($transfer_request) {
                cache()->save($cache_instance->set($transfer_request)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('transfer_request_id=' . $transfer_request->transfer_request_id));
            }
        } else {
            /* Get cache */
            $transfer_request = $cache_instance->get();
        }


        /* Make sure the transfer exists and is enabled */
        if(!$transfer_request || !$transfer_request->is_enabled) {
            if (isset(\Altum\Router::$data['domain']) && \Altum\Router::$data['domain']->custom_not_found_url) {
                header('Location: ' . \Altum\Router::$data['domain']->custom_not_found_url);
                die();
            } else {
                throw_404();
            }
        }

        /* Set it controller wide */
        $this->transfer_request = $transfer_request;

        /* Parse some details */
        foreach(['settings', 'pixels_ids', 'notifications'] as $key) {
            $this->transfer_request->{$key} = json_decode($this->transfer_request->{$key} ?? '');
        }

        /* Check for expiration */
        if($transfer_request->expiration_datetime && (new \DateTime()) >= (new \DateTime($transfer_request->expiration_datetime))) {
            Alerts::add_info(l('r_transfer_request.expired.info_message'));
            throw_404();
        }

        /* Initiate captcha */
        $captcha = new Captcha();

        /* Check if the user has access to the link */
        $has_access =
            !$this->transfer_request->settings->password ||
            (
                $this->transfer_request->settings->password
                && isset($_COOKIE['transfer_request_password_' . $this->transfer_request->transfer_request_id])
                && $_COOKIE['transfer_request_password_' . $this->transfer_request->transfer_request_id] == $this->transfer_request->settings->password
                && session_has('transfer_request_password_' . $this->transfer_request->transfer_request_id)
            );

        if($this->transfer_request->user_id) {
            $this->transfer_request_user = (new User())->get_user_by_user_id($this->transfer_request->user_id);

            /* Make sure to check if the user is active */
            if($this->transfer_request_user->status != 1) {
                throw_404();
            }

            /* Process the plan of the user */
            (new User())->process_user_plan_expiration_by_user($this->transfer_request_user);

            /* Do not let the user have password protection if the plan doesn't allow it */
            if(!$this->transfer_request_user->plan_settings->password_protection_is_enabled) {
                $has_access = true;
            }

            /* Set the default language of the user, including the link timezone */
            \Altum\Language::set_by_name($this->transfer_request_user->language);

            /* White label */
            if(settings()->main->white_labeling_is_enabled && $this->transfer_request_user->plan_settings->white_labeling_is_enabled && \Altum\Router::$controller_key != 'invoice' && \Altum\Router::$path != 'admin') {
                if($this->transfer_request_user->preferences->white_label_title) {
                    settings()->main->title = $this->transfer_request_user->preferences->white_label_title;
                    Title::initialize(settings()->main->title);
                }

                if($this->transfer_request_user->preferences->white_label_logo_light) {
                    settings()->main->logo_light = $this->transfer_request_user->preferences->white_label_logo_light;
                    settings()->main->logo_light_full_url = \Altum\Uploads::get_full_url('users') . settings()->main->logo_light;
                }

                if($this->transfer_request_user->preferences->white_label_logo_dark) {
                    settings()->main->logo_dark = $this->transfer_request_user->preferences->white_label_logo_dark;
                    settings()->main->logo_dark_full_url = \Altum\Uploads::get_full_url('users') . settings()->main->logo_dark;
                }

                if($this->transfer_request_user->preferences->white_label_favicon) {
                    settings()->main->favicon = $this->transfer_request_user->preferences->white_label_favicon;
                    settings()->main->favicon_full_url = \Altum\Uploads::get_full_url('users') . settings()->main->favicon;
                }
            }
        }

        /* Check if the password form is submitted */
        if(!$has_access && !empty($_POST) && isset($_POST['type']) && $_POST['type'] == 'password') {
            $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);

            /* Check for any errors */
            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!password_verify($_POST['password'], $this->transfer_request->settings->password)) {
                Alerts::add_field_error('password', l('r_transfer_request.password.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                /* Set a cookie */
                setcookie('transfer_request_password_' . $this->transfer_request->transfer_request_id, $this->transfer_request->settings->password, time()+60*60*24*30);

                /* Set a session */
                session_set('transfer_request_password_' . $this->transfer_request->transfer_request_id, $_POST['password']);

                header('Location: ' . $this->transfer_request->full_url);

                die();
            }
        }

        /* Display the password form */
        if(!$has_access) {
            /* Set a custom title */
            Title::set(l('r_transfer_request.password.title'));

            /* Main View */
            $data = [];

            $view = new \Altum\View('r/partials/password', (array) $this);
            $this->add_view_content('content', $view->run($data));
        }

        /* No password or access granted, display transfer details */
        else {

            $this->create_statistics();

			if(!session_has('transfer_request_pageview_' . $this->transfer_request->transfer_request_id)) {
				$this->process_notification_handlers();
				session_set('transfer_request_pageview_' . $this->transfer_request->transfer_request_id, true);
			}

            $this->process_pixels();

            /* Set a custom title */
            Title::set(sprintf(l('r_transfer_request.submission.title'), $this->transfer_request->name));
            Meta::set_canonical_url($this->transfer_request->full_url);

            /* Override the guest */
            $this->user->plan_settings->files_per_transfer_limit = $this->transfer_request_user->plan_settings->files_per_transfer_limit;
            $this->user->plan_settings->transfer_size_limit = $this->transfer_request_user->plan_settings->transfer_size_limit;

            /* Main View */
            $data = [
                'transfer_request' => $this->transfer_request,
                'transfer_request_user' => $this->transfer_request_user,
                'captcha' => $captcha,
            ];

            $view = new \Altum\View('r/partials/submission', (array) $this);
            $this->add_view_content('content', $view->run($data));

        }

    }

    private function process_notification_handlers() {
        if(!$this->transfer_request->user_id) {
            return;
        }

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->transfer_request->user_id);

        /* Detect extra details about the user */
        $whichbrowser = get_whichbrowser();
        $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
        $browser_name = $whichbrowser->browser->name ?? null;
        $os_name = $whichbrowser->os->name ?? null;
        $device_type = get_this_device_type();

        /* Detect the location */
        try {
            $maxmind = (get_maxmind_reader_city())->get(get_ip());
        } catch(\Exception $exception) { /* :) */ }

        $continent_code = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['code'] : null;
        $country_code = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
        $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;

        /* Core data sent to the NotificationHandlers processor */
        $notification_data = [
            'transfer_request_id' => $this->transfer_request->transfer_request_id,
            'name' => $this->transfer_request->name,
            'continent_code'    => $continent_code,
            'country_code' => $country_code ? get_country_from_country_code($country_code) : null,
            'city_name' => $city_name,
            'device_type' => l('global.device.' . $device_type, $this->transfer_request_user->language),
            'os_name' => $os_name,
            'browser_name' => $browser_name,
            'browser_language'  => $browser_language,
            'url' => url('transfer-request/' . $this->transfer_request->transfer_request_id),
        ];

        /* Build the HTML list for the e-mail body */
        $email_body = \Altum\NotificationHandlers::build_dynamic_message_data(
            $notification_data,
            'transfers',
            \Altum\NotificationHandlers::DYNAMIC_DATA_FORMAT_HTML,
            $this->transfer_request_user->language,
            $this->transfer_request_user->timezone,
            emoji: false
        );

        /* Prepare the email template used by the email handler */
        $email_template = get_email_template(
            [
                '{{TRANSFER_REQUEST_NAME}}' => $this->transfer_request->name,
            ],
            l('global.emails.transfer_request_pageview.subject', $this->transfer_request_user->language),
            [
                '{{NAME}}' => $this->transfer_request_user->name,
                '{{TRANSFER_REQUEST_LINK}}' => $notification_data['url'],
                '{{TRANSFER_REQUEST_NAME}}' => $this->transfer_request->name,
                '{{DATA}}' => nl2br($email_body),
            ],
            l('global.emails.transfer_request_pageview.body', $this->transfer_request_user->language)
        );

        /* Build the context passed to the NotificationHandlers class */
        $context = [
            /* User details */
            'user' => $this->transfer_request_user,
            'language' => $this->transfer_request_user->language,
            'timezone' => $this->transfer_request_user->timezone,

            /* Email */
            'email_template' => $email_template,

            /* Dynamic data */
            'dynamic_data_type' => 'transfers',
            'message_template' => l('r_transfer_request.pageview.simple_notification', $this->transfer_request_user->language),
            'message_template_arguments' => [
                $this->transfer_request->name,
                '{{DYNAMIC_DATA}}',
                $notification_data['url'],
            ],

            /* Push notifications */
            'push_title' => l('r_transfer_request.pageview.push_notification.title', $this->transfer_request_user->language),
            'push_description' => sprintf(
                l('r_transfer_request.pageview.push_notification.description', $this->transfer_request_user->language),
                $this->transfer_request->name
            ),

            /* WhatsApp */
            'whatsapp_template' => 'transfer_request_viewed',
            'whatsapp_parameters' => [
                $this->transfer_request->name,
                $notification_data['url'],
            ],

            /* Twilio call */
            'twilio_call_url' => SITE_URL .
                'twiml/r_transfer_request.pageview.simple_notification?param1=' .
                urlencode($this->transfer_request->name) .
                '&param2=' . urlencode($notification_data['url']),

            /* Internal notification */
            'internal_icon' => 'fas fa-eye',

            /* Discord */
            'discord_color' => '2664261',

            /* Slack */
            'slack_emoji' => ':eye:',
        ];

        /* Send notifications */
        \Altum\NotificationHandlers::process(
            $notification_handlers,
            $this->transfer_request->notifications->pageview ?? [],
            $notification_data,
            $context
        );
    }

    private function process_pixels() {
        if(count($this->transfer_request->pixels_ids ?? [])) {
            /* Get the needed pixels */
            $pixels = (new \Altum\Models\Pixel())->get_pixels_by_pixels_ids_and_user_id($this->transfer_request->pixels_ids, $this->transfer_request->user_id);

            /* Prepare the pixels view */
            $pixels_view = new \Altum\View('r/partials/pixels');
            $this->add_view_content('pixels', $pixels_view->run(['pixels' => $pixels]));
        }
    }

    /* Insert statistics log */
    private function create_statistics() {

        $cookie_name = 'transfer_request_statistics_' . $this->transfer_request->transfer_request_id;

        if(isset($_COOKIE[$cookie_name]) && (int) $_COOKIE[$cookie_name] >= 3) {
            return;
        }

        /* Add the unique hit to the transfers table */
        db()->where('transfer_request_id', $this->transfer_request->transfer_request_id)->update('transfer_requests', ['pageviews' => db()->inc()]);

        /* Do not record advanced analytics if the plan does not allow */
        if(!$this->transfer_request_user || !$this->transfer_request_user->plan_settings->analytics_is_enabled) {
            return;
        }

        /* Detect extra details about the user */
        $whichbrowser = get_whichbrowser();

        /* Do not track bots */
        if($whichbrowser->device->type == 'bot') {
            return;
        }

        /* Ignore excluded ips */
        $excluded_ips = array_flip($this->transfer_request_user->preferences->excluded_ips ?? []);
        if(isset($excluded_ips[get_ip()])) return;

        /* Detect extra details about the user */
        $browser_name = $whichbrowser->browser->name ?? null;
        $os_name = $whichbrowser->os->name ?? null;
        $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
        $device_type = get_this_device_type();
        $is_unique = isset($_COOKIE[$cookie_name]) ? 0 : 1;

        /* Detect the location */
        try {
            $maxmind = (get_maxmind_reader_city())->get(get_ip());
        } catch(\Exception $exception) {
            /* :) */
        }
        $continent_code = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['code'] : null;
        $country_code = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
        $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;

        /* Process referrer */
        $referrer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : null;

        if(!isset($referrer)) {
            $referrer = [
                'host' => null,
                'path' => null
            ];
        }

        /* Check if the referrer comes from the same location */
        if(isset($referrer['host']) && $referrer['host'] == parse_url($this->transfer_request->full_url, PHP_URL_HOST)) {
            $is_unique = 0;

            $referrer = [
                'host' => null,
                'path' => null
            ];
        }

        $utm_source = input_clean($_GET['utm_source'] ?? null);
        $utm_medium = input_clean($_GET['utm_medium'] ?? null);
        $utm_campaign = input_clean($_GET['utm_campaign'] ?? null);

        /* Insert the log */
        db()->insert('statistics', [
            'transfer_request_id' => $this->transfer_request->transfer_request_id,
            'user_id' => $this->transfer_request_user->user_id,
            'continent_code' => $continent_code,
            'country_code' => $country_code,
            'city_name' => $city_name,
            'os_name' => $os_name,
            'browser_name' => $browser_name,
            'referrer_host' => $referrer['host'],
            'referrer_path' => $referrer['path'],
            'device_type' => $device_type,
            'browser_language' => $browser_language,
            'utm_source' => $utm_source,
            'utm_medium' => $utm_medium,
            'utm_campaign' => $utm_campaign,
            'is_unique' => $is_unique,
            'datetime' => get_date(),
        ]);

        /* Set cookie to try and avoid multiple entrances */
        $cookie_new_value = isset($_COOKIE[$cookie_name]) ? (int) $_COOKIE[$cookie_name] + 1 : 0;
        setcookie($cookie_name, (int) $cookie_new_value, time()+60*60*24*1);
    }

}
