<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
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

namespace Altum;

defined('ALTUMCODE') || die();

class Filters {

    public $allowed_filters = [];
    public $allowed_order_by = [];
    public $allowed_search_by = [];
    public $allowed_results_per_page = [];

    /* Handle datetime range */
    public $allowed_datetime_fields = [];
    public $datetime_field = '';
    public $datetime_start = '';
    public $datetime_start_utc = '';
    public $datetime_end = '';
    public $datetime_end_utc = '';

    public $filters = [];
    public $filters_types = [];
    public $search = '';
    public $search_by = '';
    public $order_by = '';
    public $order_type = '';
    public $results_per_page = 25;

    public $get = [];
    public $has_applied_filters = false;

    private $is_processed = false;

    public function __construct($allowed_filters = [], $allowed_search_by = [], $allowed_order_by = [], $allowed_results_per_page = [], $filters_types = [], $allowed_datetime_fields = []) {

        $this->allowed_filters = $allowed_filters;
        $this->filters_types = $filters_types;
        $this->allowed_order_by = $allowed_order_by;
        $this->allowed_search_by = $allowed_search_by;
        $this->allowed_results_per_page = empty($allowed_results_per_page) ? [10, 25, 50, 100, 250, 500, 1000] : $allowed_results_per_page;
        $this->allowed_datetime_fields = $allowed_datetime_fields;

    }

    public function process() {
        if($this->is_processed) {
            return;
        }

        /* Filters */
        foreach($this->allowed_filters as $filter) {

            if(isset($_GET[$filter]) && $_GET[$filter] != '') {
                $this->filters[$filter] = query_clean($_GET[$filter]);
                $this->get[$filter] = $_GET[$filter];
            }

        }

        /* Search */
        if(count($this->allowed_search_by) && isset($_GET['search']) && isset($_GET['search_by']) && in_array($_GET['search_by'], $this->allowed_search_by)) {

            $_GET['search'] = query_clean($_GET['search']);
            $_GET['search_by'] = query_clean($_GET['search_by']);

            $this->search = $_GET['search'];
            $this->search_by = $_GET['search_by'];

            $this->get['search'] = $_GET['search'];
            $this->get['search_by'] = $_GET['search_by'];
        }

        /* Order by */
        if(count($this->allowed_order_by) && isset($_GET['order_by']) && in_array($_GET['order_by'], $this->allowed_order_by)) {

            $_GET['order_by'] = query_clean($_GET['order_by']);
            $order_type = isset($_GET['order_type']) && in_array($_GET['order_type'], ['ASC', 'DESC']) ? query_clean($_GET['order_type']) : 'ASC';

            $this->order_by = $_GET['order_by'];
            $this->order_type = $order_type;

            $this->get['order_by'] = $_GET['order_by'];
            $this->get['order_type'] = $_GET['order_type'];
        }

        /* Results per page */
        if(isset($_GET['results_per_page']) && in_array($_GET['results_per_page'], $this->allowed_results_per_page)) {
            $this->results_per_page = (int) $_GET['results_per_page'];
            $this->get['results_per_page'] = $_GET['results_per_page'];
        }

        /* Datetime filter */
        if(count($this->allowed_datetime_fields) && isset($_GET['datetime_field']) && in_array($_GET['datetime_field'], $this->allowed_datetime_fields)) {
            $_GET['datetime_field'] = query_clean($_GET['datetime_field']);

            $this->datetime_field = $_GET['datetime_field'];
            $this->get['datetime_field'] = $_GET['datetime_field'];

            if(isset($_GET['datetime_start']) && $_GET['datetime_start'] != '') {
                $this->datetime_start = query_clean($_GET['datetime_start']);
                $this->datetime_start_utc = $this->process_datetime_value($_GET['datetime_start']);
                $this->get['datetime_start'] = $_GET['datetime_start'];
            }

            if(isset($_GET['datetime_end']) && $_GET['datetime_end'] != '') {
                $this->datetime_end = query_clean($_GET['datetime_end']);
                $this->datetime_end_utc = $this->process_datetime_value($_GET['datetime_end']);
                $this->get['datetime_end'] = $_GET['datetime_end'];
            }
        }

        if(count($this->get)) $this->has_applied_filters = true;

        $this->is_processed = true;
    }

    public function get_sql_where($table_prefix = null) {
        if(!$this->is_processed) $this->process();

        $where = '';

        $table_prefix = $table_prefix ? "`{$table_prefix}`." : null;

        /* Filters */
        foreach($this->filters as $key => $value) {
            if(isset($this->filters_types[$key])) {
                switch($this->filters_types[$key]) {
                    case 'json_contains':
                        /* Only allow numbers for array json searching */
                        $value = (int) $value;
                        $where .= " AND JSON_CONTAINS(COALESCE(NULLIF({$table_prefix}`{$key}`, ''), '[]'), '{$value}', '$')";
                        break;
                }
            } else {
                /* Classic */
                $where .= " AND {$table_prefix}`{$key}` = '{$value}'";
            }
        }

        /* Search */
        if($this->search && $this->search_by) {
            $where .= " AND {$table_prefix}`{$this->search_by}` LIKE '%{$this->search}%'";
        }

        /* Datetime */
        if($this->datetime_field) {
            if($this->datetime_start_utc) {
                $where .= " AND {$table_prefix}`{$this->datetime_field}` >= '{$this->datetime_start_utc}'";
            }

            if($this->datetime_end_utc) {
                $where .= " AND {$table_prefix}`{$this->datetime_field}` <= '{$this->datetime_end_utc}'";
            }
        }

        return $where;
    }

    public function get_sql_order_by($table_prefix = null) {
        if(!$this->is_processed) $this->process();

        $order_by = '';

        $table_prefix = $table_prefix ? "`{$table_prefix}`." : null;

        /* Order By */
        if($this->order_by && $this->order_type) {
            $order_by .= " ORDER BY {$table_prefix}`{$this->order_by}` {$this->order_type}";
        }

        return $order_by;
    }

    public function get_results_per_page() {
        return $this->results_per_page;
    }

    public function get_get() {
        return http_build_query($this->get, '', '&');
    }

    public function set_default_order_by($order_by, $order_type) {

        if(!in_array($order_type, ['ASC', 'DESC'])) {
            $order_type = 'DESC';
        }

        if(!$order_by && count($this->allowed_order_by)) {
            $order_by = reset($this->allowed_order_by);
        }

        $this->order_by = $order_by;
        $this->order_type = $order_type;
    }

    public function set_default_results_per_page($results_per_page) {

        if(!$results_per_page) {
            $results_per_page = 25;
        }

        $this->results_per_page = $results_per_page;
    }

    private function process_datetime_value($datetime) {
        if(!$datetime) {
            return null;
        }

        $datetime = query_clean($datetime);

        /* Frontend datetime-local */
        if(strpos($datetime, 'T') !== false) {
            $datetime = str_replace('T', ' ', $datetime);

            if(strlen($datetime) == 16) {
                $datetime .= ':00';
            }

            if(is_logged_in() && user()->timezone != 'UTC') {
                $datetime = (new \DateTime($datetime, new \DateTimeZone(user()->timezone)))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            }

            return $datetime;
        }

        /* API UTC datetime */
        if(preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $datetime)) {
            return $datetime;
        }

        return null;
    }
    
}
