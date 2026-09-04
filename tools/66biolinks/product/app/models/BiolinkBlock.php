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

class BiolinkBlock extends Model {

    public function delete($biolink_block_id) {

        $biolink_block_id = (int) $biolink_block_id;

        /* Get the biolink block */
        $biolink_block = db()->where('biolink_block_id', $biolink_block_id)->getOne('biolinks_blocks', ['biolink_block_id', 'link_id', 'type', 'settings']);

        if(!$biolink_block) {
            return;
        }

        /* Delete the biolink block associated resources */
        $this->delete_biolink_block_associated_resources($biolink_block);

        /* Delete from database */
        db()->where('biolink_block_id', $biolink_block_id)->delete('biolinks_blocks');

        /* Clear the cache */
        cache()->deleteItem('biolink_blocks?link_id=' . $biolink_block->link_id);
        cache()->deleteItem('biolink_block?block_id=' . $biolink_block->biolink_block_id . '&type=youtube_feed');

    }

    public function bulk_delete($biolink_blocks_ids, $link_id = null) {
        $biolink_blocks_ids = array_filter(array_unique(array_map('intval', $biolink_blocks_ids)));
        $link_id = $link_id ? (int) $link_id : null;

        if(!$biolink_blocks_ids) {
            return;
        }

        /* Get all biolink blocks */
        $database = db()->where('biolink_block_id', $biolink_blocks_ids, 'IN');

        if($link_id) {
            $database->where('link_id', $link_id);
        }

        $biolink_blocks = $database->get('biolinks_blocks', null, ['biolink_block_id', 'link_id', 'type', 'settings']);

        if(!$biolink_blocks) {
            return;
        }

        $valid_biolink_blocks_ids = [];
        $links_ids = [];

        foreach($biolink_blocks as $biolink_block) {

            /* Delete the biolink block associated resources */
            $this->delete_biolink_block_associated_resources($biolink_block);

            $valid_biolink_blocks_ids[] = (int) $biolink_block->biolink_block_id;
            $links_ids[] = (int) $biolink_block->link_id;
        }

        $links_ids = array_filter(array_unique($links_ids));

        /* Delete from database */
        if($valid_biolink_blocks_ids) {
            db()->where('biolink_block_id', $valid_biolink_blocks_ids, 'IN')->delete('biolinks_blocks');
        }

        /* Clear the links cache */
        foreach($links_ids as $link_id) {
            cache()->deleteItem('biolink_blocks?link_id=' . $link_id);
        }

        /* Clear the biolink blocks cache */
        foreach($valid_biolink_blocks_ids as $biolink_block_id) {
            cache()->deleteItem('biolink_block?block_id=' . $biolink_block_id . '&type=youtube_feed');
        }

    }

    private function delete_biolink_block_associated_resources($biolink_block) {

        $blocks_with_storage = [
			'image' => [['path' => 'block_images', 'uploaded_file_key' => 'image']],
			'featured_link' => [['path' => 'block_images', 'uploaded_file_key' => 'image']],
            'image_grid' => [['path' => 'block_images', 'uploaded_file_key' => 'image']],
            'image_comparison' => [
                ['path' => 'block_images', 'uploaded_file_key' => 'before_image'],
                ['path' => 'block_images', 'uploaded_file_key' => 'after_image'],
            ],
            'link' => [['path' => 'block_thumbnail_images', 'uploaded_file_key' => 'image']],
            'big_link' => [['path' => 'block_thumbnail_images', 'uploaded_file_key' => 'image']],
            'email_collector' => [['path' => 'block_thumbnail_images', 'uploaded_file_key' => 'image']],
            'vcard' => [
                ['path' => 'block_thumbnail_images', 'uploaded_file_key' => 'image'],
                ['path' => 'avatars', 'uploaded_file_key' => 'vcard_avatar'],
            ],
            'audio' => [['path' => 'files', 'uploaded_file_key' => 'file']],
            'video' => [['path' => 'files', 'uploaded_file_key' => 'file']],
            'file' => [['path' => 'files', 'uploaded_file_key' => 'file']],
            'pdf_document' => [['path' => 'files', 'uploaded_file_key' => 'file']],
            'powerpoint_presentation' => [['path' => 'files', 'uploaded_file_key' => 'file']],
            'excel_spreadsheet' => [['path' => 'files', 'uploaded_file_key' => 'file']],
            'avatar' => [['path' => 'avatars', 'uploaded_file_key' => 'image']],
            'review' => [['path' => 'block_images', 'uploaded_file_key' => 'image']],
            'header' => [
                ['path' => 'avatars', 'uploaded_file_key' => 'avatar'],
                ['path' => 'backgrounds', 'uploaded_file_key' => 'background'],
                ['path' => 'files', 'uploaded_file_key' => 'video_file'],
            ],
        ];

        if(array_key_exists($biolink_block->type, $blocks_with_storage)) {
            $biolink_block->settings = json_decode($biolink_block->settings ?? '');

            foreach($blocks_with_storage[$biolink_block->type] as $block_with_storage) {
                if(!empty($biolink_block->settings->{$block_with_storage['uploaded_file_key']})) {
                    $this->delete_biolink_block_uploaded_file(
                        $biolink_block->settings->{$block_with_storage['uploaded_file_key']},
                        $block_with_storage['path']
                    );
                }
            }
        }

        /* Image slider special */
        if($biolink_block->type == 'image_slider') {
            $biolink_block->settings = json_decode($biolink_block->settings ?? '');

            foreach($biolink_block->settings->items ?? [] as $item) {
                \Altum\Uploads::delete_uploaded_file($item->image ?? null, 'block_images');
            }
        }

    }

    private function delete_biolink_block_uploaded_file($file, $path) {
        if(!$file) {
            return;
        }

        /* Offload deleting */
        if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url) {
            $s3 = new \Aws\S3\S3Client(get_aws_s3_config());

            if($s3->doesObjectExist(settings()->offload->storage_name, 'uploads/' . $path . '/' . $file)) {
                $s3->deleteObject([
                    'Bucket' => settings()->offload->storage_name,
                    'Key' => 'uploads/' . $path . '/' . $file,
                ]);
            }
        }

        /* Local deleting */
        else {
            if(file_exists(UPLOADS_PATH . $path . '/' . $file)) {
                unlink(UPLOADS_PATH . $path . '/' . $file);
            }
        }

    }
}
