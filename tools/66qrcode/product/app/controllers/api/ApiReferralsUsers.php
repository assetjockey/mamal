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

class ApiReferralsUsers extends Controller {
    use Apiable;

    public function index() {

        if(!\Altum\Plugin::is_active('affiliate') || (\Altum\Plugin::is_active('affiliate') && !settings()->affiliate->is_enabled)) {
            throw_404();
        }

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->get_all();
                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['referred_by_has_converted'], [], ['datetime', 'referred_by_has_converted'], allowed_datetime_fields: ['datetime']));
        $default_order_type = isset($this->user->preferences->default_order_type) ? $this->user->preferences->default_order_type : settings()->main->default_order_type;
        $default_results_per_page = isset($this->user->preferences->default_results_per_page) ? $this->user->preferences->default_results_per_page : settings()->main->default_results_per_page;
        $filters->set_default_order_by('datetime', $default_order_type);
        $filters->set_default_results_per_page($default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows_result = database()->query("SELECT COUNT(*) AS `total` FROM `users` WHERE `referred_by` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object();
        $total_rows = $total_rows_result ? $total_rows_result->total : 0;
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $page, url('api/referrals-users?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Prepare commission status date */
        $pending_affiliate_commissions_date = (new \DateTime())->modify('-30 days')->format('Y-m-d H:i:s');

        /* Get anonymized referred users */
        $data = [];
        $data_result = database()->query("
            SELECT
                `users`.`user_id`,
                `users`.`datetime`,
                `users`.`referred_by_has_converted`,
                COUNT(`affiliates_commissions`.`affiliate_commission_id`) AS `total_affiliate_commissions`,
                COALESCE(ROUND(SUM(`affiliates_commissions`.`amount`), 2), 0) AS `total_affiliate_commissions_amount`,
                SUM(CASE WHEN `affiliates_commissions`.`affiliate_commission_id` IS NOT NULL AND `affiliates_commissions`.`datetime` > '{$pending_affiliate_commissions_date}' AND `affiliates_commissions`.`is_withdrawn` = 0 THEN 1 ELSE 0 END) AS `pending_affiliate_commissions`,
                SUM(CASE WHEN `affiliates_commissions`.`affiliate_commission_id` IS NOT NULL AND `affiliates_commissions`.`datetime` < '{$pending_affiliate_commissions_date}' AND `affiliates_commissions`.`is_withdrawn` = 0 THEN 1 ELSE 0 END) AS `approved_affiliate_commissions`,
                SUM(CASE WHEN `affiliates_commissions`.`affiliate_commission_id` IS NOT NULL AND `affiliates_commissions`.`is_withdrawn` = 1 THEN 1 ELSE 0 END) AS `withdrawn_affiliate_commissions`,
                MAX(`affiliates_commissions`.`datetime`) AS `latest_affiliate_commission_datetime`,
                GROUP_CONCAT(DISTINCT `payments`.`type` ORDER BY `payments`.`type` SEPARATOR ',') AS `payment_types`
            FROM
                `users`
            LEFT JOIN
                `affiliates_commissions` ON `users`.`user_id` = `affiliates_commissions`.`referred_user_id` AND `affiliates_commissions`.`user_id` = {$this->user->user_id}
            LEFT JOIN
                `payments` ON `affiliates_commissions`.`payment_id` = `payments`.`id`
            WHERE
                `users`.`referred_by` = {$this->user->user_id}
                {$filters->get_sql_where('users')}
            GROUP BY
                `users`.`user_id`,
                `users`.`datetime`,
                `users`.`referred_by_has_converted`
            {$filters->get_sql_order_by('users')}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Generate anonymized referral code */
            $referral_hash = str_replace(['+', '/', '='], '', base64_encode(hash('sha256', $row->user_id . $this->user->referral_key, true)));
            $referral_code = 'ref_' . mb_strtolower(mb_substr($referral_hash . md5($row->user_id . $this->user->referral_key), 0, 6));

            /* Prepare payment types */
            if($row->payment_types) {
                $payment_types = explode(',', $row->payment_types);
            } else {
                $payment_types = [];
            }

            /* Prepare commission counters */
            $pending_affiliate_commissions = (int) $row->pending_affiliate_commissions;
            $approved_affiliate_commissions = (int) $row->approved_affiliate_commissions;
            $withdrawn_affiliate_commissions = (int) $row->withdrawn_affiliate_commissions;
            $total_affiliate_commissions = (int) $row->total_affiliate_commissions;

            /* Prepare commission statuses */
            $commission_statuses = [];

            if($pending_affiliate_commissions) {
                $commission_statuses[] = 'pending';
            }

            if($approved_affiliate_commissions) {
                $commission_statuses[] = 'approved';
            }

            if($withdrawn_affiliate_commissions) {
                $commission_statuses[] = 'withdrawn';
            }

            if(!$total_affiliate_commissions) {
                $commission_statuses[] = 'none';
            }

            /* Prepare the data */
            $row = [
                'referral_code' => $referral_code,
                'status' => $row->referred_by_has_converted ? 'converted' : 'signed_up',
                'is_converted' => (bool) $row->referred_by_has_converted,
                'payment_types' => $payment_types,
                'commission_statuses' => $commission_statuses,
                'total_affiliate_commissions' => $total_affiliate_commissions,
                'total_affiliate_commissions_amount' => (float) $row->total_affiliate_commissions_amount,
                'pending_affiliate_commissions' => $pending_affiliate_commissions,
                'approved_affiliate_commissions' => $approved_affiliate_commissions,
                'withdrawn_affiliate_commissions' => $withdrawn_affiliate_commissions,
                'latest_affiliate_commission_datetime' => $row->latest_affiliate_commission_datetime,
                'currency' => settings()->payment->default_currency,
                'datetime' => $row->datetime,
            ];

            $data[] = $row;
        }

        /* Prepare the data */
        $meta = [
            'page' => $page,
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
            'self' => $paginator->getPageUrl($page)
        ]];

        Response::jsonapi_success($data, $meta, 200, $others);
    }

}
