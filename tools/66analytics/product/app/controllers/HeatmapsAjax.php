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
namespace Altum\Controllers;

use Altum\Response;

defined('ALTUMCODE') || die();

class HeatmapsAjax extends Controller {

    public function index() {
        die();
    }

    private function verify() {
        \Altum\Authentication::guard();

        if(!\Altum\Csrf::check() && !\Altum\Csrf::check('global_token')) {
            die();
        }

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Response::json('Please create an account on the demo to test out this function.', 'error');
    }

    public function create() {
        $this->verify();

        if($this->team) {
            die();
        }

        if(empty($_POST)) {
            die();
        }

        $_POST['is_bulk'] = (int) isset($_POST['is_bulk']);
        $is_enabled = 1;
        $datetime = get_date();

        /* Bulk generator */
        if($_POST['is_bulk']) {
            $_POST['paths'] = isset($_POST['paths']) ? $_POST['paths'] : '';

            /* Check for possible errors */
            if(empty(trim($_POST['paths']))) {
                Response::json(l('global.error_message.empty_fields'), 'error');
            }

            /* Existing heatmap paths */
            $existing_paths = [];
            $existing_paths_result = database()->query("SELECT `path` FROM `websites_heatmaps` WHERE `website_id` = {$this->website->website_id}");
            while($row = $existing_paths_result->fetch_object()) {
                $existing_paths[$row->path] = true;
            }

            /* Check for the plan limit */
            $total_websites_heatmaps = count($existing_paths);
            if($this->user->plan_settings->websites_heatmaps_limit != -1 && $total_websites_heatmaps >= $this->user->plan_settings->websites_heatmaps_limit) {
                Response::json(l('global.info_message.plan_feature_limit'), 'error');
            }

            /* Parse bulk paths */
            $paths = explode("\n", str_replace(["\r\n", "\r"], "\n", $_POST['paths']));
            $parsed_paths = [];
            $heatmaps_batch = [];

            foreach($paths as $path) {
                $path = input_clean($path, 1024);

                if(empty($path)) {
                    continue;
                }

                $path = '/' . ltrim(trim($path), '/');

                if(isset($parsed_paths[$path]) || isset($existing_paths[$path])) {
                    continue;
                }

                if($this->user->plan_settings->websites_heatmaps_limit != -1 && $total_websites_heatmaps + count($heatmaps_batch) >= $this->user->plan_settings->websites_heatmaps_limit) {
                    break;
                }

                /* Prepare heatmap */
                $heatmaps_batch[] = [
                    'user_id' => $this->user->user_id,
                    'website_id' => $this->website->website_id,
                    'name' => input_clean($this->website->scheme . $this->website->host . $this->website->path . $path, 256),
                    'path' => $path,
                    'is_enabled' => $is_enabled,
                    'datetime' => $datetime,
                ];

                $parsed_paths[$path] = true;
            }

            if(empty($heatmaps_batch)) {
                Response::json(l('global.error_message.basic'), 'error');
            }

            /* Database query */
            db()->insertBulkInChunks('websites_heatmaps', $heatmaps_batch);

            /* Clear the cache */
            cache()->deleteItem('website_heatmaps?website_id=' . $this->website->website_id);

            /* Set a nice success message */
            Response::json(l('global.success_message.create2'));
        }

        $_POST['name'] = isset($_POST['name']) ? input_clean($_POST['name'], 256) : '';
        $_POST['path'] = !empty($_POST['path']) ? '/' . ltrim(trim(input_clean($_POST['path'])), '/') : '/';

        /* Check for possible errors */
        if(empty($_POST['name'])) {
            Response::json(l('global.error_message.empty_fields'), 'error');
        }

        /* Get the count of already created heatmaps */
        $total_websites_heatmaps_result = database()->query("SELECT COUNT(*) AS `total` FROM `websites_heatmaps` WHERE `website_id` = {$this->website->website_id}")->fetch_object();
        $total_websites_heatmaps = $total_websites_heatmaps_result ? $total_websites_heatmaps_result->total : 0;
        if($this->user->plan_settings->websites_heatmaps_limit != -1 && $total_websites_heatmaps >= $this->user->plan_settings->websites_heatmaps_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Database query */
        db()->insert('websites_heatmaps', [
            'user_id' => $this->user->user_id,
            'website_id' => $this->website->website_id,
            'name' => $_POST['name'],
            'path' => $_POST['path'],
            'is_enabled' => $is_enabled,
            'datetime' => $datetime,
        ]);

        /* Clear the cache */
        cache()->deleteItem('website_heatmaps?website_id=' . $this->website->website_id);

        /* Set a nice success message */
        Response::json(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));
    }

    public function update() {
        $this->verify();

        if($this->team) {
            die();
        }

        if(empty($_POST)) {
            die();
        }

        $_POST['name'] = input_clean($_POST['name'], 256);
        $_POST['is_enabled'] = (int) isset($_POST['is_enabled']);
        $_POST['heatmap_id'] = (int) $_POST['heatmap_id'];

        /* Check for possible errors */
        if(empty($_POST['name'])) {
            Response::json(l('global.error_message.empty_fields'), 'error');
        }

        /* Database query */
        db()->where('website_id', $this->website->website_id)->where('heatmap_id', $_POST['heatmap_id'])->update('websites_heatmaps', [
            'name' => $_POST['name'],
            'is_enabled' => $_POST['is_enabled'],
            'heatmap_id' => $_POST['heatmap_id'],
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('website_heatmaps?website_id=' . $this->website->website_id);

        /* Set a nice success message */
        Response::json(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));
    }

    public function retake_snapshots() {
        $this->verify();

        if($this->team) {
            die();
        }

        if(empty($_POST)) {
            die();
        }

        $_POST['snapshot_id_desktop'] = (int) isset($_POST['snapshot_id_desktop']);
        $_POST['snapshot_id_tablet'] = (int) isset($_POST['snapshot_id_tablet']);
        $_POST['snapshot_id_mobile'] = (int) isset($_POST['snapshot_id_mobile']);
        $_POST['heatmap_id'] = (int) $_POST['heatmap_id'];

        foreach(['desktop', 'tablet', 'mobile'] as $key) {
            if($_POST['snapshot_id_' . $key]) {
                db()->where('website_id', $this->website->website_id)->where('heatmap_id', $_POST['heatmap_id'])->where('type', $key)->delete('heatmaps_snapshots');

                /* Clear the cache */
                $snapshot_id_type = 'snapshot_id_' . $key;
                $cache_key = 'heatmap?hash=' . md5("SELECT `website_id`, `heatmap_id`, `path`, `{$snapshot_id_type}`, `is_enabled` FROM `websites_heatmaps` WHERE `heatmap_id` = {$_POST['heatmap_id']}");
                cache()->deleteItem($cache_key);
            }
        }

        /* Clear the cache */
        cache()->deleteItem('website_heatmaps?website_id=' . $this->website->website_id);

        Response::json(l('heatmap_retake_snapshots_modal.success_message'), 'success');
    }

}
