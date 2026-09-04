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

namespace Altum\Models;

use Altum\Uploads;

defined('ALTUMCODE') || die();

class AiQrCode extends Model {

    /**
     * Delete one AI QR code
     *
     * @param int $ai_qr_code_id AI QR code id to delete
     * @param int|null $user_id Optional user id to scope deletion
     * @return void
     */
    public function delete($ai_qr_code_id, $user_id = null) {

        $ai_qr_code_id = (int) $ai_qr_code_id;
        $user_id = $user_id ? (int) $user_id : null;

        /* Get the AI QR code */
        $database = db()->where('ai_qr_code_id', $ai_qr_code_id);

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $ai_qr_code = $database->getOne('ai_qr_codes', ['user_id', 'ai_qr_code_id', 'ai_qr_code']);

        if(!$ai_qr_code) {
            return;
        }

        Uploads::delete_uploaded_file($ai_qr_code->ai_qr_code, 'ai_qr_codes');

        /* Delete from database */
        db()->where('ai_qr_code_id', $ai_qr_code_id)->delete('ai_qr_codes');

        /* Clear the cache */
        cache()->deleteItem('ai_qr_codes_total?user_id=' . $ai_qr_code->user_id);
        cache()->deleteItem('ai_qr_codes_dashboard?user_id=' . $ai_qr_code->user_id);

    }

    /**
     * Delete multiple AI QR codes
     *
     * @param array $ai_qr_codes_ids AI QR code ids to delete
     * @param int|null $user_id Optional user id to avoid extra user lookup and scope deletion as an extra safety check
     * @param bool $is_verified Whether the AI QR code ids were already verified before calling
     * @return void
     */
    public function bulk_delete($ai_qr_codes_ids, $user_id = null) {

        $ai_qr_codes_ids = array_filter(array_unique(array_map('intval', $ai_qr_codes_ids)));
        $user_id = $user_id ? (int) $user_id : null;

        if(!$ai_qr_codes_ids) {
            return;
        }

        /* Get all AI QR codes */
        $database = db()->where('ai_qr_code_id', $ai_qr_codes_ids, 'IN');

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $ai_qr_codes = $database->get('ai_qr_codes', null, ['user_id', 'ai_qr_code_id', 'ai_qr_code']);

        if(!$ai_qr_codes) {
            return;
        }

        $users_ids = [];
        $ai_qr_codes_ids = [];

        foreach($ai_qr_codes as $ai_qr_code) {

            /* Delete the stored file */
            Uploads::delete_uploaded_file($ai_qr_code->ai_qr_code, 'ai_qr_codes');

            $ai_qr_codes_ids[] = (int) $ai_qr_code->ai_qr_code_id;
            $users_ids[] = (int) $ai_qr_code->user_id;
        }

        $ai_qr_codes_ids = array_filter(array_unique($ai_qr_codes_ids));
        $users_ids = array_filter(array_unique($users_ids));

        /* Delete from database */
        db()->where('ai_qr_code_id', $ai_qr_codes_ids, 'IN')->delete('ai_qr_codes');

        /* Clear the users cache */
        foreach($users_ids as $user_id) {
            cache()->deleteItem('ai_qr_codes_total?user_id=' . $user_id);
            cache()->deleteItem('ai_qr_codes_dashboard?user_id=' . $user_id);
        }

    }

}
