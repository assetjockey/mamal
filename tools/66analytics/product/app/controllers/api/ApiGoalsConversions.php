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
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiGoalsConversions extends Controller {
    use Apiable;

    public function index() {

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                /* Detect if we only need an object, or the whole list */
                if(isset($this->params[0])) {
                    $this->get();
                } else {
                    $this->get_all();
                }

                break;

            case 'POST':

                /* Detect what method to use */
                if(isset($this->params[0])) {
                    $this->patch();
                } else {
                    $this->post();
                }

                break;

            case 'DELETE':
                $this->delete();
                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['website_id', 'user_id', 'goal_id'], [], ['conversion_id', 'datetime'], allowed_datetime_fields: ['datetime']));
        $filters->set_default_order_by('conversion_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `goals_conversions` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/goals-conversions?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `goals_conversions`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->conversion_id,
                'user_id' => (int) $row->user_id,
                'event_id' => (int) $row->event_id,
                'session_id' => (int) $row->session_id,
                'visitor_id' => (int) $row->visitor_id,
                'goal_id' => (int) $row->goal_id,
                'website_id' => (int) $row->website_id,
                'expiration_date' => $row->expiration_date,
                'datetime' => $row->datetime,
            ];

            $data[] = $row;
        }

        /* Prepare the data */
        $meta = [
            'page' => $_GET['page'] ?? 1,
            'total_pages' => $paginator->getNumPages(),
            'results_per_page' => $filters->get_results_per_page(),
            'total_results' => (int) $total_rows,
        ];

        /* Prepare the pagination links */
        $others = ['links' => [
            'first' => $paginator->getPageUrl(1),
            'last' => $paginator->getNumPages() ? $paginator->getPageUrl($paginator->getNumPages()) : null,
            'next' => $paginator->getNextUrl(),
            'prev' => $paginator->getPrevUrl(),
            'self' => $paginator->getPageUrl($_GET['page'] ?? 1)
        ]];

        Response::jsonapi_success($data, $meta, 200, $others);
    }

    private function get() {

        $conversion_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $goal = db()->where('conversion_id', $conversion_id)->where('user_id', $this->user->user_id)->getOne('goals_conversions');

        /* We haven't found the resource */
        if(!$goal) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $goal->conversion_id,
            'user_id' => (int) $goal->user_id,
            'event_id' => (int) $goal->event_id,
            'session_id' => (int) $goal->session_id,
            'visitor_id' => (int) $goal->visitor_id,
            'goal_id' => (int) $goal->goal_id,
            'website_id' => (int) $goal->website_id,
            'expiration_date' => $goal->expiration_date,
            'datetime' => $goal->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for any errors */
        $required_fields = ['goal_id'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['goal_id'] = (int) $_POST['goal_id'];
        $_POST['visitor_id'] = isset($_POST['visitor_id']) ? (int) $_POST['visitor_id'] : null;
        $_POST['session_id'] = isset($_POST['session_id']) ? (int) $_POST['session_id'] : null;
        $_POST['event_id'] = isset($_POST['event_id']) ? (int) $_POST['event_id'] : null;

        if(!$goal = db()->where('goal_id', $_POST['goal_id'])->where('user_id', $this->user->user_id)->getOne('websites_goals')) {
            $this->return_404();
        }

        if(isset($_POST['visitor_id'])) {
            if(!db()->where('visitor_id', $_POST['visitor_id'])->where('website_id', $goal->website_id)->has('websites_visitors')) {
                $this->return_404();
            }
        }

        if(isset($_POST['session_id'])) {
            if(!db()->where('session_id', $_POST['session_id'])->where('website_id', $goal->website_id)->has('visitors_sessions')) {
                $this->return_404();
            }
        }

        if(isset($_POST['event_id'])) {
            if(!db()->where('event_id', $_POST['event_id'])->where('website_id', $goal->website_id)->has('sessions_events')) {
                $this->return_404();
            }
        }

        $expiration_date = (new \DateTime())->modify('+' . $this->user->plan_settings->sessions_events_retention . ' days')->format('Y-m-d');

        /* Database query */
        $conversion_id = db()->insert('goals_conversions', [
			'user_id' => $this->user->user_id,
            'website_id' => $goal->website_id,
            'goal_id' => $goal->goal_id,
            'event_id' => $_POST['event_id'],
            'session_id' => $_POST['session_id'],
            'visitor_id' => $_POST['visitor_id'],
            'expiration_date' => $expiration_date,
            'datetime' => get_date(),
        ]);

        /* Update the website usage */
        db()->where('website_id', $goal->website_id)->update('websites', ['current_month_sessions_events' => db()->inc()]);

        /* Prepare the data */
        $data = [
            'id' => (int) $conversion_id,
            'user_id' => (int) $this->user->user_id,
            'event_id' => $_POST['event_id'],
            'session_id' => $_POST['session_id'],
            'visitor_id' => $_POST['visitor_id'],
            'goal_id' => (int) $goal->goal_id,
            'website_id' => (int) $goal->website_id,
            'expiration_date' => $expiration_date,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $conversion_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $goal_conversion = db()->where('conversion_id', $conversion_id)->where('user_id', $this->user->user_id)->getOne('goals_conversions');

        /* We haven't found the resource */
        if(!$goal_conversion) {
            $this->return_404();
        }

        $_POST['goal_id'] = isset($_POST['goal_id']) ? (int) $_POST['goal_id'] : $goal_conversion->goal_id;
        $_POST['visitor_id'] = isset($_POST['visitor_id']) ? (int) $_POST['visitor_id'] : $goal_conversion->visitor_id;
        $_POST['session_id'] = isset($_POST['session_id']) ? (int) $_POST['session_id'] : $goal_conversion->session_id;
        $_POST['event_id'] = isset($_POST['event_id']) ? (int) $_POST['event_id'] : $goal_conversion->event_id;

        if(!$goal = db()->where('goal_id', $_POST['goal_id'])->where('user_id', $this->user->user_id)->getOne('websites_goals')) {
            $this->return_404();
        }

        if(isset($_POST['visitor_id'])) {
            if(!db()->where('visitor_id', $_POST['visitor_id'])->where('website_id', $goal->website_id)->has('websites_visitors')) {
                $this->return_404();
            }
        }

        if(isset($_POST['session_id'])) {
            if(!db()->where('session_id', $_POST['session_id'])->where('website_id', $goal->website_id)->has('visitors_sessions')) {
                $this->return_404();
            }
        }

        if(isset($_POST['event_id'])) {
            if(!db()->where('event_id', $_POST['event_id'])->where('website_id', $goal->website_id)->has('sessions_events')) {
                $this->return_404();
            }
        }

        /* Database query */
        db()->where('conversion_id', $conversion_id)->where('user_id', $this->user->user_id)->update('goals_conversions', [
            'event_id' => $_POST['event_id'],
            'session_id' => $_POST['session_id'],
            'visitor_id' => $_POST['visitor_id'],
            'goal_id' => (int) $goal->goal_id,
            'website_id' => (int) $goal->website_id,
        ]);

        /* Prepare the data */
        $data = [
            'id' => (int) $goal_conversion->conversion_id,
            'user_id' => (int) $this->user->user_id,
            'event_id' => $_POST['event_id'],
            'session_id' => $_POST['session_id'],
            'visitor_id' => $_POST['visitor_id'],
            'goal_id' => (int) $goal->goal_id,
            'website_id' => (int) $goal->website_id,
            'expiration_date' => $goal_conversion->expiration_date,
            'datetime' => $goal_conversion->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $conversion_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $goal = db()->where('conversion_id', $conversion_id)->where('user_id', $this->user->user_id)->getOne('goals_conversions');

        /* We haven't found the resource */
        if(!$goal) {
            $this->return_404();
        }

        /* Database query */
        db()->where('conversion_id', $conversion_id)->delete('goals_conversions');

        /* Clear the cache */
        cache()->deleteItemsByTag('goals?website_id=' . $goal->website_id);

        http_response_code(200);
        die();

    }

}
