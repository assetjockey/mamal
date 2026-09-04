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

class Link extends Model {

    public function get_full_links_by_user_id($user_id) {

        /* Get the user links */
        $links = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem('links?user_id=' . $user_id);

        /* Set cache if not existing */
        if(!$cache_instance->isHit()) {

            /* Get data from the database */
            $links_result = database()->query("SELECT `links`.*, `domains`.`scheme`, `domains`.`host`, `domains`.`link_id` as `domain_link_id` FROM `links` LEFT JOIN `domains` ON `links`.`domain_id` = `domains`.`domain_id` WHERE `links`.`user_id` = {$user_id}");
            while($row = $links_result->fetch_object()) {
                $row->full_url = $row->domain_id ? $row->scheme . $row->host . '/' . ($row->domain_link_id == $row->link_id ? null : $row->url) : SITE_URL . $row->url;
                $links[$row->link_id] = $row;
            }

            cache()->save(
                $cache_instance->set($links)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $user_id)
            );

        } else {

            /* Get cache */
            $links = $cache_instance->get();

        }

        return $links;

    }

    /**
     * Delete one link
     *
     * @param int $link_id Link id to delete
     * @param int|null $user_id Optional user id to scope deletion
     * @return void
     */
    public function delete($link_id, $user_id = null) {

        $link_id = (int) $link_id;
        $user_id = $user_id ? (int) $user_id : null;

        /* Get the link */
        $database = db()->where('link_id', $link_id);

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $link = $database->getOne('links', ['user_id', 'link_id', 'biolink_theme_id', 'type', 'settings', 'additional']);

        if(!$link) {
            return;
        }

        /* Delete the link associated resources */
        $this->delete_link_associated_resources($link);

        /* Delete from database */
        db()->where('link_id', $link_id)->delete('links');

        /* Clear the cache */
        cache()->deleteItem($link->type . '_links_total?user_id=' . $link->user_id);
        cache()->deleteItem('links_total?user_id=' . $link->user_id);
        cache()->deleteItem('links?user_id=' . $link->user_id);
        cache()->deleteItem('biolink_blocks?link_id=' . $link->link_id);
        cache()->deleteItemsByTag('link_id=' . $link->link_id);

    }

    /**
     * Delete multiple links
     *
     * @param array $links_ids Link ids to delete
     * @param int|null $user_id Optional user id to avoid extra user lookup and scope deletion as an extra safety check
     * @return void
     */
    public function bulk_delete($links_ids, $user_id = null) {

        $links_ids = array_filter(array_unique(array_map('intval', $links_ids)));
        $user_id = $user_id ? (int) $user_id : null;

        if(!$links_ids) {
            return;
        }

        /* Get all links */
        $database = db()->where('link_id', $links_ids, 'IN');

        if($user_id) {
            $database->where('user_id', $user_id);
        }

        $links = $database->get('links', null, ['user_id', 'link_id', 'biolink_theme_id', 'type', 'settings', 'additional']);

        if(!$links) {
            return;
        }

        $valid_links_ids = [];
        $users_ids = [];
        $link_types = [];
        $biolinks_ids = [];

        foreach($links as $link) {

            /* Delete the link associated resources */
            $this->delete_link_associated_resources($link, false);

            $valid_links_ids[] = (int) $link->link_id;
            $users_ids[] = (int) $link->user_id;
            $link_types[$link->user_id][] = $link->type;

            if($link->type == 'biolink') {
                $biolinks_ids[] = (int) $link->link_id;
            }
        }

        $users_ids = array_filter(array_unique($users_ids));
        $biolinks_ids = array_filter(array_unique($biolinks_ids));

        /* Delete all biolink blocks related to the biolinks */
        if($biolinks_ids) {
            $biolink_blocks_ids = db()->where('link_id', $biolinks_ids, 'IN')->getValue('biolinks_blocks', 'biolink_block_id', null);

            if($biolink_blocks_ids) {
                (new \Altum\Models\BiolinkBlock())->bulk_delete($biolink_blocks_ids);
            }
        }

        /* Delete from database */
        if($valid_links_ids) {
            db()->where('link_id', $valid_links_ids, 'IN')->delete('links');
        }

        /* Clear the links cache */
        foreach($valid_links_ids as $link_id) {
            cache()->deleteItem('biolink_blocks?link_id=' . $link_id);
            cache()->deleteItemsByTag('link_id=' . $link_id);
        }

        /* Clear the users cache */
        foreach($users_ids as $user_id) {
            cache()->deleteItem('links_total?user_id=' . $user_id);
            cache()->deleteItem('links?user_id=' . $user_id);

            foreach(array_unique($link_types[$user_id]) as $link_type) {
                cache()->deleteItem($link_type . '_links_total?user_id=' . $user_id);
            }
        }

    }

    /**
     * Delete link associated resources
     *
     * @param object $link Link object
     * @param bool $delete_biolink_blocks Whether to delete biolink blocks
     * @return void
     */
    private function delete_link_associated_resources($link, $delete_biolink_blocks = true) {

        /* Process to delete the stored files of the vcard avatar */
        if($link->type == 'vcard') {
            $link->settings = json_decode($link->settings ?? '');

            \Altum\Uploads::delete_uploaded_file($link->settings->vcard_avatar ?? null, 'avatars');
        }

        /* Process to delete the stored files of the link */
        if($link->type == 'file') {
            $link->settings = json_decode($link->settings ?? '');

            \Altum\Uploads::delete_uploaded_file($link->settings->file ?? null, 'files');
        }

        /* Process to delete the stored files of the link */
        if($link->type == 'biolink') {
            $link->settings = json_decode($link->settings ?? '');

            if(!empty($link->settings->pwa_file_name)) {
                \Altum\Uploads::delete_uploaded_file($link->settings->pwa_file_name, 'pwa');
            }

            \Altum\Uploads::delete_uploaded_file($link->settings->favicon ?? null, 'favicons');
			\Altum\Uploads::delete_uploaded_file($link->settings->seo->image ?? null, 'block_images');
			\Altum\Uploads::delete_uploaded_file($link->settings->password_seo_image ?? null, 'block_images');
			\Altum\Uploads::delete_uploaded_file($link->settings->sensitive_content_seo_image ?? null, 'block_images');
            \Altum\Uploads::delete_uploaded_file($link->settings->pwa_icon ?? null, 'app_icon');
            \Altum\Uploads::delete_uploaded_file($link->settings->branded_button_icon ?? null, 'branded_button_icon');

            if(($link->settings->background_type ?? null) == 'image' && !$link->biolink_theme_id) {
                \Altum\Uploads::delete_uploaded_file($link->settings->background ?? null, 'backgrounds');
            }

            if($delete_biolink_blocks) {

                /* Get all the available biolink blocks */
                $biolink_blocks_ids = db()->where('link_id', $link->link_id)->getValue('biolinks_blocks', 'biolink_block_id', null);

                /* Delete all biolink blocks */
                if($biolink_blocks_ids) {
                    (new \Altum\Models\BiolinkBlock())->bulk_delete($biolink_blocks_ids, $link->link_id);
                }
            }
        }

        /* Process to delete the stored files of the link */
        if($link->type == 'static') {
            $link->additional = json_decode($link->additional);
            $link->settings = json_decode($link->settings);
            $static_folders = [];

            /* Get static folder */
            if(isset($link->settings->static_folder) && $link->settings->static_folder) {
                $static_folders[] = $link->settings->static_folder;
            }

            if(isset($link->additional->static_folder) && $link->additional->static_folder && !in_array($link->additional->static_folder, $static_folders)) {
                $static_folders[] = $link->additional->static_folder;
            }

            /* Get AI backup folder */
            if(isset($link->additional->static_ai_backup_folder) && $link->additional->static_ai_backup_folder && preg_match('/^[a-f0-9]{32}$/', $link->additional->static_ai_backup_folder) && !in_array($link->additional->static_ai_backup_folder, $static_folders)) {
                $static_folders[] = $link->additional->static_ai_backup_folder;
            }

            foreach($static_folders as $static_folder) {
                /* Clear the already existing folder and contents */
                remove_directory_and_contents(\Altum\Uploads::get_full_path('static') . $static_folder);
            }
        }

    }
}
