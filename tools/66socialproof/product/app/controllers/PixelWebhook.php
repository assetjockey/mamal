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

class PixelWebhook extends Controller {

    public function index() {

		/* Make sure no cache is being used on the endpoint */
		header('Cache-Control: no-store');

        $notification_key = isset($this->params[0]) ? query_clean($this->params[0]) : null;

        if(!$notification_key) {
            die();
        }

        /* Make sure the api key exists */
        if(!$notification = db()->where('notification_key', $notification_key)->getOne('notifications')) {
            die();
        }

        /* Make sure the $notification_key belongs to an active user */
        if(!db()->where('user_id', $notification->user_id)->where('status', 1)->getOne('users')) {
            die();
        }

        /* Check for JSON submitted payload */
        $payload = @json_decode(@file_get_contents('php://input'), true);

        if($payload) {
            $_POST = (array) $payload;
        }

        /* Flatten everything recursively */
        $_POST = array_flatten($_POST);

        /* Location */
        $location = [];

        /* Clean all the received variables */
        foreach($_POST as $key => $value) {
            $_POST[$key] = input_clean($value, 512);

            if($key == 'city') {
                $location['city'] = $_POST[$key];
                unset($_POST[$key]);
            }

            if($key == 'country') {
                $location['country'] = $_POST[$key];
                unset($_POST[$key]);
            }

            if($key == 'country_code') {
                $location['country_code'] = $_POST[$key];
                unset($_POST[$key]);
            }
        }

        /* Make sure data is not empty */
        if(empty($_POST)) {
            exit();
        }

        /* Data for the conversion */
        $data = json_encode($_POST);
        $type = 'webhook';
        $location = !empty($location) ? json_encode($location) : '[]';

        /* Make sure that the data is not already submitted and exists for this notification */
        $result = database()->query("SELECT `id` FROM `track_conversions` WHERE `notification_id` = {$notification->notification_id} AND `data` = '{$data}'");

        if($result->num_rows) {
            die();
        }

        /* Insert the conversion log */
        db()->insert('track_conversions', [
            'user_id' => $notification->user_id,
            'notification_id' => $notification->notification_id,
            'type' => $type,
            'data' => $data,
            'location' => $location,
            'datetime' => get_date(),
        ]);

    }

}
