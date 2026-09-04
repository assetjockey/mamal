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

use Altum\Cache;

defined('ALTUMCODE') || die();

class SessionsReplays extends Model {

	public function delete($replay_id) {
		Cache::store_initialize();

		/* Get replay */
		$replay = db()->where('replay_id', $replay_id)->getOne('sessions_replays');

		if(!$replay) {
			return;
		}

		$session_id = $replay->session_id;

		/* Load chunk index */
		$index_item = cache('store_adapter')->getItem('session_replay_keys_' . $session_id);
		$chunk_keys = $index_item->get() ?: [];

		/* Delete chunk index */
		cache('store_adapter')->deleteItem('session_replay_keys_' . $session_id);

		/* Delete each chunk */
		foreach($chunk_keys as $chunk_key) {
			cache('store_adapter')->deleteItem($chunk_key);
		}

		/* Delete offloaded file if needed */
		if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url && $replay->is_offloaded) {

			$file_name = base64_encode($replay->session_id . $replay->datetime) . '.txt';

			try {
				$s3 = new \Aws\S3\S3Client(get_aws_s3_config());

				$s3->deleteObject([
					'Bucket' => settings()->offload->storage_name,
					'Key' => UPLOADS_URL_PATH . 'store/' . $file_name,
				]);

			} catch (\Exception $exception) {
				dil($exception->getMessage());
			}
		}

		/* Delete database entry */
		db()->where('replay_id', $replay_id)->delete('sessions_replays');
	}
}
