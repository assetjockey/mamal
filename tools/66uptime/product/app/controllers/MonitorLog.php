<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
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

namespace Altum\Controllers;

use Altum\Title;

defined('ALTUMCODE') || die();

class MonitorLog extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->monitors_is_enabled) {
            throw_404();
        }

        \Altum\Authentication::guard();

        $monitor_log_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$monitor_log = db()->where('monitor_log_id', $monitor_log_id)->where('user_id', $this->user->user_id)->getOne('monitors_logs')) {
            redirect('monitors');
        }

        if(!$monitor = db()->where('monitor_id', $monitor_log->monitor_id)->where('user_id', $this->user->user_id)->getOne('monitors')) {
            redirect('monitors');
        }

        $monitor->details = json_decode($monitor->details);
        $monitor->settings = json_decode($monitor->settings ?? '');

        /* Get available ping servers */
        $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();

        /* Set a custom title */
        Title::set(sprintf(l('monitor_log.title'), $monitor->name));

        /* Prepare the view */
        $data = [
            'monitor' => $monitor,
            'monitor_log' => $monitor_log,
            'ping_servers' => $ping_servers,
        ];

        $view = new \Altum\View('monitor-log/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }
}
