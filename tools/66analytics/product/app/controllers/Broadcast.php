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


defined('ALTUMCODE') || die();

class Broadcast extends Controller {

    public function index() {

        function return_image() {
            header('Content-Type: image/gif');
            echo base64_decode('R0lGODlhAQABAIAAAP///wAAACwAAAAAAQABAAACAkQBADs=');
            die();
        }

        if(!isset($_GET['id'])) {
            throw_404();
        }

        /* Decode the base64 id */
        $id = base64_decode($_GET['id']);

        /* Parse the parameters */
        parse_str($id, $parameters);

        /* Make sure all parameters are present */
        if(!isset($parameters['broadcast_id'])) {
            throw_404();
        }

        $parameters['broadcast_id'] = (int) $parameters['broadcast_id'];
        $url = isset($_GET['url']) ? get_url($_GET['url']) : null;

        /* Make sure the broadcast & user exists properly */
        if(!$broadcast = db()->where('broadcast_id', $parameters['broadcast_id'])->getOne('broadcasts')) {
            throw_404();
        }

        if(!in_array($broadcast->status, ['sent', 'processing'])) {
            throw_404();
        }

        $broadcast->users_ids = json_decode($broadcast->users_ids);
        $broadcast->settings = json_decode($broadcast->settings);
        $is_system_email = isset($broadcast->settings->is_system_email) ? (bool) $broadcast->settings->is_system_email : true;

        if($is_system_email) {
            if(!isset($parameters['user_id'])) {
                throw_404();
            }

            $parameters['user_id'] = (int) $parameters['user_id'];

            if(!$recipient_id = db()->where('user_id', $parameters['user_id'])->getValue('users', 'user_id')) {
                throw_404();
            }

            $statistic_column = 'user_id';
            $user_id = $recipient_id;
            $broadcast_subscriber_id = null;
        }

        else {
            if(!isset($parameters['broadcast_subscriber_id'])) {
                throw_404();
            }

            $parameters['broadcast_subscriber_id'] = (int) $parameters['broadcast_subscriber_id'];

            if(!$broadcast_subscriber = db()->where('broadcast_subscriber_id', $parameters['broadcast_subscriber_id'])->getOne('broadcast_subscribers', ['broadcast_subscriber_id', 'user_id'])) {
                throw_404();
            }

            $recipient_id = $broadcast_subscriber->broadcast_subscriber_id;
            $statistic_column = 'broadcast_subscriber_id';
            $user_id = $broadcast_subscriber->user_id;
            $broadcast_subscriber_id = $broadcast_subscriber->broadcast_subscriber_id;
        }

        /* Make sure the recipient is included in the broadcast */
        if(!in_array($recipient_id, $broadcast->users_ids)) {
            throw_404();
        }

        /* Prepare for database insertion */
        $type = $url ? 'click' : 'view';
        $target = $url ?? null;

        /* Make sure the log was not already created */
        $broadcast_statistic = db()
            ->where('broadcast_id', $parameters['broadcast_id'])
            ->where($statistic_column, $recipient_id)
            ->where('type', $type)
            ->where('target', $target)
            ->getValue('broadcasts_statistics', 'id');

        if($broadcast_statistic && $type == 'view') {
            return_image();
        }

        if($broadcast_statistic && $type == 'click') {
            header('Location: ' . $url); die();
        }

        if($type == 'click' && !str_contains($broadcast->content, $url)) {
            throw_404();
        }

        /* Insert log and update stats */
        db()->insert('broadcasts_statistics', [
            'broadcast_id' => $parameters['broadcast_id'],
            'user_id' => $user_id,
            'broadcast_subscriber_id' => $broadcast_subscriber_id,
            'type' => $type,
            'target' => $target,
            'datetime' => get_date(),
        ]);

        switch($type) {
            case 'view':
                db()->where('broadcast_id', $parameters['broadcast_id'])->update('broadcasts', [
                    'views' => db()->inc()
                ]);

                return_image();
                break;

            case 'click':
                db()->where('broadcast_id', $parameters['broadcast_id'])->update('broadcasts', [
                    'clicks' => db()->inc()
                ]);

                header('Location: ' . $url);
                break;
        }

        die();
    }

}
