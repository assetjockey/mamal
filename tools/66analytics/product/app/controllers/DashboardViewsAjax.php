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

class DashboardViewsAjax extends Controller {

    public function index() {
        die();
    }

    private function verify() {
        \Altum\Authentication::guard();

        if(!\Altum\Csrf::check() && !\Altum\Csrf::check('global_token')) {
            die();
        }

        if(!$this->website || !settings()->analytics->dashboard_views_is_enabled) {
            die();
        }

        if($this->team) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Response::json('Please create an account on the demo to test out this function.', 'error');
    }

    private function process_filters($filters) {
        $filters = json_decode($filters);

        if(!$filters || !is_array($filters)) {
            Response::json(l('dashboard_views.error_message.filters'), 'error');
        }

        /* Get available filters */
        $available_filters = $this->website->tracking_type == 'lightweight' ? \Altum\AnalyticsFilters::$lightweight_events : array_merge(\Altum\AnalyticsFilters::$websites_visitors, \Altum\AnalyticsFilters::$sessions_events);
        $processed_filters = [];

        foreach($filters as $filter) {
            if(!isset($filter->by, $filter->rule, $filter->value)) {
                continue;
            }

            $filter->by = query_clean($filter->by);
            $filter->rule = query_clean($filter->rule);
            $filter->value = query_clean((string) $filter->value);

            if(!in_array($filter->by, $available_filters)) {
                continue;
            }

            if(!in_array($filter->rule, [
                'is',
                'is_not',
                'contains',
                'starts_with',
                'ends_with'
            ])) {
                continue;
            }

            if(trim($filter->value) === '') {
                continue;
            }

            $processed_filters[] = [
                'by' => $filter->by,
                'rule' => $filter->rule,
                'value' => $filter->value,
            ];
        }

        if(!count($processed_filters)) {
            Response::json(l('dashboard_views.error_message.filters'), 'error');
        }

        return json_encode($processed_filters);
    }

    public function create() {
        $this->verify();

        if(empty($_POST)) {
            die();
        }

        $_POST['name'] = input_clean($_POST['name'], 64);

        if(empty($_POST['name']) || !isset($_POST['filters'])) {
            Response::json(l('global.error_message.empty_fields'), 'error');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `dashboard_views` WHERE `website_id` = {$this->website->website_id} AND `user_id` = {$this->user->user_id}")->fetch_object()->total;
        if($this->user->plan_settings->dashboard_views_limit != -1 && $total_rows >= $this->user->plan_settings->dashboard_views_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        $_POST['filters'] = $this->process_filters($_POST['filters']);

        /* Database query */
        $dashboard_view_id = db()->insert('dashboard_views', [
            'user_id' => $this->user->user_id,
            'website_id' => $this->website->website_id,
            'name' => $_POST['name'],
            'filters' => $_POST['filters'],
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItemsByTag('dashboard_views?website_id=' . $this->website->website_id);

        /* Set a nice success message */
        Response::json(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'), 'success', [
            'dashboard_view_id' => $dashboard_view_id
        ]);
    }

    public function update() {
        $this->verify();

        if(empty($_POST)) {
            die();
        }

        $_POST['dashboard_view_id'] = (int) $_POST['dashboard_view_id'];

        if(!$dashboard_view = db()->where('dashboard_view_id', $_POST['dashboard_view_id'])->where('website_id', $this->website->website_id)->where('user_id', $this->user->user_id)->getOne('dashboard_views')) {
            die();
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `dashboard_views` WHERE `website_id` = {$this->website->website_id} AND `user_id` = {$this->user->user_id}")->fetch_object()->total;
        if($this->user->plan_settings->dashboard_views_limit != -1 && $total_rows > $this->user->plan_settings->dashboard_views_limit) {
            Response::json(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->dashboard_views_limit, mb_strtolower(l('dashboard_views.title')), l('global.info_message.plan_upgrade')), 'error');
        }

        $_POST['name'] = isset($_POST['name']) ? input_clean($_POST['name'], 64) : $dashboard_view->name;

        if(empty($_POST['name'])) {
            Response::json(l('global.error_message.empty_fields'), 'error');
        }

        $values = [
            'name' => $_POST['name'],
            'last_datetime' => get_date(),
        ];

        if(isset($_POST['filters'])) {
            $values['filters'] = $this->process_filters($_POST['filters']);
        }

        /* Database query */
        db()->where('dashboard_view_id', $dashboard_view->dashboard_view_id)->where('user_id', $this->user->user_id)->update('dashboard_views', $values);

        /* Clear the cache */
        cache()->deleteItemsByTag('dashboard_views?website_id=' . $this->website->website_id);

        /* Set a nice success message */
        Response::json(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));
    }

    public function delete() {
        $this->verify();

        if(empty($_POST)) {
            die();
        }

        $_POST['dashboard_view_id'] = (int) $_POST['dashboard_view_id'];

        if(!$dashboard_view = db()->where('dashboard_view_id', $_POST['dashboard_view_id'])->where('website_id', $this->website->website_id)->where('user_id', $this->user->user_id)->getOne('dashboard_views', ['dashboard_view_id', 'name'])) {
            die();
        }

        /* Database query */
        db()->where('dashboard_view_id', $dashboard_view->dashboard_view_id)->where('user_id', $this->user->user_id)->delete('dashboard_views');

        /* Clear the cache */
        cache()->deleteItemsByTag('dashboard_views?website_id=' . $this->website->website_id);

        /* Set a nice success message */
        Response::json(sprintf(l('global.success_message.delete1'), '<strong>' . $dashboard_view->name . '</strong>'));
    }

}
