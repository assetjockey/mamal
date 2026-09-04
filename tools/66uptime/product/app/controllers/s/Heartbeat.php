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

use Altum\Date;
use Altum\Meta;
use Altum\Title;

defined('ALTUMCODE') || die();

class Heartbeat extends Controller {
    public $status_page;
    public $status_page_user = null;

    public $heartbeat;

    public function index() {

        /* Make sure there are no extra URL additions */
        if(isset($this->params[3])) {
            throw_404();
        }

        /* Parse & control the status_page */
        require_once APP_PATH . 'controllers/s/StatusPage.php';
        $status_page_controller = new \Altum\Controllers\StatusPage((array) $this);

        $status_page_controller->init();

        /* Check if the user has access */
        if(!$status_page_controller->has_access) {
            header('Location: ' . $status_page_controller->status_page->full_url); die();
        }

        /* Set the needed variables for the wrapper */
        $this->status_page_user = $status_page_controller->status_page_user;
        $this->status_page = $status_page_controller->status_page;

        /* Monitor init */
        $this->init();

        /* Prepare date selector stuff */
        $start_date = isset($_GET['start_date']) ? query_clean($_GET['start_date']) : Date::get('', 4);
        $end_date = isset($_GET['end_date']) ? query_clean($_GET['end_date']) : Date::get('', 4);
        $date = \Altum\Date::get_start_end_dates($start_date, $end_date);

        /* Get logs */
        if($this->status_page->theme == 'new-york') {
            $heartbeat_logs_processed = (new \Altum\Models\HeartbeatsLogs())->get_heartbeat_logs_chart_data_by_heartbeat_id($this->heartbeat->heartbeat_id, $date->start_date_query, $date->end_date_query);
        } elseif($this->status_page->theme == 'phoenix') {
            $heartbeat_logs_processed = (new \Altum\Models\HeartbeatsLogs())->get_heartbeat_logs_data_by_heartbeat_id($this->heartbeat->heartbeat_id, $date->start_date_query, $date->end_date_query);
        }

        $total_heartbeat_logs = $heartbeat_logs_processed['total_logs'];
        $total_ok_checks = $heartbeat_logs_processed['total_ok_checks'];
        $total_not_ok_checks = $heartbeat_logs_processed['total_not_ok_checks'];
        $heartbeat_logs_chart = $heartbeat_logs_processed['chart'] ?? null;
        $heartbeat_logs = $heartbeat_logs_processed['logs'] ?? null;

        /* calculate some data */
        $uptime = $total_ok_checks > 0 ? $total_ok_checks / ($total_ok_checks + $total_not_ok_checks) * 100 : 0;
        $downtime = 100 - $uptime;

        /* Get potential incidents */
        $heartbeat_incidents = (new \Altum\Models\HeartbeatsIncidents())->get_heartbeat_incidents_by_heartbeat_id_and_start_datetime_and_end_datetime($this->heartbeat->heartbeat_id, $date->start_date_query, $date->end_date_query);

        /* Add statistics */
        $status_page_controller->create_statistics($this->status_page->status_page_id);

        /* Set a custom title */
        if($this->status_page->settings->title) {
            Title::set(sprintf(l('s_heartbeat.title'), $this->heartbeat->name, $this->status_page->settings->title));
        } else {
            Title::set(sprintf(l('s_heartbeat.title'), $this->heartbeat->name, $this->status_page->name));
        }

        /* Meta */
        Meta::set_canonical_url($this->status_page->full_url . 'heartbeat/' . $this->heartbeat->heartbeat_id);

        /* Set the meta tags */
        Meta::set_social_url($this->status_page->full_url . $this->heartbeat->heartbeat_id);
        Meta::set_social_title(sprintf(l('s_heartbeat.title'), $this->heartbeat->name, $this->status_page->name));
        Meta::set_social_image(!empty($this->status_page->opengraph) ? \Altum\Uploads::get_full_url('status_pages_opengraph') . $this->status_page->opengraph : null);

        /* Prepare the header */
        $view = new \Altum\View('s/partials/header', (array) $this);
        $this->add_view_content('header', $view->run(['status_page' => $this->status_page]));

        /* Main View */
        $data = [
            'status_page' => $this->status_page,
            'status_page_user' => $this->status_page_user,

            'heartbeat' => $this->heartbeat ,
            'heartbeat_logs_chart' => $heartbeat_logs_chart,
            'heartbeat_logs' => $heartbeat_logs,
            'total_heartbeat_logs' => $total_heartbeat_logs,
            'heartbeat_logs_data' => [
                'uptime' => $uptime,
                'downtime' => $downtime,
                'total_ok_checks' => $total_ok_checks,
                'total_not_ok_checks' => $total_not_ok_checks
            ],

            'heartbeat_incidents' => $heartbeat_incidents,

            'date' => $date
        ];

        $view = new \Altum\View('s/heartbeat/' . $this->status_page->theme . '/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function init() {
        $heartbeat_id = \Altum\Router::$data['id'];

        $heartbeat = $this->heartbeat = (new \Altum\Models\Heartbeats())->get_heartbeat_by_heartbeat_id($heartbeat_id);

        if(!$heartbeat || ($heartbeat && (!$heartbeat->is_enabled || !in_array($heartbeat->heartbeat_id, $this->status_page->heartbeats_ids)))) {
            throw_404();
        }

    }

}
