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


use Altum\Captcha;

defined('ALTUMCODE') || die();

class Dashboard extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Get some stats */
        $total_transfers = \Altum\Cache::cache_function_result('transfers_total?user_id=' . $this->user->user_id, null, function() {
            return db()->where('user_id', $this->user->user_id)->getValue('transfers', 'count(*)');
        });

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user, false);

        /* Get available pixels */
        $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Dashboard features */
        $dashboard_features = ((array) $this->user->preferences->dashboard) + array_fill_keys(require APP_PATH . 'includes/available_dashboard_features.php', true);

        /* Get the dns monitors */
        if($dashboard_features['transfers']) {
            /* Get the transfers */
            $transfers = [];
            $transfers_result = database()->query("SELECT * FROM `transfers` WHERE `user_id` = {$this->user->user_id} ORDER BY `transfer_id` DESC LIMIT 5");
            while($row = $transfers_result->fetch_object()) {
                $row->full_url = (new \Altum\Models\Transfers())->get_transfer_full_url($row, $this->user, $domains);
                $row->settings = json_decode($row->settings ?? '');
                $transfers[] = $row;
            }
        }

        /* Transfer requests */
        if($dashboard_features['transfer_requests'] && settings()->transfers->transfer_requests_is_enabled) {
            /* Get the transfer requests */
            $transfer_requests = [];
            $transfer_requests_result = database()->query("SELECT * FROM `transfer_requests` WHERE `user_id` = {$this->user->user_id} ORDER BY `transfer_request_id` DESC LIMIT 5");
            while($row = $transfer_requests_result->fetch_object()) {
                $row->full_url = (new \Altum\Models\TransferRequests())->get_transfer_request_full_url($row, $this->user, $domains);
                $row->settings = json_decode($row->settings ?? '');
                $transfer_requests[] = $row;
            }
        }

        if(settings()->transfers->transfer_requests_is_enabled) {
            /* Get some stats */
            $total_transfer_requests = \Altum\Cache::cache_function_result('transfer_requests_total?user_id=' . $this->user->user_id, null, function() {
                return db()->where('user_id', $this->user->user_id)->getValue('transfer_requests', 'count(*)');
            });
        }

        /* Initiate captcha */
        $captcha = new Captcha();

        /* Prepare the view */
        $data = [
            'transfers' => $transfers ?? [],
            'projects' => $projects,
            'pixels' => $pixels,
            'notification_handlers' => $notification_handlers,
            'total_transfers' => $total_transfers,
            'domains' => $domains,
            'captcha' => $captcha,

            'total_transfer_requests' => $total_transfer_requests ?? null,
            'transfer_requests' => $transfer_requests ?? [],
        ];

        $view = new \Altum\View('dashboard/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
