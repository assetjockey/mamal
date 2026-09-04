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

class Campaign extends Model {
	public function get_campaign_by_pixel_key($pixel_key) {

		/* Try to check if the store posts exists via the cache */
		$cache_instance = cache()->getItem('campaign?pixel_key=' . md5($pixel_key));

		/* Set cache if not existing */
		if(is_null($cache_instance->get())) {

			/* Get data from the database */
			$data = db()->where('pixel_key', $pixel_key)->getOne('campaigns');

			if($data) {
				/* Save to cache */
				cache()->save(
					$cache_instance->set($data)->expiresAfter(43200)->addTag('user_id=' . $data->user_id)->addTag('campaign_id=' . $data->campaign_id)
				);
			}

		} else {

			/* Get cache */
			$data = $cache_instance->get();

		}

		return $data;
	}


	/**
     * Delete one campaign
     *
     * @param int $campaign_id Campaign id to delete
     * @param int|null $user_id Optional user id to scope deletion
     * @return void
     */
    public function delete($campaign_id, $user_id = null) {

        $campaign_id = (int) $campaign_id;
        $user_id = $user_id ? (int) $user_id : null;

        /* Get the campaign */
        $database = db()->where('campaign_id', $campaign_id);

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $campaign = $database->getOne('campaigns', ['campaign_id', 'user_id']);

        if(!$campaign) return;

        $user_id = (int) $campaign->user_id;

        /* Get all notifications related to the campaign */
        $notifications_ids = db()->where('campaign_id', $campaign_id)->where('user_id', $user_id)->getValue('notifications', 'notification_id', null);

        /* Delete all notifications related to the campaign */
        if($notifications_ids) {
            (new \Altum\Models\Notification())->bulk_delete($notifications_ids, $user_id, true);
        }

        /* Delete the campaign */
        db()->where('campaign_id', $campaign_id)->delete('campaigns');

        /* Clear the cache */
        cache()->deleteItemsByTag('campaign_id=' . $campaign_id);

    }

    /**
     * Delete multiple campaigns
     *
     * @param array $campaigns_ids Campaign ids to delete
     * @param int|null $user_id Optional user id to avoid extra user lookup and scope deletion as an extra safety check
     * @param bool $is_verified Whether the campaign ids were already verified before calling
     * @return void
     */
    public function bulk_delete($campaigns_ids, $user_id = null, $is_verified = false) {
        $campaigns_ids = array_filter(array_unique(array_map('intval', $campaigns_ids)));
        $user_id = $user_id ? (int) $user_id : null;

        if(!$campaigns_ids) {
            return;
        }

        /* Get all campaigns */
        $database = db()->where('campaign_id', $campaigns_ids, 'IN');

        if($user_id && !$is_verified) {
            $database->where('user_id', $user_id);
        }

        $campaigns = $database->get('campaigns', null, ['campaign_id', 'user_id']);

        if(!$campaigns) {
            return;
        }

        $campaigns_ids = [];
        $users_ids = [];

        foreach($campaigns as $campaign) {
            $campaigns_ids[] = (int) $campaign->campaign_id;
            $users_ids[] = (int) $campaign->user_id;
        }

        $users_ids = array_filter(array_unique($users_ids));

        /* Get all notifications related to the campaigns */
        $notifications_database = db()->where('campaign_id', $campaigns_ids, 'IN');

        if($user_id) {
            $notifications_database->where('user_id', $user_id);
        }

        $notifications_ids = $notifications_database->getValue('notifications', 'notification_id', null);

        /* Delete all notifications related to the campaigns */
        if($notifications_ids) {
            (new \Altum\Models\Notification())->bulk_delete($notifications_ids, $user_id, true);
        }

        /* Delete the campaigns */
        $database = db()->where('campaign_id', $campaigns_ids, 'IN');

        if($user_id && !$is_verified) {
            $database->where('user_id', $user_id);
        }

        $database->delete('campaigns');

        /* Clear the campaigns cache */
        foreach($campaigns_ids as $campaign_id) {
            cache()->deleteItemsByTag('campaign_id=' . $campaign_id);
        }

        /* Clear the users cache */
        foreach($users_ids as $user_id) {
            cache()->deleteItem('campaigns_total?user_id=' . $user_id);
        }

    }

}
