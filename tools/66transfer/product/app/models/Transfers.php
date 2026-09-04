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

use Altum\Uploads;

defined('ALTUMCODE') || die();

class Transfers extends Model {

    public function get_transfer_full_url($transfer, $user, $domains = null) {

        /* Detect the URL of the link */
        if($transfer->domain_id) {

            /* Get available custom domains */
            if(!$domains) {
                $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($user);
            }

            if(isset($domains[$transfer->domain_id])) {
                $transfer->full_url = $domains[$transfer->domain_id]->scheme . $domains[$transfer->domain_id]->host . '/' . $transfer->url . '/';
            }

        } else {

            $transfer->full_url = SITE_URL . $transfer->url . '/';

        }

        return $transfer->full_url;
    }

    public function delete($transfer_id, $user_id = null) {

        $transfer_id = (int) $transfer_id;
        $user_id = $user_id ? (int) $user_id : null;

        /* Get the transfer */
        $database = db()->where('transfer_id', $transfer_id);

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $transfer = $database->getOne('transfers', ['transfer_id', 'user_id']);

        if(!$transfer) {
            return;
        }

        $user_id = (int) $transfer->user_id;

        /* Get all files related to the transfer */
        $files = db()->where('transfer_id', $transfer_id)->get('files', null, ['file_id', 'name', 'offload_id']);

        /* Go through the actual files and delete them */
        $file_ids = [];
        foreach($files as $file) {
            /* Delete the stored file */
            Uploads::delete_uploaded_file_and_potential_residue($file->name, 'files', $file->offload_id);

            $file_ids[] = (int) $file->file_id;
        }

        /* Delete the files resources */
        if($file_ids) {
            db()->where('file_id', $file_ids, 'IN')->delete('files');
        }

        /* Delete the resource */
        db()->where('transfer_id', $transfer_id)->delete('transfers');

        /* Recalculate file usage */
        (new \Altum\Models\Files())->calculate_and_update_file_usage($user_id);

        /* Clear the cache */
        cache()->deleteItem('transfers_total?user_id=' . $user_id);
        cache()->deleteItemsByTag('transfer_id=' . $transfer_id);
        cache()->deleteItem('files?transfer_id=' . $transfer_id);
    }

    public function bulk_delete($transfers_ids, $user_id = null, $is_verified = false) {
        $transfers_ids = array_filter(array_unique(array_map('intval', $transfers_ids)));
        $user_id = $user_id ? (int) $user_id : null;

        if(!$transfers_ids) {
            return;
        }

        /* Get all related users */
        $database = db()->where('transfer_id', $transfers_ids, 'IN');

        if($user_id && !$is_verified) {
            $database->where('user_id', $user_id);
        }

        $users_ids = $user_id && $is_verified ? [$user_id] : $database->getValue('transfers', 'user_id', null);
        $users_ids = array_filter(array_unique(array_map('intval', $users_ids)));

        if(!$users_ids) {
            return;
        }

        /* Get all files related to the transfers */
        $files = db()->where('transfer_id', $transfers_ids, 'IN')->get('files', null, ['file_id', 'transfer_id', 'name', 'offload_id']);

        $file_ids = [];

        foreach($files as $file) {
            /* Delete the stored file */
            Uploads::delete_uploaded_file_and_potential_residue($file->name, 'files', $file->offload_id);

            $file_ids[] = (int) $file->file_id;
        }

        /* Delete the files resources */
        if($file_ids) {
            db()->where('file_id', $file_ids, 'IN')->delete('files');
        }

        /* Delete the resources */
        $database = db()->where('transfer_id', $transfers_ids, 'IN');

        if($user_id && !$is_verified) {
            $database->where('user_id', $user_id);
        }

        $database->delete('transfers');

        /* Clear the transfers cache */
        foreach($transfers_ids as $transfer_id) {
            cache()->deleteItemsByTag('transfer_id=' . $transfer_id);
            cache()->deleteItem('files?transfer_id=' . $transfer_id);
        }

        /* Recalculate file usage and clear users cache */
        foreach($users_ids as $user_id) {
            (new \Altum\Models\Files())->calculate_and_update_file_usage($user_id);

            cache()->deleteItem('transfers_total?user_id=' . $user_id);
        }
    }

    public function get_expiration_datetime_text($expiration_datetime) {
        if(!$expiration_datetime) {
            return l('transfers.expiration_datetime_null');
        }

        $expiration_datetime_object = (new \DateTime($expiration_datetime));
        $now_datetime_object = (new \DateTime());

        if($now_datetime_object < $expiration_datetime_object) {
            return sprintf(l('transfers.expiration_datetime_x'), \Altum\Date::get_time_until($expiration_datetime));
        } else {
            return l('transfers.pending_deletion');
        }
    }

    public function get_downloads_limit_text($downloads, $downloads_limit) {
        return sprintf(l('transfers.downloads_limit'), $downloads, $downloads_limit ?? '∞');
    }
}
