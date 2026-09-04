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

class WebhookHeartbeat extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->heartbeats_is_enabled) {
            http_response_code(404);
            die();
        }

        /* Clean the heartbeat code */
        $code = isset($this->params[0]) ? query_clean($this->params[0]) : false;

        /* Get the details of the campaign from the database */
        $heartbeat = (new \Altum\Models\Heartbeats())->get_heartbeat_by_code($code);

        /* Make sure the campaign has access */
        if(!$heartbeat) {
            http_response_code(401);
            die();
        }

        $heartbeat->notifications = json_decode($heartbeat->notifications ?? '');
        $heartbeat->settings = json_decode($heartbeat->settings ?? '');
        $heartbeat->last_logs = json_decode($heartbeat->last_logs ?? '');

        if(!$heartbeat->is_enabled) {
            http_response_code(403);
            die();
        }

        /* Make sure we don't get spammed */
        /* 57 instead of 60 - to allow for small time differences */
        if($heartbeat->last_run_datetime && (new \DateTime($heartbeat->last_run_datetime))->modify('+57 seconds') > (new \DateTime())) {
            http_response_code(403);
            die();
        }

        /* Make sure to get the user data and confirm the user is ok */
        $user = (new \Altum\Models\User())->get_user_by_user_id($heartbeat->user_id);

        if(!$user) {
            http_response_code(403);
            die();
        }

        if(!$user->status) {
            http_response_code(403);
            die();
        }

        /* Make sure the user's plan is not already expired */
        if((new \DateTime()) > (new \DateTime($user->plan_expiration_date)) && $user->plan_id != 'free') {
            http_response_code(403);
            die();
        }

        /* Get the language for the user and set the timezone */
        \Altum\Date::$timezone = $user->timezone;

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($user->user_id);

        $is_ok = 1;

        /* Insert the history log */
        $heartbeat_log_id = db()->insert('heartbeats_logs', [
            'heartbeat_id' => $heartbeat->heartbeat_id,
            'user_id' => $user->user_id,
            'is_ok' => $is_ok,
            'datetime' => get_date(),
        ]);

        /* Assuming, based on the run interval */
        $uptime_seconds_to_add = 0;
        switch($heartbeat->settings->run_interval_type) {
            case 'minutes':
                $uptime_seconds_to_add = $heartbeat->settings->run_interval * 60;
                break;

            case 'hours':
                $uptime_seconds_to_add = $heartbeat->settings->run_interval * 60 * 60;
                break;

            case 'days':
                $uptime_seconds_to_add = $heartbeat->settings->run_interval * 60 * 60 * 24;
                break;
        }
        $uptime_seconds = $heartbeat->uptime_seconds + $uptime_seconds_to_add;
        $downtime_seconds = $heartbeat->downtime_seconds;

        /* ^_^ */
        $uptime = $uptime_seconds > 0 ? $uptime_seconds / ($uptime_seconds + $downtime_seconds) * 100 : 0;
        $downtime = 100 - $uptime;
        $main_run_datetime = !$heartbeat->main_run_datetime || (!$heartbeat->is_ok && $is_ok) ? get_date() : $heartbeat->main_run_datetime;
        $last_run_datetime = get_date();

        /* Calculate expected next run */
        $next_run_datetime = (new \DateTime())
            ->modify('+' . $heartbeat->settings->run_interval . ' ' . $heartbeat->settings->run_interval_type)
            ->modify('+' . $heartbeat->settings->run_interval_grace . ' ' . $heartbeat->settings->run_interval_grace_type)
            ->format('Y-m-d H:i:s');

        /* Close incident */
        if($is_ok && $heartbeat->incident_id) {

            /* Database query */
            db()->where('incident_id', $heartbeat->incident_id)->update('incidents', [
                'end_heartbeat_log_id' => $heartbeat_log_id,
                'end_datetime' => get_date(),
            ]);

            $incident_id = null;

            /* Get details about the incident */
            $heartbeat_incident = db()->where('incident_id', $heartbeat->incident_id)->getOne('incidents', ['start_datetime', 'end_datetime']);

            /* Get the language for the user */
            \Altum\Date::$timezone = $user->timezone;

            /* Processing the notification handlers */
            /* Core data to be sent to the new processor */
            $notification_data = [
                'heartbeat_id' => $heartbeat->heartbeat_id,
                'name'         => $heartbeat->name,
                'is_ok'        => $is_ok,
                'url'          => url('heartbeat/' . $heartbeat->heartbeat_id),
                'incident_url' => url('incident/' . $heartbeat->incident_id),
            ];

            /* Build a plain caught-data string for the generic message */
            $dynamic_message_data = \Altum\NotificationHandlers::build_dynamic_message_data($notification_data);

            /* Compose the generic notification text */
            $notification_message = sprintf(
                l('heartbeat.simple_notification.is_ok', $user->language),
                $heartbeat->name,
                $dynamic_message_data,
                $notification_data['url']
            );

            /* Prepare the email template used by the email handler */
            $email_template = (object) [
                'subject' => sprintf(l('cron.is_ok.title', $user->language), $heartbeat->name),
                'body'    => (new \Altum\View('partials/cron/heartbeat_is_ok', (array) $this))->run([
                    'heartbeat_incident' => $heartbeat_incident,
                    'user'               => $user,
                    'row'                => $heartbeat,
                    'incident_id'        => $heartbeat->incident_id,
                ]),
            ];

            /* Build the context passed to the new NotificationHandlers class */
            $context = [
                /* User details */
                'user' => $user,

                /* Email */
                'email_template' => $email_template,

                /* Basic message for most integrations */
                'message' => $notification_message,

                /* Push notifications */
                'push_title'       => l('heartbeat.push_notification.is_not_ok.title', $heartbeat->language),
                'push_description' => sprintf(
                    l('heartbeat.push_notification.description', $heartbeat->language),
                    $heartbeat->name,
                    $heartbeat->target
                ),

                /* WhatsApp */
                'whatsapp_template'   => 'heartbeat_up',
                'whatsapp_parameters' => [
                    $heartbeat->name,
                    $notification_data['url'],
                ],

                /* Twilio call */
                'twilio_call_url' => SITE_URL .
                    'twiml/heartbeat.simple_notification.is_ok?param1=' .
                    urlencode($heartbeat->name) .
                    '&param2=&param3=' .
                    urlencode($notification_data['url']),

                /* Internal notification */
                'internal_icon' => 'fas fa-heartbeat',

                /* Discord */
                'discord_color' => '2664261',

                /* Slack */
                'slack_emoji' => ':large_green_square:',
            ];

            /* Send notifications */
            \Altum\NotificationHandlers::process(
                $notification_handlers,
                $heartbeat->notifications->is_ok,
                $notification_data,
                $context
            );
        }

        /* Keep the last logs for immediate access */
        $last_logs = [];

        for($i = 1; $i <= 6; $i++) {
            $last_logs[] = isset($heartbeat->last_logs[$i]) ? $heartbeat->last_logs[$i] : [];
        }

        $last_logs[] = [
            'incident_id' => null,
            'is_ok' => $is_ok,
            'datetime' => get_date(),
        ];

        /* Update the heartbeat */
        db()->where('heartbeat_id', $heartbeat->heartbeat_id)->update('heartbeats', [
            'incident_id' => $incident_id,
            'is_ok' => $is_ok,
            'uptime' => $uptime,
            'uptime_seconds' => $uptime_seconds,
            'downtime' => $downtime,
            'downtime_seconds' => $downtime_seconds,
            'total_runs' => db()->inc(),
            'main_run_datetime' => $main_run_datetime,
            'last_run_datetime' => $last_run_datetime,
            'next_run_datetime' => $next_run_datetime,
            'last_logs' => json_encode($last_logs),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('heartbeat_id=' . $heartbeat->heartbeat_id);

        echo 'successful';

    }
}
