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

class ApiSessions extends Controller {
    use Apiable;

    public function index() {

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                if(isset($this->params[1])) {
                    $this->return_404();
                }

                /* Detect if we only need an object, or the whole list */
                if(isset($this->params[0])) {
                    $this->get();
                } else {
                    $this->get_all();
                }

                break;

            case 'DELETE':
                if(isset($this->params[1])) {
                    $this->return_404();
                }

                $this->delete();
                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Website */
        $website_id = isset($_GET['website_id']) ? (int) $_GET['website_id'] : null;

        /* Verify ownership */
        $website = \Altum\Cache::cache_function_result('website?website_id=' . $website_id, ['website_id=' . $website_id], function() use ($website_id) {
            return db()
                ->where('website_id', $website_id)
                ->where('user_id', $this->user->user_id)
                ->getOne('websites', ['website_id', 'tracking_type']);
        });

        if(!$website || $website->tracking_type != 'advanced') {
            $this->return_404();
        }

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['website_id', 'visitor_id'], [], ['session_id', 'visitor_id', 'total_events', 'date'], allowed_datetime_fields: ['date']));
        $filters->set_default_order_by('session_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("
            SELECT
                COUNT(*) AS `total`
            FROM
                `visitors_sessions`
            WHERE
                `visitors_sessions`.`website_id` = {$website_id}
                {$filters->get_sql_where('visitors_sessions')}
        ")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/sessions?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                `visitors_sessions`.*
            FROM
                `visitors_sessions`
            WHERE
                `visitors_sessions`.`website_id` = {$website_id}
                {$filters->get_sql_where('visitors_sessions')}
                {$filters->get_sql_order_by('visitors_sessions')}

            {$paginator->get_sql_limit()}
        ");

        while($row = $data_result->fetch_object()) {
            $data[] = $this->process_session($row);
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

        $session_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $session = database()->query("
            SELECT
                `visitors_sessions`.*
            FROM
                `visitors_sessions`
            WHERE
                `visitors_sessions`.`session_id` = {$session_id}
        ")->fetch_object() ?? null;

        /* We haven't found the resource */
        if(!$session) {
            $this->return_404();
        }

        /* Access check */
        if(!db()->where('user_id', $this->user->user_id)->where('website_id', $session->website_id)->where('tracking_type', 'advanced')->has('websites')) {
            $this->return_404();
        }

        Response::jsonapi_success($this->process_session($session));

    }

    private function delete() {

        $session_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $session = db()->where('session_id', $session_id)->getOne('visitors_sessions');

        /* We haven't found the resource */
        if(!$session) {
            $this->return_404();
        }

        /* Access check */
        if(!db()->where('user_id', $this->user->user_id)->where('website_id', $session->website_id)->where('tracking_type', 'advanced')->has('websites')) {
            $this->return_404();
        }

        /* Database query */
        db()->where('session_id', $session->session_id)->delete('visitors_sessions');

        /* Clear the cache */
        cache()->deleteItem('session?session_uuid=' . md5($session->session_uuid_binary));

        http_response_code(200);
        die();

    }

    private function process_session($session) {

        return [
            'id' => (int) $session->session_id,
            'session_uuid' => bin2hex($session->session_uuid_binary),
            'visitor_id' => (int) $session->visitor_id,
            'website_id' => (int) $session->website_id,
            'total_events' => (int) $session->total_events,
            'datetime' => $session->date,
        ];

    }

}
