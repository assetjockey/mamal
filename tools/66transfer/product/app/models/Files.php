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

class Files extends Model {

    public function get_files_by_transfer_id($transfer_id) {

        /* Get the files */
        $files = [];

        /* Try to check if the vcard blocks exists via the cache */
        $cache_instance = cache()->getItem('files?transfer_id=' . $transfer_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $result = database()->query("SELECT * FROM `files` WHERE `transfer_id` = {$transfer_id}");
            while($row = $result->fetch_object()) {
                $files[$row->file_id] = $row;
            }

            /* Properly tag the cache */
            $cache_instance->set($files)->expiresAfter(CACHE_DEFAULT_SECONDS);

            foreach($files as $file) {
                $cache_instance->addTag('file_id=' . $file->file_id);
            }

            if(count($files)) {
                cache()->save($cache_instance);
            }

        } else {

            /* Get cache */
            $files = $cache_instance->get();

        }

        return $files;

    }

	public function get_files_by_transfer_request_id($transfer_request_id) {

		/* Get the files */
		$files = [];

		/* Try to check if the vcard blocks exists via the cache */
		$cache_instance = cache()->getItem('files?transfer_request_id=' . $transfer_request_id);

		/* Set cache if not existing */
		if(!$cache_instance->isHit()) {

			/* Get data from the database */
			$result = database()->query("SELECT * FROM `files` WHERE `transfer_request_id` = {$transfer_request_id}");
			while($row = $result->fetch_object()) {
				$files[$row->file_id] = $row;
			}

			/* Properly tag the cache */
			$cache_instance->set($files)->expiresAfter(CACHE_DEFAULT_SECONDS);

			foreach($files as $file) {
				$cache_instance->addTag('file_id=' . $file->file_id);
			}

			if(count($files)) {
				cache()->save($cache_instance);
			}

		} else {

			/* Get cache */
			$files = $cache_instance->get();

		}

		return $files;

	}


    public function calculate_and_update_file_usage($user_id) {

        if(!$user_id) {
            return;
        }

        /* Get all files of the user */
        $stats = database()->query("
            SELECT COUNT(`file_id`) as `total_files`, SUM(`size`) as `total_files_size`
            FROM `files`
            WHERE `user_id` = {$user_id}
        ")->fetch_object() ?? null;

        if(!$stats) {
            return;
        }

        /* Database query */
        db()->where('user_id', $user_id)->update('users', [
            'total_files' => $stats->total_files,
            'total_files_size' => $stats->total_files_size,
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('user_id=' . $user_id);
    }

}
