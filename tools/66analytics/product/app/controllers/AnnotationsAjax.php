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

class AnnotationsAjax extends Controller {

    public function index() {
        die();
    }

    private function verify() {
        \Altum\Authentication::guard();

        if(!\Altum\Csrf::check() && !\Altum\Csrf::check('global_token')) {
            die();
        }

        if(!$this->website || !settings()->analytics->annotations_is_enabled) {
            die();
        }

        if($this->team) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Response::json('Please create an account on the demo to test out this function.', 'error');
    }

    private function process_chart_datetime() {
        if(!isset($_POST['chart_datetime']) || !\Altum\Date::validate($_POST['chart_datetime'], 'Y-m-d H:i:s')) {
            Response::json(l('global.error_message.empty_fields'), 'error');
        }

        /* Convert date */
        return (new \DateTime($_POST['chart_datetime'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
    }

    public function create() {
        $this->verify();

        if(empty($_POST)) {
            die();
        }

        $_POST['name'] = input_clean($_POST['name'], 64);
        $_POST['chart_datetime'] = $this->process_chart_datetime();

        /* Check for possible errors */
        if(empty($_POST['name'])) {
            Response::json(l('global.error_message.empty_fields'), 'error');
        }

        /* Check the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `annotations` WHERE `website_id` = {$this->website->website_id} AND `user_id` = {$this->user->user_id}")->fetch_object()->total;
        if($this->user->plan_settings->annotations_limit != -1 && $total_rows >= $this->user->plan_settings->annotations_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Database query */
        $annotation_id = db()->insert('annotations', [
            'user_id' => $this->user->user_id,
            'website_id' => $this->website->website_id,
            'name' => $_POST['name'],
            'chart_datetime' => $_POST['chart_datetime'],
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('annotations?website_id=' . $this->website->website_id);

        /* Set a nice success message */
        Response::json(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'), 'success', [
            'annotation_id' => $annotation_id
        ]);
    }

    public function update() {
        $this->verify();

        if(empty($_POST)) {
            die();
        }

        $_POST['annotation_id'] = (int) $_POST['annotation_id'];
        $_POST['name'] = input_clean($_POST['name'], 64);
        $_POST['chart_datetime'] = $this->process_chart_datetime();

        if(!$annotation = db()->where('annotation_id', $_POST['annotation_id'])->where('website_id', $this->website->website_id)->where('user_id', $this->user->user_id)->getOne('annotations', ['annotation_id', 'website_id'])) {
            die();
        }

        /* Check for possible errors */
        if(empty($_POST['name'])) {
            Response::json(l('global.error_message.empty_fields'), 'error');
        }

        /* Check the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `annotations` WHERE `website_id` = {$annotation->website_id} AND `user_id` = {$this->user->user_id}")->fetch_object()->total;
        if($this->user->plan_settings->annotations_limit != -1 && $total_rows > $this->user->plan_settings->annotations_limit) {
            Response::json(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->annotations_limit, mb_strtolower(l('annotations.title')), l('global.info_message.plan_upgrade')), 'error');
        }

        /* Database query */
        db()->where('annotation_id', $annotation->annotation_id)->where('website_id', $annotation->website_id)->where('user_id', $this->user->user_id)->update('annotations', [
            'name' => $_POST['name'],
            'chart_datetime' => $_POST['chart_datetime'],
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('annotations?website_id=' . $annotation->website_id);

        /* Set a nice success message */
        Response::json(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));
    }

    public function delete() {
        $this->verify();

        if(empty($_POST)) {
            die();
        }

        $_POST['annotation_id'] = (int) $_POST['annotation_id'];

        if(!$annotation = db()->where('annotation_id', $_POST['annotation_id'])->where('website_id', $this->website->website_id)->where('user_id', $this->user->user_id)->getOne('annotations', ['annotation_id', 'website_id', 'name'])) {
            die();
        }

        /* Database query */
        db()->where('annotation_id', $annotation->annotation_id)->where('website_id', $annotation->website_id)->where('user_id', $this->user->user_id)->delete('annotations');

        /* Clear the cache */
        cache()->deleteItemsByTag('annotations?website_id=' . $annotation->website_id);

        /* Set a nice success message */
        Response::json(sprintf(l('global.success_message.delete1'), '<strong>' . $annotation->name . '</strong>'));
    }

}
