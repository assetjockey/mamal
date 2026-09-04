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

class GoalsAjax extends Controller {

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

        $_POST['type'] = in_array($_POST['type'], ['pageview', 'custom', 'scroll']) ? query_clean($_POST['type']) : 'pageview';
        $_POST['name'] = input_clean($_POST['name'], 32);

        switch($_POST['type']) {
            case 'pageview':
                /* Clean pageview goal */
                $_POST['path'] = '/' . trim(query_clean($_POST['path']));
                $_POST['key'] = string_generate(16);
                $_POST['scroll_percentage'] = null;

                break;

            case 'custom':
                /* Clean custom goal */
                $_POST['key'] = empty(trim(get_slug(query_clean($_POST['key'])))) ? string_generate(16) : trim(get_slug(query_clean($_POST['key'])));
                $_POST['path'] = null;
                $_POST['scroll_percentage'] = null;

                break;

            case 'scroll':
                /* Clean scroll goal */
                $_POST['path'] = '/' . trim(query_clean($_POST['path']));
                $_POST['key'] = string_generate(16);
                $_POST['scroll_percentage'] = isset($_POST['scroll_percentage']) ? (int) $_POST['scroll_percentage'] : 0;

                break;
        }


        /* Check for possible errors */
        if(empty($_POST['name'])) {
            Response::json(l('global.error_message.empty_fields'), 'error');
        }

        /* Validate scroll percentage */
        if($_POST['type'] == 'scroll' && ($_POST['scroll_percentage'] < 1 || $_POST['scroll_percentage'] > 100)) {
            Response::json(l('goal_create_modal.scroll_percentage_error'), 'error');
        }

        /* Get the count of already created goals */
        $total_websites_goals = database()->query("SELECT COUNT(*) AS `total` FROM `websites_goals` WHERE `website_id` = {$this->website->website_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->websites_goals_limit != -1 && $total_websites_goals >= $this->user->plan_settings->websites_goals_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Database query */
        db()->insert('websites_goals', [
            'user_id' => $this->user->user_id,
            'website_id' => $this->website->website_id,
            'key' => $_POST['key'],
            'type' => $_POST['type'],
            'path' => $_POST['path'],
            'scroll_percentage' => $_POST['scroll_percentage'],
            'name' => $_POST['name'],
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('website_goals?website_id=' . $this->website->website_id);

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

        $_POST['goal_id'] = (int) $_POST['goal_id'];
        $_POST['name'] = input_clean($_POST['name'], 32);
        $_POST['type'] = in_array($_POST['type'], ['pageview', 'custom', 'scroll']) ? query_clean($_POST['type']) : 'pageview';

        switch($_POST['type']) {
            case 'pageview':
                /* Clean pageview goal */
                $_POST['path'] = '/' . trim(query_clean($_POST['path']));
                $_POST['key'] = string_generate(16);
                $_POST['scroll_percentage'] = null;

                break;

            case 'custom':
                /* Clean custom goal */
                $_POST['key'] = empty(trim(get_slug(query_clean($_POST['key'])))) ? string_generate(16) : trim(get_slug(query_clean($_POST['key'])));
                $_POST['path'] = null;
                $_POST['scroll_percentage'] = null;

                break;

            case 'scroll':
                /* Clean scroll goal */
                $_POST['path'] = '/' . trim(query_clean($_POST['path']));
                $_POST['key'] = string_generate(16);
                $_POST['scroll_percentage'] = isset($_POST['scroll_percentage']) ? (int) $_POST['scroll_percentage'] : 0;

                break;
        }


        /* Check for possible errors */
        if(empty($_POST['name'])) {
            Response::json(l('global.error_message.empty_fields'), 'error');
        }

        /* Validate scroll percentage */
        if($_POST['type'] == 'scroll' && ($_POST['scroll_percentage'] < 1 || $_POST['scroll_percentage'] > 100)) {
            Response::json(l('goal_create_modal.scroll_percentage_error'), 'error');
        }

        /* Database query */
        db()->where('goal_id', $_POST['goal_id'])->where('website_id', $this->website->website_id)->update('websites_goals', [
            'key' => $_POST['key'],
            'type' => $_POST['type'],
            'path' => $_POST['path'],
            'scroll_percentage' => $_POST['scroll_percentage'],
            'name' => $_POST['name'],
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('website_goals?website_id=' . $this->website->website_id);

        /* Set a nice success message */
        Response::json(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));
    }

    public function delete() {
        $this->verify();

        if($this->team) {
            die();
        }

        if(empty($_POST)) {
            die();
        }

        $_POST['goal_id'] = (int) $_POST['goal_id'];

        if(!$goal = db()->where('goal_id', $_POST['goal_id'])->where('website_id', $this->website->website_id)->getOne('websites_goals', ['goal_id', 'name'])) {
            die();
        }

        /* Database query */
        db()->where('goal_id', $_POST['goal_id'])->where('website_id', $this->website->website_id)->delete('websites_goals');

        /* Clear the cache */
        cache()->deleteItem('website_goals?website_id=' . $this->website->website_id);

        /* Set a nice success message */
        Response::json(sprintf(l('global.success_message.delete1'), '<strong>' . $goal->name . '</strong>'));
    }
}
