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

class ServerMonitorTrack extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->server_monitors_is_enabled) {
            throw_404();
        }

        $server_monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;
        $api_key = isset($this->params[1]) ? input_clean($this->params[1]) : null;

        if(!$server_monitor_id || !$api_key) {
            die(settings()->main->title . " (" . SITE_URL. "): Server monitor ID or API key is missing.");
        }

        /* Get the Payload of the Post */
        $payload = @file_get_contents('php://input');
        $post = json_decode($payload);

        if(!$post) {
            die(settings()->main->title . " (" . SITE_URL. "): No content posted.");
        }

        $required_fields = ['cpu_usage', 'ram_usage', 'disk_usage'];
        foreach($required_fields as $field) {
            if(!isset($post->{$field}) || (isset($post->{$field}) && empty($post->{$field}) && $post->{$field} != '0')) {
                die(settings()->main->title . " (" . SITE_URL. "): Required fields are missing.");
            }
        }

        /* Get the user */
        $user = \Altum\Cache::cache_function_result('user?api_key=' . $api_key, null, function() use ($api_key, $server_monitor_id) {
            return db()->where('api_key', $api_key)->where('status', 1)->getOne('users');
        });

        if(!$user) {
            die(settings()->main->title . " (" . SITE_URL. "): Server monitor owner not found.");
        }

        if($user->status != 1) {
            die(settings()->main->title . " (" . SITE_URL. "): Server monitor owner is disabled.");
        }

        $user->plan_settings = json_decode($user->plan_settings ?? '');

        /* Get the server monitor */
        $server_monitor = \Altum\Cache::cache_function_result('server_monitor?server_monitor_id=' . $server_monitor_id, null, function() use ($user, $server_monitor_id) {
            return db()->where('server_monitor_id', $server_monitor_id)->where('user_id', $user->user_id)->getOne('server_monitors');
        });

        /* Get the server monitor */
        if(!$server_monitor) {
            die(settings()->main->title . " (" . SITE_URL. "): Server monitor not found.");
        }

        if(!$server_monitor->is_enabled) {
            die(settings()->main->title . " (" . SITE_URL. "): Server monitor is disabled.");
        }

        $server_monitor_settings_have_changed = false;
        $server_monitor->settings = json_decode($server_monitor->settings ?? '');
        $server_monitor->notifications = json_decode($server_monitor->notifications ?? '');

        /* Skip if there is too much data too quick */
        $last_log_minutes_elapsed = $server_monitor->last_log_datetime ? (new \DateTime($server_monitor->last_log_datetime))->diff(new \DateTime())->i : ($server_monitor->settings->server_check_interval_seconds / 60);

        if($last_log_minutes_elapsed < ($server_monitor->settings->server_check_interval_seconds / 60)) {
            die(settings()->main->title . " (" . SITE_URL. "): Too fast.");
        }

        /* Alerts processing in case its required */
        if(count($server_monitor->notifications ?? []) && count($server_monitor->settings->alerts ?? [])) {

            /* Go through each alert to determine if we need to retrieve past logs */
            $alert_trigger_max_value = 1;
            foreach($server_monitor->settings->alerts as $alert) {
                $alert_trigger_max_value = $alert->trigger > $alert_trigger_max_value ? $alert->trigger : $alert_trigger_max_value;
            }

            /* Only continue if there are no untriggered alerts */
            $server_monitor_logs = [
                (object)[
                    'cpu_usage' => $post->cpu_usage,
                    'disk_usage' => $post->disk_usage,
                    'ram_usage' => $post->ram_usage,
                ],
            ];

            /* Get past logs if we need to */
            if($alert_trigger_max_value > 1) {
                $previous_server_monitor_logs = db()->where('server_monitor_id', $server_monitor->server_monitor_id)->orderBy('server_monitor_log_id', 'DESC')->get('server_monitors_logs', $alert_trigger_max_value - 1, ['cpu_usage', 'disk_usage', 'ram_usage']);
                $server_monitor_logs = array_merge($server_monitor_logs, $previous_server_monitor_logs);
            }

            /* Get available notification handlers */
            $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($server_monitor->user_id);

            /* Go through each alert */
            foreach($server_monitor->settings->alerts as $alert_key => $alert) {

                /* Assume that the alert will trigger */
                $should_trigger = true;

                /* Go through each logs and check if we should actually trigger the alert or not */
                foreach($server_monitor_logs as $server_monitor_log) {

                    /* We're checking for negatives */
                    switch($alert->rule) {
                        case 'is_higher':

                            if($server_monitor_log->{$alert->metric} < $alert->value) {
                                $should_trigger = false;
                            }

                            break;

                        case 'is_lower':

                            if($server_monitor_log->{$alert->metric} > $alert->value) {
                                $should_trigger = false;
                            }

                            break;
                    }

                }

                /* Notifications processing */
                if($should_trigger && !$alert->is_triggered) {
                    $server_monitor->settings->alerts[$alert_key]->is_triggered = 1;
                    $server_monitor_settings_have_changed = true;

                    /* Core data to be sent to the new processor */
                    $notification_data = [
                        'server_monitor_id' => $server_monitor->server_monitor_id,
                        'name'              => $server_monitor->name,
                        'target'            => $server_monitor->target,
                        'metric_key'        => $alert->metric,
                        'metric_value'      => $post->{$alert->metric},
                        'url'               => url('server-monitor/' . $server_monitor->server_monitor_id),
                    ];

                    /* Build a plain caught-data string for the generic message */
                    $dynamic_message_data = \Altum\NotificationHandlers::build_dynamic_message_data($notification_data);

                    /* Compose the generic notification text */
                    $notification_message = sprintf(
                        l('server_monitor.simple_notification.alert', $user->language),
                        $server_monitor->name,
                        $server_monitor->target,
                        l('server_monitor.' . $alert->metric, $user->language),
                        $post->{$alert->metric},
                        $dynamic_message_data,
                        $notification_data['url']
                    );

                    /* Prepare the email template used by the email handler */
                    $email_title   = sprintf(
                        l('cron.server_monitor.title', $user->language),
                        $server_monitor->name,
                        l('server_monitor.' . $alert->metric, $user->language),
                        $post->{$alert->metric}
                    );

                    $email_content = (new \Altum\View('partials/cron/server_monitor_alert', (array) $this))->run([
                        'user'          => $user,
                        'row'           => $server_monitor,
                        'alert'         => $alert,
                        $alert->metric  => $post->{$alert->metric},
                    ]);

                    $email_template = get_email_template(
                        [],
                        $email_title,
                        [],
                        $email_content
                    );

                    /* Build the context passed to the new NotificationHandlers class */
                    $context = [
                        /* User details */
                        'user'               => $user,

                        /* Email */
                        'email_template'     => $email_template,

                        /* Basic message for most integrations */
                        'message'            => $notification_message,

                        /* Push notifications */
                        'push_title'         => sprintf(
                            l('server_monitor.push_notification.title', $user->language),
                            $server_monitor->name,
                            $server_monitor->target
                        ),
                        'push_description'   => sprintf(
                            l('server_monitor.push_notification.description', $user->language),
                            l('server_monitor.' . $alert->metric, $user->language),
                            $post->{$alert->metric}
                        ),

                        /* WhatsApp */
                        'whatsapp_template'  => 'server_monitor',
                        'whatsapp_parameters'=> [
                            $server_monitor->name,
                            $server_monitor->target,
                            l('server_monitor.' . $alert->metric, $user->language),
                            $post->{$alert->metric},
                            $notification_data['url'],
                        ],

                        /* Twilio call */
                        'twilio_call_url'    => SITE_URL .
                            'twiml/heartbeat.simple_notification.is_ok?param1=' .
                            urlencode($server_monitor->name) .
                            '&param2=' . urlencode($server_monitor->target) .
                            '&param3=' . urlencode(l('server_monitor.' . $alert->metric, $user->language)) .
                            '&param4=' . urlencode($post->{$alert->metric}) .
                            '&param5=&param6=' . $notification_data['url'],

                        /* Internal notification */
                        'internal_icon'      => 'fas fa-microchip',

                        /* Discord */
                        'discord_color'      => '2664261',

                        /* Slack */
                        'slack_emoji'        => ':large_green_circle:',
                    ];

                    /* Send notifications */
                    \Altum\NotificationHandlers::process(
                        $notification_handlers,
                        $server_monitor->notifications,
                        $notification_data,
                        $context
                    );

                }

                if(!$should_trigger && $alert->is_triggered) {
                    $server_monitor->settings->alerts[$alert_key]->is_triggered = 0;
                    $server_monitor_settings_have_changed = true;
                }
            }
        }


        /* Update the Server monitor */
        db()->where('server_monitor_id', $server_monitor->server_monitor_id)->update('server_monitors', [
            'uptime' => (int) $post->uptime ?? 0,
            'network_total_download' => (int) $post->network_total_download ?? 0,
            'network_download' => (int) $post->network_download ?? 0,
            'network_total_upload' => (int) $post->network_total_upload ?? 0,
            'network_upload' => (int) $post->network_upload ?? 0,
            'os_name' => input_clean($post->os_name ?? ''),
            'os_version' => input_clean($post->os_version ?? ''),
            'kernel_name' => input_clean($post->kernel_name ?? ''),
            'kernel_version' => input_clean($post->kernel_version ?? ''),
            'kernel_release' => input_clean($post->kernel_release ?? ''),
            'cpu_architecture' => input_clean($post->cpu_architecture ?? ''),
            'cpu_usage' => round($post->cpu_usage, 2),
            'cpu_model' => input_clean($post->cpu_model ?? ''),
            'cpu_cores' => (int) $post->cpu_cores ?? 0,
            'cpu_frequency' => (int) $post->cpu_frequency ?? 0,
            'ram_usage' => round($post->ram_usage, 2),
            'ram_used' => (int) $post->ram_used ?? 0,
            'ram_total' => (int) $post->ram_total ?? 0,
            'disk_usage' => round($post->disk_usage, 2),
            'disk_used' => (int) $post->disk_used ?? 0,
            'disk_total' => (int) $post->disk_total ?? 0,
            'total_logs' => db()->inc(),
            'last_log_datetime' => get_date(),

            /* Update the settings with potential triggered alerts */
            'settings' => json_encode($server_monitor->settings),
        ]);

        /* Flush cache if settings have changed */

        /* Clear the cache */
        if($server_monitor_settings_have_changed) {
            cache()->deleteItemsByTag('server_monitor_id=' . $server_monitor->server_monitor_id);
            cache()->deleteItem('server_monitor?server_monitor_id=' . $server_monitor->server_monitor_id);
        }

        /* Database query */
        db()->insert('server_monitors_logs', [
            'server_monitor_id' => $server_monitor->server_monitor_id,
            'user_id' => $user->user_id,
            'cpu_usage' => round($post->cpu_usage, 2),
            'ram_usage' => round($post->ram_usage, 2),
            'disk_usage' => round($post->disk_usage, 2),
            'cpu_load_1' => isset($post->cpu_load_1) ? round($post->cpu_load_1, 2) : null,
            'cpu_load_5' => isset($post->cpu_load_5) ? round($post->cpu_load_5, 2) : null,
            'cpu_load_15' => isset($post->cpu_load_15) ? round($post->cpu_load_15, 2) : null,
            'network_download' => (int) $post->network_download ?? 0,
            'network_upload' => (int) $post->network_upload ?? 0,
            'datetime' => get_date(),
        ]);

        /* Clear out old dns monitor logs */
        if($user->plan_settings->logs_retention != -1) {
            $x_days_ago_datetime = (new \DateTime())->modify('-' . ($user->plan_settings->logs_retention ?? 90) . ' days')->format('Y-m-d H:i:s');
            database()->query("DELETE FROM `server_monitors_logs` WHERE `datetime` < '{$x_days_ago_datetime}' AND `user_id` = {$user->user_id}");
        }
    }

}
