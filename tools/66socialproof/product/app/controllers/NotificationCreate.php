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
use Altum\Notification;

defined('ALTUMCODE') || die();

class NotificationCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.notifications')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('dashboard');
        }

        $campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Make sure the campaign exists and is accessible to the user */
        if(!$campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->user->user_id)->getOne('campaigns')) {
            redirect('dashboard');
        }

        $campaign->branding = json_decode($campaign->branding);

        $enabled_notifications = require APP_PATH . 'includes/enabled_notifications.php';

        /* Check for plan limit */
        $notifications_total = database()->query("SELECT COUNT(*) AS `total` FROM `notifications` WHERE `user_id` = {$this->user->user_id} AND `campaign_id` = {$campaign->campaign_id}")->fetch_object()->total;
        if($this->user->plan_settings->notifications_limit != -1 && $notifications_total >= $this->user->plan_settings->notifications_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('campaign/' . $campaign->campaign_id);
        }

        if(!empty($_POST)) {
            $_POST['type'] = trim(query_clean($_POST['type']));
            $_POST['campaign_id'] = (int) $_POST['campaign_id'];

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['type', 'campaign_id'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            /* If the notification settings is not set it means we got an invalid type */
            if(!isset($enabled_notifications[$_POST['type']])) {
                redirect('notification-create');
            }

            /* Check for possible errors */
            if(!db()->where('campaign_id', $_POST['campaign_id'])->where('user_id', $this->user->user_id)->getValue('campaigns', 'campaign_id')) {
                redirect('notification-create');
            }

            /* Check for permission of usage of the notification */
            $plan_enabled_notifications = $this->user->plan_settings->enabled_notifications;
            $notification_has_direct_access = property_exists($plan_enabled_notifications, $_POST['type']);
            $notification_is_enabled = $notification_has_direct_access ? $plan_enabled_notifications->{$_POST['type']} : false;

            if(!$notification_is_enabled) {
                redirect('notification-create');
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                /* Determine the default settings */
                $notification_settings = $enabled_notifications[$_POST['type']];
                $notification_key = md5($this->user->user_id . $_POST['campaign_id'] . $_POST['type'] . time());
                $name = generate_prefilled_dynamic_names(l('notifications.notification'));

                /* Insert to database */
                $notification_id = db()->insert('notifications', [
                    'user_id' => $this->user->user_id,
                    'campaign_id' => $_POST['campaign_id'],
                    'name' => $name,
                    'type' => $_POST['type'],
                    'settings' => json_encode($notification_settings),
                    'notification_key' => $notification_key,
                    'is_enabled' => 0,
                    'datetime' => get_date(),
                ]);

                $notification_settings['custom_css'] = sprintf(l('global.custom_css_placeholder'), $notification_id);

                db()->where('notification_id', $notification_id)->update('notifications', [
                    'settings' => json_encode($notification_settings),
                ]);

                /* Clear the cache */
                cache()->deleteItem('notifications_total?user_id=' . $this->user->user_id);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($name) . '</strong>'));

                /* Redirect */
                redirect('notification/' . $notification_id);
            }
        }

        /* Prepare the view */
        $data = [
            'campaign' => $campaign,
            'notifications' => require APP_PATH . 'includes/enabled_notifications.php',
            'notifications_config'  => require APP_PATH . 'includes/enabled_notifications.php'
        ];

        $view = new \Altum\View('notification-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
