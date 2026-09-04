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

class SessionAjax extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $session_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Get the Visitor basic data and make sure it exists */
        if(!$session = db()->where('session_id', $session_id)->where('website_id', $this->website->website_id)->getOne('visitors_sessions')) {
            die();
        }

        /* Get session events */
        $session_events_result = database()->query("SELECT * FROM `sessions_events` WHERE `session_id` = {$session->session_id} ORDER BY `event_id` ASC");

        $events = [];

        while($row = $session_events_result->fetch_object()) {
            $events[] = $row;
        }

        /* Get the child events */
        $session_events_children_result = database()->query("SELECT * FROM `events_children` WHERE `session_id` = {$session->session_id} ORDER BY `id` ASC");

        $events_children = [];

        while($row = $session_events_children_result->fetch_object()) {

            if(!isset($events_children[$row->event_id])) {
                $events_children[$row->event_id] = [];
            }

            $row->data = json_decode($row->data);

            $events_children[$row->event_id][] = $row;
        }

        /* Prepare the view */
        $data = [
            'session'           => $session,
            'events'            => $events,
            'events_children'   => $events_children
        ];

        $view = new \Altum\View('session/ajaxed_partials/events', (array) $this);

        Response::json('', 'success', ['html' => $view->run($data)]);

    }

}
