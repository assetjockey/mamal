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

defined('ALTUMCODE') || die();

class NotificationsAjax extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!empty($_POST) && (\Altum\Csrf::check('token') || \Altum\Csrf::check('global_token')) && isset($_POST['request_type'])) {

            switch($_POST['request_type']) {

                /* Status toggle */
                case 'is_enabled_toggle': $this->is_enabled_toggle(); break;

                /* Get conversion data */
                case 'read_data_conversion': $this->read_data_conversion(); break;

            }

        }

        if(!empty($_GET) && (\Altum\Csrf::check('token') || \Altum\Csrf::check('global_token')) && isset($_GET['request_type'])) {

            switch($_GET['request_type']) {

                /* Get conversion data */
                case 'read_data_conversion': $this->read_data_conversion(); break;

            }

        }

    }

    private function is_enabled_toggle() {
        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Response::json('Please create an account on the demo to test out this function.', 'error');

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.notifications')) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        $_POST['notification_id'] = (int) $_POST['notification_id'];

        /* Get the current status */
        $is_enabled = db()->where('notification_id', $_POST['notification_id'])->getValue('notifications', 'is_enabled');

        /* Update data in database */
        db()->where('notification_id', $_POST['notification_id'])->where('user_id', $this->user->user_id)->update('notifications', [
            'is_enabled' => (int) !$is_enabled,
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('campaign_id=' . $_POST['campaign_id']);

        Response::json('', 'success');
    }

    private function read_data_conversion() {
        $_GET['notification_id'] = (int) $_GET['notification_id'];
        $_GET['id'] = (int) $_GET['id'];

        /* Get the current status */
        $user_id = db()->where('notification_id', $_GET['notification_id'])->getValue('notifications', 'user_id');

        if($user_id && $user_id == $this->user->user_id) {

            /* Get the data from the conversions table */
            $conversion = db()->where('id', $_GET['id'])->where('notification_id', $_GET['notification_id'])->getOne('track_conversions');

            if($conversion) {

                $conversion->data = json_decode($conversion->data);
                $conversion->location = !empty($conversion->location) ? json_decode($conversion->location) : null;

                /* Generate the view */
                $data = [
                    'conversion' => $conversion,
                ];
                $view = new \Altum\View('notification/data/data.read_conversion.method', (array) $this);

                Response::json('', 'success', ['html' => $view->run($data)]);
            }

        }
    }

}
