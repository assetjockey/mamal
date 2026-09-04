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

class TransferRequests extends Model {

    public function get_transfer_request_full_url($transfer_request, $user, $domains = null) {

        /* Detect the URL of the link */
        if($transfer_request->domain_id) {

            /* Get available custom domains */
            if(!$domains) {
                $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($user);
            }

            if(isset($domains[$transfer_request->domain_id])) {
                $transfer_request->full_url = $domains[$transfer_request->domain_id]->scheme . $domains[$transfer_request->domain_id]->host . '/' . $transfer_request->url . '/';
            }

        } else {

            $transfer_request->full_url = SITE_URL . 'r/' . $transfer_request->url . '/';

        }

        return $transfer_request->full_url;
    }

    /**
     * Delete one transfer request
     *
     * @param int $transfer_request_id Transfer request id to delete
     * @param int|null $user_id Optional user id to scope deletion
     * @return void
     */
    public function delete($transfer_request_id, $user_id = null) {

        $transfer_request_id = (int) $transfer_request_id;
        $user_id = $user_id ? (int) $user_id : null;

        /* Get the transfer request */
        $database = db()->where('transfer_request_id', $transfer_request_id);

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $transfer_request = $database->getOne('transfer_requests', ['transfer_request_id', 'user_id']);

        if(!$transfer_request) {
            return;
        }

        $user_id = (int) $transfer_request->user_id;

        /* Get all files related to the transfer request */
        $files = db()->where('transfer_request_id', $transfer_request_id)->get('files', null, ['file_id', 'name', 'offload_id']);

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
        $database = db()->where('transfer_request_id', $transfer_request_id);

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $database->delete('transfer_requests');

        /* Recalculate file usage */
        (new \Altum\Models\Files())->calculate_and_update_file_usage($user_id);

        /* Clear the cache */
        cache()->deleteItem('transfer_requests_total?user_id=' . $user_id);
        cache()->deleteItemsByTag('transfer_request_id=' . $transfer_request_id);
        cache()->deleteItem('files?transfer_request_id=' . $transfer_request_id);
    }

    /**
     * Delete multiple transfer requests
     *
     * @param array $transfer_requests_ids Transfer request ids to delete
     * @param int|null $user_id Optional user id to avoid extra user lookup and scope deletion as an extra safety check
     * @param bool $is_verified Whether the transfer request ids were already verified before calling
     * @return void
     */
    public function bulk_delete($transfer_requests_ids, $user_id = null, $is_verified = false) {

        $transfer_requests_ids = array_filter(array_unique(array_map('intval', $transfer_requests_ids)));
        $user_id = $user_id ? (int) $user_id : null;

        if(!$transfer_requests_ids) {
            return;
        }

        /* Get all valid transfer requests */
        $database = db()->where('transfer_request_id', $transfer_requests_ids, 'IN');

        if($user_id && !$is_verified) {
            $database->where('user_id', $user_id);
        }

        $transfer_requests = $database->get('transfer_requests', null, ['transfer_request_id', 'user_id']);

        if(!$transfer_requests) {
            return;
        }

        $transfer_requests_ids = [];
        $users_ids = [];

        foreach($transfer_requests as $transfer_request) {
            $transfer_requests_ids[] = (int) $transfer_request->transfer_request_id;
            $users_ids[] = (int) $transfer_request->user_id;
        }

        $users_ids = array_filter(array_unique($users_ids));

        /* Get all files related to the transfer requests */
        $files = db()->where('transfer_request_id', $transfer_requests_ids, 'IN')->get('files', null, ['file_id', 'transfer_request_id', 'name', 'offload_id']);

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
        $database = db()->where('transfer_request_id', $transfer_requests_ids, 'IN');

        if($user_id && !$is_verified) {
            $database->where('user_id', $user_id);
        }

        $database->delete('transfer_requests');

        /* Clear the transfer requests cache */
        foreach($transfer_requests_ids as $transfer_request_id) {
            cache()->deleteItemsByTag('transfer_request_id=' . $transfer_request_id);
            cache()->deleteItem('files?transfer_request_id=' . $transfer_request_id);
        }

        /* Recalculate file usage and clear users cache */
        foreach($users_ids as $user_id) {
            (new \Altum\Models\Files())->calculate_and_update_file_usage($user_id);

            cache()->deleteItem('transfer_requests_total?user_id=' . $user_id);
        }
    }
}
