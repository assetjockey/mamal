<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
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

namespace Altum\Helpers;

defined('ALTUMCODE') || die();

class Monitor {

    public static function check($monitor, $ping_servers = [], $exclude_ping_server_id_if_possible = null) {

        /* If we have multiple ping servers available, exclude a particular ping server */
        if(count($ping_servers) > 1 && $exclude_ping_server_id_if_possible) {
            unset($monitor->ping_servers_ids[$exclude_ping_server_id_if_possible]);
        }

        /* Select a server to do the request */
        $ping_server_id = count($monitor->ping_servers_ids) ? $monitor->ping_servers_ids[array_rand($monitor->ping_servers_ids)] : 1;

        /* Use default if the ping server is not accessible for some reason */
        if(!isset($ping_servers[$ping_server_id])) {
            $ping_server_id = 1;
        }
        $ping_server = $ping_servers[$ping_server_id];

        /* Error details */
        $error = null;

        /* Local request, native server */
        if($ping_server_id == 1) {
            switch($monitor->type) {

                /* Fsockopen */
                case 'port':

                    $ping = new \Altum\Helpers\CustomPing($monitor->target);
                    $ping->setTimeout($monitor->settings->timeout_seconds);
                    $ping->setPort($monitor->port);
                    $latency = $ping->ping('fsockopen');

                    if($latency !== false) {
                        $response_status_code = 0;
                        $response_time = $latency;

                        /*  :)  */
                        $is_ok = 1;
                    } else {
                        $response_status_code = 0;
                        $response_time = 0;

                        /*  :)  */
                        $is_ok = 0;

                        $error = [
                            'type' => $ping->get_error_type(),
                            'message' => $ping->get_error_message(),
                        ];
                    }

                    break;

                /* Ping check */
                case 'ping':

                    $ping = new \Altum\Helpers\CustomPing($monitor->target);
                    $ping->setTimeout($monitor->settings->timeout_seconds);
                    $ping->set_ipv($monitor->settings->ping_ipv);
                    $latency = $ping->ping(settings()->monitors_heartbeats->monitors_ping_method);

                    if($latency !== false) {
                        $response_status_code = 0;
                        $response_time = $latency;

                        /*  :)  */
                        $is_ok = 1;
                    } else {
                        $response_status_code = 0;
                        $response_time = 0;

                        /*  :)  */
                        $is_ok = 0;

                        $error = [
                            'type' => $ping->get_error_type(),
                            'message' => $ping->get_error_message(),
                        ];
                    }

                    break;

                /* Websites check */
                case 'website':

                    /* Set timeout */
                    \Unirest\Request::timeout($monitor->settings->timeout_seconds);

                    /* Set follow redirects */
                    \Unirest\Request::curlOpts([
                        CURLOPT_FOLLOWLOCATION => $monitor->settings->follow_redirects ?? true,
                        CURLOPT_MAXREDIRS => 5,
                    ]);

                    try {

                        /* Cache buster */
                        if($monitor->settings->cache_buster_is_enabled) {
                            $query = parse_url($monitor->target, PHP_URL_QUERY);

                            $monitor->target .= ($query ? '&' : '?') . 'cache_buster=' . mb_substr(md5(uniqid('', true) . random_bytes(16)), 0, 8);
                        }

                        /* Verify SSL */
                        \Unirest\Request::verifyPeer($monitor->settings->verify_ssl_is_enabled ?? true);

                        /* Set auth */
                        \Unirest\Request::auth($monitor->settings->request_basic_auth_username ?? '', $monitor->settings->request_basic_auth_password ?? '');

                        /* Make the request to the website */
                        $method = mb_strtolower($monitor->settings->request_method ?? settings()->monitors_heartbeats->monitors_default_request_method);

                        /* Prepare request headers */
                        $request_headers = [];

                        /* Set custom user agent */
                        if(settings()->monitors_heartbeats->user_agent) {
                            $request_headers['User-Agent'] = settings()->monitors_heartbeats->user_agent;
                        }

                        foreach($monitor->settings->request_headers as $request_header) {
                            $request_headers[$request_header->name] = $request_header->value;
                        }

                        /* Bugfix on Unirest php library for Head requests */
                        if($method == 'head') {
                            \Unirest\Request::curlOpt(CURLOPT_NOBODY, true);
                        }

                        if(in_array($method, ['post', 'put', 'patch'])) {
                            $response = \Unirest\Request::{$method}($monitor->target, $request_headers, $monitor->settings->request_body ?? []);
                        } else {
                            $response = \Unirest\Request::{$method}($monitor->target, $request_headers);
                        }

                        /* Clear custom settings */
                        \Unirest\Request::clearCurlOpts();

                        /* Get info after the request */
                        $info = \Unirest\Request::getInfo();

                        /* Some needed variables */
                        $response_status_code = $info['http_code'];
                        $response_time = $info['total_time'] * 1000;

                        /* Check the response to see how we interpret the results */
                        $is_ok = 1;

                        /* Check against response code */
                        if(
                            (is_array($monitor->settings->response_status_code) && !in_array($response_status_code, $monitor->settings->response_status_code))
                            || (!is_array($monitor->settings->response_status_code) && $response_status_code != ($monitor->settings->response_status_code ?? 200))
                        ) {
                            $is_ok = 0;
                            $error = ['type' => 'response_status_code'];
                        }

                        if(isset($monitor->settings->response_body) && $monitor->settings->response_body && mb_strpos($response->raw_body, $monitor->settings->response_body) === false) {
                            $is_ok = 0;
                            $error = ['type' => 'response_body'];
                            $response_body = $response->raw_body;
                        }

                        if(isset($monitor->settings->response_headers)) {
                            foreach($monitor->settings->response_headers as $response_header) {
                                $response_header->name = mb_strtolower($response_header->name);

                                if(!isset($response->headers[$response_header->name]) || (isset($response->headers[$response_header->name]) && $response->headers[$response_header->name] != $response_header->value)) {
                                    $is_ok = 0;
                                    $error = ['type' => 'response_header'];
                                    break;
                                }
                            }
                        }

                    } catch (\Exception $exception) {
                        $response_status_code = 0;
                        $response_time = 0;
                        $error = [
                            'type' => 'exception',
                            'code' => curl_errno(\Unirest\Request::getCurlHandle()),
                            'message' => curl_error(\Unirest\Request::getCurlHandle()),
                        ];

                        /*  :)  */
                        $is_ok = 0;
                    }

                    break;
            }
        }

        /* Outside request, via a random ping server */
        else {

            /* Request the data from outside source */
            try {
                \Unirest\Request::timeout($monitor->settings->timeout_seconds + 3);
                $response = \Unirest\Request::post($ping_server->url, [], [
                    'user_agent' => settings()->monitors_heartbeats->user_agent,
                    'ping_method' => settings()->monitors_heartbeats->monitors_ping_method,
                    'type' => $monitor->type,
                    'target' => $monitor->target,
                    'port' => $monitor->port,
                    'settings' => json_encode($monitor->settings),
                    'api_key' => $ping_server->api_key,
                ]);
            } catch (\Exception $exception) {
                $is_ok = 0;
                $response_time = 0;
                $response_status_code = 0;
                $error = 'Ping server error: ' . $exception->getMessage();
            }

            /* Make sure we got the proper result back */
            if(!isset($exception)) {
                $is_ok = $response->body->is_ok;
                $response_time = $response->body->response_time;
                $response_status_code = $response->body->response_status_code;
                $response_body = $response->body->response_body;
                $error = $response->body->error;
            }
        }

        return [
            'ping_server_id' => $ping_server_id,
            'is_ok' => $is_ok,
            'response_time' => $response_time,
            'response_status_code' => $response_status_code,
            'response_body' => $response_body ?? null,
            'error' => $error
        ];

    }

    public static function vars($monitor, $check) {
        /* Assuming, based on the check interval */
        $uptime_seconds = $check['is_ok'] ? $monitor->uptime_seconds + $monitor->settings->check_interval_seconds : $monitor->uptime_seconds;
        $downtime_seconds = !$check['is_ok'] ? $monitor->downtime_seconds + $monitor->settings->check_interval_seconds : $monitor->downtime_seconds;

        /* Recalculate uptime and downtime */
        $uptime = $uptime_seconds > 0 ? $uptime_seconds / ($uptime_seconds + $downtime_seconds) * 100 : 0;
        $downtime = 100 - $uptime;

        $total_ok_checks = $check['is_ok'] ? $monitor->total_ok_checks + 1 : $monitor->total_ok_checks;
        $total_not_ok_checks = !$check['is_ok'] ? $monitor->total_not_ok_checks + 1 : $monitor->total_not_ok_checks;
        $last_check_datetime = get_date();
        $next_check_datetime = (new \DateTime())->modify('+' . ($monitor->settings->check_interval_seconds ?? 3600) . ' seconds')->format('Y-m-d H:i:s');
        $last_ok_datetime = $check['is_ok'] ? get_date() : $monitor->last_ok_datetime;
        $last_not_ok_datetime = !$check['is_ok'] ? get_date() : $monitor->last_not_ok_datetime;
        $average_response_time = $check['response_time'] ? ($monitor->average_response_time + $check['response_time']) / ($monitor->total_ok_checks == 0 ? 1 : 2) : $monitor->average_response_time;

        /* Does the monitor have history */
        if($monitor->last_check_datetime) {
            $main_ok_datetime = !$monitor->is_ok && $check['is_ok'] ? get_date() : $monitor->main_ok_datetime;
            $main_not_ok_datetime = $monitor->is_ok && !$check['is_ok'] ? get_date() : $monitor->main_not_ok_datetime;
        } else {
            $main_ok_datetime = $check['is_ok'] ? get_date() : null;
            $main_not_ok_datetime = !$check['is_ok'] ? get_date() : null;
        }

        /* Keep the last logs for immediate access */
        $last_logs = [];

        for($i = 1; $i <= 6; $i++) {
            $last_logs[] = isset($monitor->last_logs[$i]) ? $monitor->last_logs[$i] : [];
        }

        $last_logs[] = [
            'incident_id' => null,
            'is_ok' => $check['is_ok'],
            'response_time' => $check['response_time'],
            'response_status_code' => $check['response_status_code'],
            'error' => $check['error'],
            'ping_server_id' => $check['ping_server_id'],
            'datetime' => get_date(),
        ];

        return [
            'uptime_seconds' => $uptime_seconds,
            'downtime_seconds' => $downtime_seconds,
            'uptime' => $uptime,
            'downtime' => $downtime,
            'total_ok_checks' => $total_ok_checks,
            'total_not_ok_checks' => $total_not_ok_checks,
            'last_check_datetime' => $last_check_datetime,
            'next_check_datetime' => $next_check_datetime,
            'main_ok_datetime' => $main_ok_datetime,
            'last_ok_datetime' => $last_ok_datetime,
            'main_not_ok_datetime' => $main_not_ok_datetime,
            'last_not_ok_datetime' => $last_not_ok_datetime,
            'average_response_time' => $average_response_time,
            'last_logs' => $last_logs,
        ];
    }

}
