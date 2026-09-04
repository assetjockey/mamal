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


defined('ALTUMCODE') || die();

class Dashboard extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $dashboard_features = ((array) $this->user->preferences->dashboard) + array_fill_keys(['websites', 'audits'], true);

        /* Get some stats */
        $total_websites = \Altum\Cache::cache_function_result('websites_total?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function() {
            return db()->where('user_id', $this->user->user_id)->getValue('websites', 'count(*)');
        });

        $total_audits = \Altum\Cache::cache_function_result('audits_total?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function() {
            return db()->where('user_id', $this->user->user_id)->getValue('audits', 'count(*)');
        });

        /* Get current monthly usage */
        $usage = db()->where('user_id', $this->user->user_id)->getOne('users', ['audit_audits_current_month']);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Get websites */
        if($dashboard_features['websites']) {
            $websites = \Altum\Cache::cache_function_result('websites_dashboard?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function () {
                $websites = [];
                $websites_result = database()->query("SELECT * FROM `websites` WHERE `user_id` = {$this->user->user_id} ORDER BY `last_audit_datetime` DESC, `website_id` DESC LIMIT 5");
                while ($row = $websites_result->fetch_object()) {
                    $row->settings = json_decode($row->settings ?? '');
                    $websites[] = $row;
                }

                return $websites;
            });
        }

        /* Get audits */
        if($dashboard_features['audits']) {
            $audits = \Altum\Cache::cache_function_result('audits_dashboard?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function () {
                $audits = [];
                $audits_result = database()->query("SELECT * FROM `audits` WHERE `user_id` = {$this->user->user_id} ORDER BY `last_refresh_datetime` DESC, `audit_id` DESC LIMIT 5");
                while ($row = $audits_result->fetch_object()) {
                    $row->settings = json_decode($row->settings ?? '');
                    $audits[] = $row;
                }

                return $audits;
            });
        }

        /* Prepare the view */
        $data = [
            'total_websites' => $total_websites,
            'total_audits' => $total_audits,
            'usage' => $usage,
            'domains' => $domains,
            'notification_handlers' => $notification_handlers,

            'websites' => $websites ?? null,
            'audits' => $audits ?? null,
        ];

        $view = new \Altum\View('dashboard/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
