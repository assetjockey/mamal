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

class QrCode extends Model {

    /**
     * Delete one QR code
     *
     * @param int $qr_code_id QR code id to delete
     * @param int|null $user_id Optional user id to scope deletion
     * @return void
     */
    public function delete($qr_code_id, $user_id = null) {

        $qr_code_id = (int) $qr_code_id;
        $user_id = $user_id ? (int) $user_id : null;

        /* Get the QR code */
        $user_id_where = $user_id ? "AND `qr_codes`.`user_id` = {$user_id}" : null;
        $qr_code = database()->query("
            SELECT `qr_codes`.`user_id`, `qr_codes`.`qr_code_id`, `qr_codes`.`link_id`, `qr_codes`.`type`, `qr_codes`.`qr_code`, `qr_codes`.`qr_code_logo`, `qr_codes`.`qr_code_background`, `qr_codes`.`qr_code_foreground`, `links`.`file`
            FROM `qr_codes`
            LEFT JOIN `links` ON `qr_codes`.`link_id` = `links`.`link_id`
            WHERE `qr_codes`.`qr_code_id` = {$qr_code_id} {$user_id_where}
        ")->fetch_object();

        if(!$qr_code) {
            return;
        }

        /* Delete uploaded files */
        Uploads::delete_uploaded_file($qr_code->qr_code ?? '', 'qr_codes/logo');
        Uploads::delete_uploaded_file($qr_code->qr_code_logo ?? '', 'qr_codes/logo');
        Uploads::delete_uploaded_file($qr_code->qr_code_background ?? '', 'qr_code_background');
        Uploads::delete_uploaded_file($qr_code->qr_code_foreground ?? '', 'qr_code_foreground');

        if($qr_code->type == 'file' && $qr_code->link_id) {
            Uploads::delete_uploaded_file($qr_code->file, 'qr_code_files');

            /* Delete the link */
            db()->where('link_id', $qr_code->link_id)->delete('links');

            /* Clear the link cache */
            cache()->deleteItemsByTag('link_id=' . $qr_code->link_id);
            cache()->deleteItem('links?user_id=' . $qr_code->user_id);
            cache()->deleteItem('links_total?user_id=' . $qr_code->user_id);
            cache()->deleteItem('links_dashboard?user_id=' . $qr_code->user_id);
        }

        else {
            /* Delete from database */
            db()->where('qr_code_id', $qr_code_id)->delete('qr_codes');
        }

        /* Clear the cache */
        cache()->deleteItem('qr_codes_total?user_id=' . $qr_code->user_id);
        cache()->deleteItem('qr_codes_dashboard?user_id=' . $qr_code->user_id);

    }

    /**
     * Delete multiple QR codes
     *
     * @param array $qr_codes_ids QR code ids to delete
     * @param int|null $user_id Optional user id to avoid extra user lookup and scope deletion as an extra safety check
     * @param bool $is_verified Whether the QR code ids were already verified before calling
     * @return void
     */
    public function bulk_delete($qr_codes_ids, $user_id = null) {

        $qr_codes_ids = array_filter(array_unique(array_map('intval', $qr_codes_ids)));
        $user_id = $user_id ? (int) $user_id : null;

        if(!$qr_codes_ids) {
            return;
        }

        /* Get all QR codes */
        $qr_codes_ids_query = implode(',', $qr_codes_ids);
        $user_id_where = $user_id ? "AND `qr_codes`.`user_id` = {$user_id}" : null;
        $qr_codes_result = database()->query("
            SELECT `qr_codes`.`user_id`, `qr_codes`.`qr_code_id`, `qr_codes`.`link_id`, `qr_codes`.`type`, `qr_codes`.`qr_code`, `qr_codes`.`qr_code_logo`, `qr_codes`.`qr_code_background`, `qr_codes`.`qr_code_foreground`, `links`.`file`
            FROM `qr_codes`
            LEFT JOIN `links` ON `qr_codes`.`link_id` = `links`.`link_id`
            WHERE `qr_codes`.`qr_code_id` IN ({$qr_codes_ids_query}) {$user_id_where}
        ");
        $qr_codes = [];

        while($row = $qr_codes_result->fetch_object()) {
            $qr_codes[] = $row;
        }

        if(!$qr_codes) {
            return;
        }

        $users_ids = [];
        $qr_codes_ids = [];
        $links_ids = [];
        $links_users_ids = [];

        foreach($qr_codes as $qr_code) {

            /* Delete uploaded files */
            Uploads::delete_uploaded_file($qr_code->qr_code ?? '', 'qr_codes/logo');
            Uploads::delete_uploaded_file($qr_code->qr_code_logo ?? '', 'qr_codes/logo');
            Uploads::delete_uploaded_file($qr_code->qr_code_background ?? '', 'qr_code_background');
            Uploads::delete_uploaded_file($qr_code->qr_code_foreground ?? '', 'qr_code_foreground');

            if($qr_code->type == 'file' && $qr_code->link_id) {
                Uploads::delete_uploaded_file($qr_code->file, 'qr_code_files');
                $links_ids[] = (int) $qr_code->link_id;
                $links_users_ids[] = (int) $qr_code->user_id;
            }

            else {
                $qr_codes_ids[] = (int) $qr_code->qr_code_id;
            }

            $users_ids[] = (int) $qr_code->user_id;
        }

        $qr_codes_ids = array_filter(array_unique($qr_codes_ids));
        $links_ids = array_filter(array_unique($links_ids));
        $links_users_ids = array_filter(array_unique($links_users_ids));
        $users_ids = array_filter(array_unique($users_ids));

        /* Delete the links */
        if($links_ids) {
            db()->where('link_id', $links_ids, 'IN')->delete('links');
        }

        /* Delete from database */
        if($qr_codes_ids) {
            db()->where('qr_code_id', $qr_codes_ids, 'IN')->delete('qr_codes');
        }

        /* Clear the links cache */
        foreach($links_ids as $link_id) {
            cache()->deleteItemsByTag('link_id=' . $link_id);
        }

        foreach($links_users_ids as $user_id) {
            cache()->deleteItem('links?user_id=' . $user_id);
            cache()->deleteItem('links_total?user_id=' . $user_id);
            cache()->deleteItem('links_dashboard?user_id=' . $user_id);
        }

        /* Clear the users cache */
        foreach($users_ids as $user_id) {
            cache()->deleteItem('qr_codes_total?user_id=' . $user_id);
            cache()->deleteItem('qr_codes_dashboard?user_id=' . $user_id);
        }

    }

}
