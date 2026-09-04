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

class QrCode extends Model {

    public function delete($qr_code_id, $user_id = null) {
        $qr_code_id = (int) $qr_code_id;
        $user_id = $user_id ? (int) $user_id : null;

        /* Get the QR code */
        $database = db()->where('qr_code_id', $qr_code_id);

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        if(!$qr_code = $database->getOne('qr_codes', ['user_id', 'qr_code_id', 'qr_code', 'qr_code_logo', 'qr_code_background', 'qr_code_foreground'])) {
            return;
        }

        /* Delete the stored files */
        foreach(['qr_code', 'qr_code_logo', 'qr_code_background', 'qr_code_foreground'] as $image_key) {
            \Altum\Uploads::delete_uploaded_file($qr_code->{$image_key} ?? '', $image_key);
        }

        /* Delete the resource */
        db()->where('qr_code_id', $qr_code_id)->delete('qr_codes');

        /* Clear the cache */
        cache()->deleteItem('qr_codes_total?user_id=' . $qr_code->user_id);
    }

    public function bulk_delete($qr_codes_ids, $user_id = null) {

        $qr_codes_ids = array_filter(array_unique(array_map('intval', $qr_codes_ids)));
        $user_id = $user_id ? (int) $user_id : null;

        if(!$qr_codes_ids) {
            return;
        }

        /* Get all QR codes */
        $database = db()->where('qr_code_id', $qr_codes_ids, 'IN');

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $qr_codes = $database->get('qr_codes', null, ['user_id', 'qr_code_id', 'qr_code', 'qr_code_logo', 'qr_code_background', 'qr_code_foreground']);

        if(!$qr_codes) {
            return;
        }

        $users_ids = [];

        foreach($qr_codes as $qr_code) {

            /* Delete the stored files */
            foreach(['qr_code', 'qr_code_logo', 'qr_code_background', 'qr_code_foreground'] as $image_key) {
                \Altum\Uploads::delete_uploaded_file($qr_code->{$image_key} ?? '', $image_key);
            }

            $users_ids[] = (int) $qr_code->user_id;
        }

        $users_ids = array_filter(array_unique($users_ids));

        /* Delete the resources */
        $database = db()->where('qr_code_id', $qr_codes_ids, 'IN');

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $database->delete('qr_codes');

        /* Clear the cache */
        foreach($users_ids as $user_id) {
            cache()->deleteItem('qr_codes_total?user_id=' . $user_id);
        }
    }
}
