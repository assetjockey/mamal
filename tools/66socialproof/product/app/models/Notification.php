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

namespace Altum\Models;

defined('ALTUMCODE') || die();

class Notification extends Model {

    /**
     * Delete one notification
     *
     * @param int $notification_id Notification id to delete
     * @param int|null $user_id Optional user id to scope deletion
     * @return void
     */
    public function delete($notification_id, $user_id = null) {

        $notification_id = (int) $notification_id;
        $user_id = $user_id ? (int) $user_id : null;

        /* Get the notification */
        $database = db()->where('notification_id', $notification_id);

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $notification = $database->getOne('notifications', ['user_id', 'campaign_id', 'notification_id', 'settings']);

        if(!$notification) return;

        $notification->settings = json_decode($notification->settings ?? '');

        /* Delete uploaded files */
        \Altum\Uploads::delete_uploaded_file($notification->settings->image ?? null, 'notifications');
        \Altum\Uploads::delete_uploaded_file($notification->settings->audio ?? null, 'notifications');

        /* Delete the notification */
        db()->where('notification_id', $notification_id)->delete('notifications');

        /* Clear the cache */
        cache()->deleteItem('notifications_total?user_id=' . $notification->user_id);
        cache()->deleteItem('notification?notification_id=' . $notification->notification_id);
        cache()->deleteItemsByTag('campaign_id=' . $notification->campaign_id);

    }

    /**
     * Delete multiple notifications
     *
     * @param array $notifications_ids Notification ids to delete
     * @param int|null $user_id Optional user id to avoid extra user lookup and scope deletion as an extra safety check
     * @param bool $is_verified Whether the notification ids were already verified before calling
     * @return void
     */
    public function bulk_delete($notifications_ids, $user_id = null, $is_verified = false) {
        $notifications_ids = array_filter(array_unique(array_map('intval', $notifications_ids)));
        $user_id = $user_id ? (int) $user_id : null;

        if(!$notifications_ids) {
            return;
        }

        /* Get all notifications */
        $database = db()->where('notification_id', $notifications_ids, 'IN');

        if($user_id && !$is_verified) {
            $database->where('user_id', $user_id);
        }

        $notifications = $database->get('notifications', null, ['user_id', 'campaign_id', 'notification_id', 'settings']);

        if(!$notifications) {
            return;
        }

        $users_ids = [];
        $campaigns_ids = [];

        /* Delete potential files from each notification */
        foreach($notifications as $notification) {
            $notification->settings = json_decode($notification->settings ?? '');

            /* Delete uploaded files */
            \Altum\Uploads::delete_uploaded_file($notification->settings->image ?? null, 'notifications');
            \Altum\Uploads::delete_uploaded_file($notification->settings->audio ?? null, 'notifications');

            $users_ids[] = (int) $notification->user_id;
            $campaigns_ids[] = (int) $notification->campaign_id;

            /* Clear the cache */
            cache()->deleteItem('notification?notification_id=' . $notification->notification_id);
        }

        $users_ids = array_filter(array_unique($users_ids));
        $campaigns_ids = array_filter(array_unique($campaigns_ids));

        /* Delete the notifications */
        $database = db()->where('notification_id', $notifications_ids, 'IN');

        if($user_id && !$is_verified) {
            $database->where('user_id', $user_id);
        }

        $database->delete('notifications');

        /* Clear the users cache */
        foreach($users_ids as $user_id) {
            cache()->deleteItem('notifications_total?user_id=' . $user_id);
        }

        /* Clear the campaigns cache */
        foreach($campaigns_ids as $campaign_id) {
            cache()->deleteItemsByTag('campaign_id=' . $campaign_id);
        }

    }

}
