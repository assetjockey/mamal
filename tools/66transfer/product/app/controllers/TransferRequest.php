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
use Altum\Csrf;
use Altum\Date;
use Altum\Models\User;
use Altum\Response;
use Altum\Title;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class TransferRequest extends Controller {
    use Apiable;

    public function index() {

        if(!settings()->transfers->transfer_requests_is_enabled) {
            throw_404();
        }

        $transfer_request_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Get transfer_request details */
        if(!$transfer_request = db()->where('transfer_request_id', $transfer_request_id)->getOne('transfer_requests')) {
            throw_404();
        }

        /* Make sure the current user has access */
        if(($transfer_request->uploader_id != md5(get_ip())) && (!$transfer_request->user_id || $transfer_request->user_id != $this->user->user_id)) {
            throw_404();
        }

        /* Generate the transfer_request full URL base */
        $transfer_request->full_url = (new \Altum\Models\TransferRequests())->get_transfer_request_full_url($transfer_request, $this->user);

        $transfer_request->settings = json_decode($transfer_request->settings ?? '');

        /* Get all submissions */
        $request_submissions = [];
        if($transfer_request->total_submissions) {
            $request_submissions_result = database()->query("SELECT * FROM `requests_submissions` WHERE `transfer_request_id` = {$transfer_request->transfer_request_id}");

            while($row = $request_submissions_result->fetch_object()) {
                $row->files = [];
                $request_submissions[$row->request_submission_id] = $row;
            }
        }

        /* Get the files */
        $files = (new \Altum\Models\Files())->get_files_by_transfer_request_id($transfer_request->transfer_request_id);

        /* File stats */
        $files_stats = [
            'total_size' => 0,
            'total_files' => 0,
        ];

        foreach($files as $file) {
            $files_stats['total_size'] += $file->size;
            $files_stats['total_files']++;

            /* Attach to the submissions */
            $request_submissions[$file->request_submission_id]->files[] = $file;
        }

        /* Clear it as its not needed */
        unset($files);

        $statistics = [];
        if($transfer_request->pageviews) {
            $statistics = db()->orderBy('transfer_request_id', 'DESC')->where('transfer_request_id', $transfer_request->transfer_request_id)->get('statistics', 5);
        }

        /* Set a custom title */
        Title::set(sprintf(l('transfer_request.title'), $transfer_request->name));

        /* Initiate captcha */
        $captcha = new Captcha();

        /* Main View */
        $data = [
            'transfer_request' => $transfer_request,
            'files_stats' => $files_stats,
            'statistics' => $statistics,
            'request_submissions' => $request_submissions,
            'captcha' => $captcha,
        ];

        $view = new \Altum\View('transfer-request/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }

    public function update_api() {

        set_time_limit(0);

        if(empty($_POST)) {
            throw_404();
        }

        /* Define the return content to be treated as JSON */
        header('Content-Type: application/json');

        /* Check for required fields */
        $required_fields = ['transfer_request_id', 'uploaded_files'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field])) {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        if(!Csrf::check('global_token')) {
            $this->response_error(l('global.error_message.invalid_csrf_token'), 401);
        }

        /* Initiate captcha */
        $captcha = new Captcha();

        if(settings()->captcha->transfer_request_upload_is_enabled && !$captcha->is_valid(false)) {
            $this->response_error(l('global.error_message.invalid_captcha'), 401);
        }

        /* Filter variables */
        $_POST['transfer_request_id'] = (int) $_POST['transfer_request_id'];
        $_POST['uploaded_files'] = array_query_clean($_POST['uploaded_files']);
        $_POST['uploaded_files'] = array_map(function($uuid) {
            return preg_replace('/[^a-zA-Z0-9]/', '', $uuid ?? '');
        }, $_POST['uploaded_files']);

        /* Get the transfer */
        $transfer_request = db()->where('transfer_request_id', $_POST['transfer_request_id'])->getOne('transfer_requests');

        if(!$transfer_request || !$transfer_request->is_enabled) {
            $this->response_error(l('global.error_message.basic'), 404);
        }

        /* Do not allow updates to expired transfers */
        if($transfer_request->expiration_datetime && (new \DateTime($transfer_request->expiration_datetime)) < (new \DateTime())) {
            $this->response_error(l('global.error_message.basic'), 401);
        }

        /* Settings */
        $transfer_request->settings = json_decode($transfer_request->settings ?? '');

		/* Check for access based on password */
		$has_access =
			!$transfer_request->settings->password ||
			(
				$transfer_request->settings->password
				&& isset($_POST['password'])
				&& $_POST['password'] == $transfer_request->settings->password
				&& session_has('transfer_request_password_' . $transfer_request->transfer_request_id)
			);

		if(!$has_access) {
			$this->response_error(l('global.error_message.basic'), 401);
		}

        /* Notifications */
        $transfer_request->notifications = json_decode($transfer_request->notifications ?? '');

        /* Get details for the owner of the transfer request */
        $user = (new User())->get_user_by_user_id($transfer_request->user_id);

        $files = [];
        $total_size = 0;

        /* Make sure the uploaded files exist and are ready */
        foreach($_POST['uploaded_files'] as $file_uuid) {
            $file_uuid = hex2bin($file_uuid);
            $file = db()->where('file_uuid', $file_uuid)->getOne('files');

            if(!$file) {
                continue;
            }

            if($file->status == 'uploading') {
                continue;
            }

            if($file->uploader_id != md5(get_ip())) {
                continue;
            }

            if($file->transfer_id) {
                continue;
            }

            $files[] = $file;
            $total_size += $file->size;
        }

        $files_count = count($files);

        if(!$files_count) {
            $this->response_error(l('transfer.error_message.empty_files'), 401);
        }

        /* Existing + new files limit */
        $new_total_files = $transfer_request->total_files + $files_count;

        if($user->plan_settings->files_per_transfer_limit != -1 && $new_total_files > $user->plan_settings->files_per_transfer_limit) {
            $this->response_error(l('transfer.error_message.files_per_transfer_limit'), 401);
        }

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($user);

        /* Full transfer URL */
        $transfer_request->full_url = $transfer_request->domain_id && isset($domains[$transfer_request->domain_id]) ? $domains[$transfer_request->domain_id]->scheme . $domains[$transfer_request->domain_id]->host . '/r' . $transfer_request->url . '/' : SITE_URL . 'r/' . $transfer_request->url . '/';

        /* New total size for the transfer request */
        $new_total_size = $transfer_request->total_size + $total_size;

        /* Detect extra details about the user */
        $whichbrowser = get_whichbrowser();

        /* Detect extra details about the user */
        $browser_name = $whichbrowser->browser->name ?? null;
        $os_name = $whichbrowser->os->name ?? null;
        $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
        $device_type = get_this_device_type();

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
        $referrer = [
            'host' => null,
            'path' => null
        ];

        $is_unique = 1;
        if(isset($_SERVER['HTTP_REFERER'])) {
            $referrer = parse_url($_SERVER['HTTP_REFERER']);

            if($_SERVER['HTTP_REFERER'] == $transfer_request->full_url) {
                $is_unique = 0;

                $referrer = [
                    'host' => null,
                    'path' => null
                ];
            }
        }

        $utm_source = input_clean($_GET['utm_source'] ?? null);
        $utm_medium = input_clean($_GET['utm_medium'] ?? null);
        $utm_campaign = input_clean($_GET['utm_campaign'] ?? null);

        /* Create the submission */
        $request_submission_id = db()->insert('requests_submissions', [
            'transfer_request_id' => $_POST['transfer_request_id'],
            'uploader_id' => md5(get_ip()),
            'total_files' => $new_total_files,
            'total_size' => $new_total_size,
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
            'datetime' => get_date()
        ]);

        /* Attach files */
        foreach($files as $file) {
            db()->where('file_id', $file->file_id)->update('files', [
                'user_id' => $transfer_request->user_id,
                'transfer_request_id' => $transfer_request->transfer_request_id,
                'request_submission_id' => $request_submission_id,
            ]);
        }

        /* Update transfer totals */
        db()->where('transfer_request_id', $transfer_request->transfer_request_id)->update('transfer_requests', [
            'total_submissions' => db()->inc(),
            'total_files' => $new_total_files,
            'total_size' => $new_total_size,
            'last_submission_datetime' => get_date(),
        ]);

        /* Update the user file usage */
        (new \Altum\Models\Files())->calculate_and_update_file_usage($user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($user->user_id);

        /* Core data sent to the NotificationHandlers processor */
        $notification_data = [
            'transfer_request_id' => $transfer_request->transfer_request_id,
            'name' => $transfer_request->name,
            'total_files' => nr($files_count),
            'total_size' => get_formatted_bytes($total_size),
            'continent_code'    => $continent_code,
            'country_code' => $country_code ? get_country_from_country_code($country_code) : null,
            'city_name' => $city_name,
            'device_type' => l('global.device.' . $device_type, $user->language),
            'os_name' => $os_name,
            'browser_name' => $browser_name,
            'browser_language'  => $browser_language,
            'url' => url('transfer-request/' . $transfer_request->transfer_request_id),
        ];

        /* Build the HTML list for the e-mail body */
        $email_body = \Altum\NotificationHandlers::build_dynamic_message_data(
            $notification_data,
            'transfers',
            \Altum\NotificationHandlers::DYNAMIC_DATA_FORMAT_HTML,
            $user->language,
            $user->timezone,
            emoji: false
        );

        /* Prepare the email template used by the email handler */
        $email_template = get_email_template(
            [
                '{{TRANSFER_REQUEST_NAME}}' => $transfer_request->name,
            ],
            l('global.emails.transfer_request_submission.subject', $user->language),
            [
                '{{NAME}}' => $user->name,
                '{{TRANSFER_REQUEST_LINK}}' => $notification_data['url'],
                '{{TRANSFER_REQUEST_NAME}}' => $transfer_request->name,
                '{{DATA}}' => nl2br($email_body),
            ],
            l('global.emails.transfer_request_submission.body', $user->language)
        );

        /* Build the context passed to the NotificationHandlers class */
        $context = [
            /* User details */
            'user' => $user,
            'language' => $user->language,
            'timezone' => $user->timezone,

            /* Email */
            'email_template' => $email_template,

            /* Dynamic data */
            'dynamic_data_type' => 'transfers',
            'message_template' => l('r_transfer_request.submission.simple_notification', $user->language),
            'message_template_arguments' => [
                $transfer_request->name,
                '{{DYNAMIC_DATA}}',
                $notification_data['url'],
            ],

            /* Push notifications */
            'push_title' => l('r_transfer_request.submission.push_notification.title', $user->language),
            'push_description' => sprintf(
                l('r_transfer_request.submission.push_notification.description', $user->language),
                $transfer_request->name
            ),

            /* WhatsApp */
            'whatsapp_template' => 'transfer_request_submission',
            'whatsapp_parameters' => [
                $transfer_request->name,
                $notification_data['url'],
            ],

            /* Twilio call */
            'twilio_call_url' => SITE_URL .
                'twiml/r_transfer_request.submission.simple_notification?param1=' .
                urlencode($transfer_request->name) .
                '&param2=' . urlencode($notification_data['url']),

            /* Internal notification */
            'internal_icon' => 'fas fa-upload',

            /* Discord */
            'discord_color' => '2664261',

            /* Slack */
            'slack_emoji' => ':arrow_up:',
        ];

        /* Send notifications */
        \Altum\NotificationHandlers::process(
            $notification_handlers,
            $transfer_request->notifications->submission ?? [],
            $notification_data,
            $context
        );

        /* Clear the cache */
        // :)

        /* Set a nice success message */
        Alerts::add_success(l('r_transfer_request.submission.success_message'));

        Response::jsonapi_success([
            'view_url' => $transfer_request->full_url,
        ]);
    }

}
