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

use Altum\Models\User;

defined('ALTUMCODE') || die();

class PixelTrack extends Controller {

    public function index() {

		/* Make sure no cache is being used on the endpoint */
		header('Cache-Control: no-store');

        /* Check against bots */
        $CrawlerDetect = new \Jaybizzle\CrawlerDetect\CrawlerDetect();

        if($CrawlerDetect->isCrawler()) {
            die(settings()->main->title . " (" . SITE_URL. "): Bot usage has been detected, pixel stopped from executing.");
        }

        /* Get the Payload of the Post */
        $payload = @file_get_contents('php://input');
        $post = json_decode($payload);

        if(!$post) {
            die(settings()->main->title . " (" . SITE_URL. "): No content posted.");
        }

        /* Allowed types of requests to this endpoint */
        $allowed_types = ['track', 'notification', 'auto_capture', 'collector'];
        $date = get_date();
        $domain = query_clean(parse_url($post->url, PHP_URL_HOST));
        $pixel_key = isset($this->params[0]) ? input_clean($this->params[0]) : null;

        /* Remove www. from the host */
        $domain = str_starts_with($domain, 'www.') ? substr($domain, 4) : $domain;

        if(!isset($post->type) || !in_array($post->type, $allowed_types)) {
            die(settings()->main->title . " (" . SITE_URL. "): Invalid type.");
        }

        /* Clean all the received variables */
        foreach($post as $key => $value) {
            $post->{$key} = input_clean($value);
        }

        /* Get the details of the campaign from the database */
        $campaign = (new \Altum\Models\Campaign())->get_campaign_by_pixel_key($pixel_key);

        /* Make sure the campaign has access */
        if(!$campaign) {
            die(settings()->main->title . " (" . SITE_URL. "): No campaign found for this pixel.");
        }

        if(
            !$campaign->is_enabled
            || ($campaign->domain != $domain && $campaign->domain != 'www.' . $domain)
        ) {
            die(settings()->main->title . " (" . SITE_URL. "): Campaign disabled or it does not match the set domain/subdomain.");
        }

        /* Make sure to get the user data and confirm the user is ok */
        $user = (new \Altum\Models\User())->get_user_by_user_id($campaign->user_id);

        if(!$user) {
            die(settings()->main->title . " (" . SITE_URL. "): Campaign owner not found.");
        }

        if($user->status != 1) {
            die(settings()->main->title . " (" . SITE_URL. "): Campaign owner is disabled.");
        }

        /* Check for a custom domain */
        if(isset(\Altum\Router::$data['domain']) && $campaign->domain_id != \Altum\Router::$data['domain']->domain_id) {
            die("console.log('" . settings()->main->title . " (" . SITE_URL. "): Domain id mismatch.')");
        }

        /* Ignore excluded ips */
        if(in_array($post->type, ['track', 'notification'])) {
            $excluded_ips = array_flip($user->preferences->excluded_ips ?? []);
            if(isset($excluded_ips[get_ip()])) {
                die("console.log('" . settings()->main->title . " (" . SITE_URL . "): Tracking disabled for this IP.')");
            }
        }

        /* Process the plan of the user */
        (new User())->process_user_plan_expiration_by_user($user);

        /* Make sure that the user didnt exceed the current plan */
        if($user->plan_settings->notifications_impressions_limit != -1 && $user->current_month_notifications_impressions >= $user->plan_settings->notifications_impressions_limit) {
            die(settings()->main->title . " (" . SITE_URL. "): Notification impressions limit exceeded.");
        }

        /* Get the current path of the submitted url */
        $path = parse_url($post->url, PHP_URL_PATH);

        switch($post->type) {

            /* Tracking the notifications states, impressions, hovers, etc */
            case 'notification':

                $post->notification_id = (int) $post->notification_id;
                $post->subtype = in_array(
                    $post->subtype,
                    [
                        'hover',
                        'impression',
                        'click',

                        /* Feedback emoji */
                        'feedback_emoji_angry',
                        'feedback_emoji_sad',
                        'feedback_emoji_neutral',
                        'feedback_emoji_happy',
                        'feedback_emoji_excited',

                        /* Feedback score */
                        'feedback_score_1',
                        'feedback_score_2',
                        'feedback_score_3',
                        'feedback_score_4',
                        'feedback_score_5'
                    ]
                ) ? $post->subtype : false;

                /* Make sure the type of notification is the correct one */
                if(!$post->subtype) {
                    die(settings()->main->title . " (" . SITE_URL. "): No subtype posted.");
                }

                /* Make sure the notification provided is a child of the campaign, exists and is enabled */
                $notification = \Altum\Cache::cache_function_result('notification?notification_id=' . $post->notification_id . '&campaign_id=' . $campaign->campaign_id . '&type=notification', 'campaign_id=' . $campaign->campaign_id, function() use ($post, $campaign) {
                    return db()->where('notification_id', $post->notification_id)->where('campaign_id', $campaign->campaign_id)->where('is_enabled', 1)->getOne('notifications', ['campaign_id', 'notification_id']);
                });

                if(!$notification) {
                    die(settings()->main->title . " (" . SITE_URL. "): Notification not found.");
                }

                /* Insert or update the log */
                db()->insert('track_notifications', [
                    'user_id' => $user->user_id,
                    'notification_id' => $notification->notification_id,
                    'campaign_id' => $notification->campaign_id,
                    'type' => $post->subtype,
                    'path' => $path,
                    'datetime' => $date,
                ]);

                /* Update notification */
                $statistic_key = match($post->subtype) {
                    'impression' => 'impressions',
                    'hover' => 'hovers',
                    'click' => 'clicks',
                    default => 'form_submissions'
                };

                db()->where('notification_id', $notification->notification_id)->update('notifications', [
                    $statistic_key => db()->inc()
                ]);

                /* Count it in the users account if it's an impression */
                if($post->subtype == 'impression') {
                    db()->where('user_id', $campaign->user_id)->update('users', [
                        'current_month_notifications_impressions' => db()->inc(),
                        'total_notifications_impressions' => db()->inc(),
                    ]);
                }

                break;

            /* Tracking the visits of the user */
            case 'track':

                /* Generate an id for the log */
                $ip = get_ip();
                $ip_binary = $ip ? inet_pton($ip) : null;

                /* Insert or update the log */
                db()->insert('track_logs', [
                    'user_id' => $campaign->user_id,
                    'campaign_id' => $campaign->campaign_id,
                    'path' => $path,
                    'path_hashed' => md5($path),
                    'ip_binary' => $ip_binary,
                    'datetime' => $date,
                ]);

                break;

            /* Getting the data from the email collector form */
            case 'collector':

                $post->notification_id = (int) $post->notification_id;

                /* Make sure the notification provided is a child of the campaign, exists and is enabled */
                $notification = \Altum\Cache::cache_function_result('notification?notification_id=' . $post->notification_id . '&campaign_id=' . $campaign->campaign_id . '&type=collector', 'campaign_id=' . $campaign->campaign_id, function() use ($post, $campaign) {
                    return db()->where('notification_id', $post->notification_id)->where('campaign_id', $campaign->campaign_id)->where('is_enabled', 1)->where('type', ['email_collector', 'request_collector', 'countdown_collector', 'collector_bar', 'collector_modal', 'collector_two_modal', 'text_feedback'], 'IN')->getOne('notifications', ['campaign_id', 'notification_id']);
                });

                if(!$notification) {
                    die(settings()->main->title . " (" . SITE_URL. "): Notification not found.");
                }

                /* Data for the conversion */
                $data = [];

                /* Determine if we have email or input keys */
                $collector_key = false;

                if(isset($post->email) && !empty($post->email)) {
                    $collector_key = 'email';

                    /* Make sure that what we got is an actual email */
                    if(!filter_var($post->email, FILTER_VALIDATE_EMAIL)) {
                        die(settings()->main->title . " (" . SITE_URL. "): Email not validated.");
                    }

                    /* Check for a potential name field */
                    if(isset($post->name)) {
                        $data['name'] = input_clean($post->name);
                    }
                }

                if(isset($post->input) && !empty($post->input)) {
                    $collector_key = 'input';
                }

                if(!$collector_key) {
                    die(settings()->main->title . " (" . SITE_URL. "): Collector key not existing.");
                }

                /* Make sure that the data is not already submitted and exists for this notification */
                $result = database()->query("SELECT `id` FROM `track_conversions` WHERE `notification_id` = {$post->notification_id} AND JSON_EXTRACT(`data`, '$.{$collector_key}') = '{$post->{$collector_key}}'");

                if($result->num_rows) {
                    die(settings()->main->title . " (" . SITE_URL. "): Conversion already submitted.");
                }

                /* Add the collector main key input to the data var */
                $data[$collector_key] =  $post->{$collector_key};

                /* Detect the location */
                try {
                    $maxmind = (get_maxmind_reader_city())->get(get_ip());
                } catch(\Exception $exception) {
                    /* :) */
                }
                $continent_code = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['code'] : null;
                $country_code = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
                $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;

                $location_data = [
                    'city' => $city_name,
                    'country_code' => $country_code,
                    'country' => get_country_from_country_code($country_code),
                    'continent_code' => $continent_code,
                    'continent' => get_continents_no_emoji_array()[mb_strtoupper($continent_code ?? '')] ?? $continent_code,
                ];

                /* Insert the conversion log */
                db()->insert('track_conversions', [
                    'user_id' => $user->user_id,
                    'notification_id' => $post->notification_id,
                    'type' => $post->type,
                    'data' => json_encode($data),
                    'path' => $path,
                    'page_title' => input_clean($post->page_title, 64),
                    'location' => json_encode($location_data),
                    'datetime' => $date,
                ]);

                /* Generate an id for the log */
                $type = 'form_submission';

                /* Insert or update the log */
                db()->insert('track_notifications', [
                    'user_id' => $user->user_id,
                    'notification_id' => $post->notification_id,
                    'campaign_id' => $campaign->campaign_id,
                    'type' => $type,
                    'path' => $path,
                    'datetime' => $date,
                ]);

                /* Update notification */
                db()->where('notification_id', $post->notification_id)->update('notifications', [
                    'form_submissions' => db()->inc()
                ]);

                /* Make sure to send the webhook of the conversion */
                $notification = database()->query("SELECT `notifications`.`notification_id`, `notifications`.`name`, `notifications`.`settings`, `notifications`.`notifications`, `campaigns`.`name` AS `campaign_name` FROM `notifications` LEFT JOIN `campaigns` ON `campaigns`.`campaign_id` = `notifications`.`campaign_id`  WHERE `notification_id` = {$post->notification_id}")->fetch_object();
                $notification->notifications = json_decode($notification->notifications ?? '');
                $notification->settings = json_decode($notification->settings ?? '');

                /* Processing the notification handlers */
				if(count($notification->notifications ?? [])) {
					$notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($user->user_id);

					/* Assemble the core notification data */
					$notification_data = array_merge(
						$data,
						$location_data,
						[
							'tracked_url' => $post->url,
							'notification_id' => $post->notification_id,
							'notification_name' => $notification->name,
							'campaign_id' => $campaign->campaign_id,
							'campaign_name' => $campaign->name,
							'url' => url('notification/' . $notification->notification_id . '/data'),
						]
					);

					/* Build the HTML list for the e-mail body */
					$email_body = \Altum\NotificationHandlers::build_dynamic_message_data(
						$notification_data,
						'collectors',
						\Altum\NotificationHandlers::DYNAMIC_DATA_FORMAT_HTML,
						$user->language,
						$user->timezone,
                        emoji: false
					);

					/* Prepare the e-mail template */
					$email_template = get_email_template(
						[
							'{{NOTIFICATION_NAME}}' => $notification->name,
							'{{CAMPAIGN_NAME}}' => $notification->campaign_name,
						],
						l('global.emails.user_data_send.subject', $user->language),
						[
                            '{{NAME}}' => $user->name,
                            '{{NOTIFICATION_NAME}}' => $notification->name,
							'{{CAMPAIGN_NAME}}' => $notification->campaign_name,
							'{{DATA_LINK}}' => $notification_data['url'],
							'{{DATA}}' => nl2br($email_body),
						],
						l('global.emails.user_data_send.body', $user->language),
					);

					/* Context passed to the central handler */
					$context = [
						/* User details */
						'user' => $user,
						'language' => $user->language,
						'timezone' => $user->timezone,

						/* Email */
						'email_template' => $email_template,

						/* Dynamic data */
						'dynamic_data_type' => 'collectors',
						'message_template' => l('notification.simple_notification', $user->language),
						'message_template_arguments' => [
							$notification->name,
							$notification->campaign_name,
							'{{DYNAMIC_DATA}}',
							$notification_data['url'],
						],

						/* Push notifications */
						'push_title' => l('notification.push_notification.title', $user->language),
						'push_description' => sprintf(
							l('notification.push_notification.description', $user->language),
							$notification->campaign_name,
							$notification->name
						),

						/* WhatsApp */
						'whatsapp_template' => 'notification_data',
						'whatsapp_parameters' => [
							$notification->campaign_name,
							$notification->name,
							$notification_data['url'],
						],

						/* Twilio call */
						'twilio_call_url' => SITE_URL .
							'twiml/notification.simple_notification?param1=' .
							urlencode($notification->campaign_name) .
							'&param2=' . urlencode($notification->name) .
							'&param3=&param4=' . urlencode($notification_data['url']),

						/* Internal notification */
						'internal_icon' => 'fas fa-database',

						/* Discord */
						'discord_color' => '2664261',

						/* Slack */
						'slack_emoji' => ':large_green_circle:',
					];

					/* Dispatch all notifications through the unified processor */
					\Altum\NotificationHandlers::process(
						$notification_handlers,
						$notification->notifications,
						$notification_data,
						$context
					);
				}

                break;

            /* Auto Capturing data from forms */
            case 'auto_capture':

                $post->notification_id = (int) $post->notification_id;

                /* Make sure the notification provided is a child of the campaign, exists, is enabled and has auto capturing enabled */
                $notification = \Altum\Cache::cache_function_result('notification?notification_id=' . $post->notification_id . '&campaign_id=' . $campaign->campaign_id . '&type=auto_capture', 'campaign_id=' . $campaign->campaign_id, function() use ($post, $campaign) {
                    return db()->where('notification_id', $post->notification_id)->where('campaign_id', $campaign->campaign_id)->where('is_enabled', 1)->where('type', ['inline_conversions', 'conversions', 'conversions_counter', 'inline_conversions_counter'], 'IN')->getOne('notifications', ['campaign_id', 'notification_id', 'settings']);
                });

                if(!$notification) {
                    die(settings()->main->title . " (" . SITE_URL. "): Notification not found.");
                }

                $notification->settings = json_decode($notification->settings ?? '');

                if(!($notification->settings->data_trigger_auto ?? false)) {
                    die(settings()->main->title . " (" . SITE_URL. "): Auto capturing is not enabled.");
                }

                /* Make sure to get only the needed data from the submission */
                $data = [];

                /* Save only parameters that start with "form_" */
                foreach($post as $key => $value) {
                    if(mb_strpos($key, 'form_') === 0) {
                        $data[str_replace('form_', '', $key)] = $value;
                    }
                }

                /* Data for the conversion */
                $data = json_encode($data);

                /* Detect the location */
                try {
                    $maxmind = (get_maxmind_reader_city())->get(get_ip());
                } catch(\Exception $exception) {
                    /* :) */
                }
                $continent_code = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['code'] : null;
                $country_code = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
                $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;

                $location_data = json_encode(
                    [
                        'city' => $city_name,
                        'country_code' => $country_code,
                        'country' => get_country_from_country_code($country_code),
                        'continent_code' => $continent_code,
                        'continent' => get_continent_from_continent_code($continent_code),
                    ]
                );

                /* Insert the conversion log */
                db()->insert('track_conversions', [
                    'user_id' => $user->user_id,
                    'notification_id' => $notification->notification_id,
                    'type' => $post->type,
                    'data' => $data,
                    'path' => $path,
                    'page_title' => input_clean($post->page_title, 64),
                    'location' => $location_data,
                    'datetime' => $date,
                ]);

                $type = 'auto_capture';

                /* Database query */
                db()->insert('track_notifications', [
                    'user_id' => $user->user_id,
                    'notification_id' => $notification->notification_id,
                    'campaign_id' => $notification->campaign_id,
                    'type' => $type,
                    'path' => $path,
                    'datetime' => $date,
                ]);

                break;
        }

    }

}
