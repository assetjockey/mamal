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

namespace Altum\Models;

defined('ALTUMCODE') || die();

class DigitalWallets extends Model {

    public function get_digital_wallets_by_user_id($user_id) {
        if(!\Altum\Plugin::is_active('digital-wallets') || !settings()->digital_wallets->is_enabled) return [];

        $user_id = (int) $user_id;

        /* Get the user wallet cards */
        $digital_wallets = [];

        /* Try to check if the user wallet cards exist via the cache */
        $cache_instance = cache()->getItem('digital_wallets?user_id=' . $user_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $digital_wallets_result = database()->query("SELECT * FROM `digital_wallets` WHERE `user_id` = {$user_id}");
            while($row = $digital_wallets_result->fetch_object()) {
                $row->settings = json_decode($row->settings ?? '');
                $digital_wallets[$row->digital_wallet_id] = $row;
            }

            cache()->save(
                $cache_instance->set($digital_wallets)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $user_id)
            );

        } else {

            /* Get cache */
            $digital_wallets = $cache_instance->get();

        }

        return $digital_wallets;

    }

    public function delete($digital_wallet_id, $user_id = null) {

        $digital_wallet_id = (int) $digital_wallet_id;
        $user_id = $user_id ? (int) $user_id : null;

        /* Get the digital wallet */
        $database = db()->where('digital_wallet_id', $digital_wallet_id);

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        if(!$digital_wallet = $database->getOne('digital_wallets', ['user_id', 'digital_wallet_id', 'settings'])) {
            return;
        }

        $digital_wallet->settings = json_decode($digital_wallet->settings ?? '') ?: (object) [];

        /* Delete stored files */
        foreach(['logo', 'image'] as $image_key) {
            \Altum\Uploads::delete_uploaded_file($digital_wallet->settings->{$image_key} ?? null, 'digital_wallets');
        }

        /* Delete the resource */
        db()->where('digital_wallet_id', $digital_wallet_id)->delete('digital_wallets');

        /* Clear the cache */
        cache()->deleteItem('digital_wallets?user_id=' . $digital_wallet->user_id);
    }

    public function bulk_delete($digital_wallets_ids, $user_id = null) {

        $digital_wallets_ids = array_filter(array_unique(array_map('intval', $digital_wallets_ids)));
        $user_id = $user_id ? (int) $user_id : null;

        if(!$digital_wallets_ids) {
            return;
        }

        /* Get all digital wallets */
        $database = db()->where('digital_wallet_id', $digital_wallets_ids, 'IN');

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $digital_wallets = $database->get('digital_wallets', null, ['user_id', 'digital_wallet_id', 'settings']);

        if(!$digital_wallets) {
            return;
        }

        $users_ids = [];

        foreach($digital_wallets as $digital_wallet) {
            $digital_wallet->settings = json_decode($digital_wallet->settings ?? '') ?: (object) [];

            /* Delete stored files */
            foreach(['logo', 'image'] as $image_key) {
                \Altum\Uploads::delete_uploaded_file($digital_wallet->settings->{$image_key} ?? null, 'digital_wallets');
            }

            $users_ids[] = (int) $digital_wallet->user_id;
        }

        $users_ids = array_filter(array_unique($users_ids));

        /* Delete the resources */
        $database = db()->where('digital_wallet_id', $digital_wallets_ids, 'IN');

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $database->delete('digital_wallets');

        /* Clear the cache */
        foreach($users_ids as $user_id) {
            cache()->deleteItem('digital_wallets?user_id=' . $user_id);
        }
    }

}
