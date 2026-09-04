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

use Altum\Title;

defined('ALTUMCODE') || die();

class AdminStatistics extends Controller {
    public $type;
    public $datetime;

    public function index() {

        $this->type = isset($this->params[0]) && method_exists($this, $this->params[0]) ? input_clean($this->params[0]) : 'growth';

        $this->datetime = \Altum\Date::get_start_end_dates_new();

        /* Process only data that is needed for that specific page */
        $type_data = $this->{$this->type}();

        /* Set a custom title */
        $dynamic_title = l('admin_statistics.' . $this->type . '.header', null, true) ?? l('admin_' . $this->type . '.title');
        Title::set(sprintf(l('admin_statistics.title'), $dynamic_title));

        /* Main View */
        $data = [
            'type' => $this->type,
            'datetime' => $this->datetime
        ];
        $data = array_merge($data, $type_data);

        $view = new \Altum\View('admin/statistics/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    protected function database() {
        //ALTUMCODE:DEMO if(DEMO) { \Altum\Alerts::add_error('This command is blocked on the demo.'); redirect('admin/statistics'); };

        /* Database details */
        $database_name = DATABASE_NAME;
        $tables = [];
        $result = database()->query("
            SELECT
                TABLE_NAME AS `table`,
                ROUND((DATA_LENGTH + INDEX_LENGTH)) AS `bytes`,
                TABLE_ROWS as 'rows'
            FROM
                information_schema.TABLES
            WHERE
                TABLE_SCHEMA = '{$database_name}'
            ORDER BY
                (DATA_LENGTH + INDEX_LENGTH)
            DESC;
        ");
        while($row = $result->fetch_object()) {

            $tables[] = $row;

        }

        return [
            'tables' => $tables,
        ];
    }

    protected function local_files() {
        //ALTUMCODE:DEMO if(DEMO) { \Altum\Alerts::add_error('This command is blocked on the demo.'); redirect('admin/statistics'); };

        $base_directory = UPLOADS_PATH;
        $folders = [];
        $total_statistics = [
            'total_files' => 0,
            'total_size' => 0,
        ];

        /* List only the main folders inside the base directory */
        foreach (new \DirectoryIterator($base_directory) as $folder_info) {
            if($folder_info->isDot() || !$folder_info->isDir()) {
                continue;
            }

            $folder_name = $folder_info->getFilename();
            $folder_path = $base_directory . DIRECTORY_SEPARATOR . $folder_name;

            $folders[$folder_name] = [
                'total_files'      => 0,
                'total_size'       => 0,
                'extensions'       => [],
            ];

            /* Only iterate files directly inside the current main folder */
            foreach (new \DirectoryIterator($folder_path) as $file_info) {
                if($file_info->isDot() || !$file_info->isFile()) {
                    continue;
                }

                $file_name = $file_info->getFilename();

                if($file_name == 'altumcode.com') {
                    continue;
                }

                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $file_size = $file_info->getSize();

                $folders[$folder_name]['total_files'] += 1;
                $folders[$folder_name]['total_size'] += $file_size;
                $folders[$folder_name]['extensions'][$file_extension] = ($folders[$folder_name]['extensions'][$file_extension] ?? 0) + 1;

                $total_statistics['total_files'] += 1;
                $total_statistics['total_size'] += $file_size;
            }

            uasort($folders, function ($folder_a, $folder_b) {
                /* Sort folders by total_size descending */
                if($folder_a['total_size'] == $folder_b['total_size']) {
                    return 0;
                }
                return ($folder_a['total_size'] > $folder_b['total_size']) ? -1 : 1;
            });
        }

        return [
            'folders' => $folders,
            'total_statistics' => $total_statistics,
        ];
    }

    protected function growth() {

        $total = ['users' => 0, 'users_logs' => 0];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        /* Users */
        $users_chart = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                 `users`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $users_chart[$row->formatted_date] = [
                'users' => $row->total
            ];

            $total['users'] += $row->total;
        }

        $users_chart = get_chart_data($users_chart);

        /* Users logs */
        $users_logs_chart = [];
        $result = database()->query("
            SELECT
                 COUNT(DISTINCT `user_id`) AS `total`,
                 DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                 `users_logs`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $users_logs_chart[$row->formatted_date] = [
                'users_logs' => $row->total
            ];

            $total['users_logs'] += $row->total;
        }

        $users_logs_chart = get_chart_data($users_logs_chart);

        return [
            'total' => $total,
            'users_chart' => $users_chart,
            'users_logs_chart' => $users_logs_chart,
        ];
    }

    protected function users_map() {

        $total = ['continents' => 0, 'countries' => 0, 'cities' => 0,];

        /* Continents */
        $continents = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `continent_code`
            FROM
                 `users`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `continent_code`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $continents[$row->continent_code ?? ''] = $row->total;
            $total['continents'] += $row->total;
        }

        /* Countries */
        $countries_map = [];
        $countries = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `country`
            FROM
                 `users`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `country`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $countries[$row->country ?? ''] = $row->total;
            $countries_map[$row->country ?? ''] = ['users' => $row->total];
            $total['countries'] += $row->total;
        }

        /* Cities */
        $cities = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `country`,
                 `city_name`
            FROM
                 `users`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `country`,
                `city_name`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $cities[$row->country . '#' . $row->city_name] = $row->total;
            $total['cities'] += $row->total;
        }

        return [
            'continents' => $continents,
            'countries' => $countries,
            'countries_map' => $countries_map,
            'cities' => $cities,
            'total' => $total,
        ];
    }

    protected function users() {

        $total = ['devices' => 0, 'sources' => 0, 'plans' => 0, 'operating_systems' => 0, 'browsers' => 0,];

        /* Device types */
        $devices = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `device_type`
            FROM
                 `users`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `device_type`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $devices[$row->device_type] = $row->total;
            $total['devices'] += $row->total;
        }

        /* Operating systems */
        $operating_systems = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `os_name`
            FROM
                 `users`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `os_name`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $operating_systems[$row->os_name] = $row->total;
            $total['operating_systems'] += $row->total;
        }

        /* Browsers */
        $browsers = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `browser_name`
            FROM
                 `users`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `browser_name`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $browsers[$row->browser_name] = $row->total;
            $total['browsers'] += $row->total;
        }

        /* Sources */
        $sources = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `source`
            FROM
                 `users`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `source`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $sources[$row->source] = $row->total;
            $total['sources'] += $row->total;
        }

        /* Plans */
        $plans = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `plan_id`
            FROM
                 `users`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `plan_id`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $plans[$row->plan_id] = $row->total;
            $total['plans'] += $row->total;
        }

        return [
            'devices' => $devices,
            'operating_systems' => $operating_systems,
            'browsers' => $browsers,
            'sources' => $sources,
            'plans' => $plans,
            'total' => $total,
        ];
    }

    protected function payments() {

        $total = ['total_amount' => 0, 'total_payments' => 0];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $payments_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total_payments`, DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`, TRUNCATE(SUM(`total_amount_default_currency`), 2) AS `total_amount` FROM `payments` WHERE `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $payments_chart[$row->formatted_date] = [
                'total_amount' => $row->total_amount,
                'total_payments' => $row->total_payments
            ];

            $total['total_amount'] += $row->total_amount;
            $total['total_payments'] += $row->total_payments;
        }

        $payments_chart = get_chart_data($payments_chart);

        /* Payment processors */
        $processors = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `processor`,
                 TRUNCATE(SUM(`total_amount_default_currency`), 2) AS `total_amount`
            FROM
                 `payments`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `processor`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $processors[] = [
                'processor' => $row->processor,
                'total' => $row->total,
                'total_amount' => $row->total_amount,
            ];
        }

        /* Plans */
        $payments_plans = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `plan_id`,
                 TRUNCATE(SUM(`total_amount_default_currency`), 2) AS `total_amount`
            FROM
                 `payments`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `plan_id`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $payments_plans[] = [
                'plan_id' => $row->plan_id,
                'total' => $row->total,
                'total_amount' => $row->total_amount,
            ];
        }

        /* Payment types */
        $types = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `type`,
                 TRUNCATE(SUM(`total_amount_default_currency`), 2) AS `total_amount`
            FROM
                 `payments`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `type`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $types[] = [
                'type' => $row->type,
                'total' => $row->total,
                'total_amount' => $row->total_amount,
            ];
        }

        /* Payment freuqencies */
        $frequencies = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `frequency`,
                 TRUNCATE(SUM(`total_amount_default_currency`), 2) AS `total_amount`
            FROM
                 `payments`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `frequency`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $frequencies[] = [
                'frequency' => $row->frequency,
                'total' => $row->total,
                'total_amount' => $row->total_amount,
            ];
        }

        return [
            'total' => $total,
            'payments_chart' => $payments_chart,
            'payments_plans' => $payments_plans,
            'frequencies' => $frequencies,
            'types' => $types,
            'processors' => $processors,
            'payment_processors' => require APP_PATH . 'includes/payment_processors.php',
            'plans' => (new \Altum\Models\Plan())->get_plans(),
        ];

    }

    protected function redeemed_codes() {

        $total = ['discount_codes' => 0, 'redeemable_codes' => 0];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $chart = [];
        $result = database()->query("SELECT `type`, COUNT(`type`) AS `total`, DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `redeemed_codes` WHERE `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`, `type`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            if(isset($chart[$row->formatted_date])) {
                $chart[$row->formatted_date] = [
                    'discount' => $row->type == 'discount' ? $chart[$row->formatted_date]['discount'] + $row->total : $chart[$row->formatted_date]['discount'],
                    'redeemable' => $row->type == 'redeemable' ? $chart[$row->formatted_date]['redeemable'] + $row->total : $chart[$row->formatted_date]['redeemable'],
                ];
            } else {
                $chart[$row->formatted_date] = [
                    'discount' => $row->type == 'discount' ? $row->total : 0,
                    'redeemable' => $row->type == 'redeemable' ? $row->total : 0,
                ];
            }

            $total['discount_codes'] += $row->type == 'discount' ? $row->total : 0;
            $total['redeemable_codes'] += $row->type == 'redeemable' ? $row->total : 0;
        }

        $chart = get_chart_data($chart);

        return [
            'total' => $total,
            'chart' => $chart,
        ];

    }

    protected function affiliates_commissions() {

        $total = ['amount' => 0, 'total_affiliates_commissions' => 0];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $affiliates_commissions_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total_affiliates_commissions`, DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`, TRUNCATE(SUM(`amount`), 2) AS `amount` FROM `affiliates_commissions` WHERE `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $affiliates_commissions_chart[$row->formatted_date] = [
                'amount' => $row->amount,
                'total_affiliates_commissions' => $row->total_affiliates_commissions
            ];

            $total['amount'] += $row->amount;
            $total['total_affiliates_commissions'] += $row->total_affiliates_commissions;
        }

        $affiliates_commissions_chart = get_chart_data($affiliates_commissions_chart);

        return [
            'total' => $total,
            'affiliates_commissions_chart' => $affiliates_commissions_chart
        ];

    }
    protected function affiliates_withdrawals() {

        $total = ['amount' => 0, 'total_affiliates_withdrawals' => 0];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $affiliates_withdrawals_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total_affiliates_withdrawals`, DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`, TRUNCATE(SUM(`amount`), 2) AS `amount` FROM `affiliates_withdrawals` WHERE `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $affiliates_withdrawals_chart[$row->formatted_date] = [
                'amount' => $row->amount,
                'total_affiliates_withdrawals' => $row->total_affiliates_withdrawals
            ];

            $total['amount'] += $row->amount;
            $total['total_affiliates_withdrawals'] += $row->total_affiliates_withdrawals;
        }

        $affiliates_withdrawals_chart = get_chart_data($affiliates_withdrawals_chart);

        return [
            'total' => $total,
            'affiliates_withdrawals_chart' => $affiliates_withdrawals_chart
        ];

    }

    protected function broadcasts() {

        $total = [
            'broadcasts' => 0,
            'sent_emails' => 0,
            'views' => 0,
            'clicks' => 0,
            'unique_viewers' => 0,
            'unique_clickers' => 0,
        ];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        /* Broadcasts */
        $broadcasts_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`, SUM(`sent_emails`) AS `sent_emails` FROM `broadcasts` WHERE `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}' GROUP BY `formatted_date` ORDER BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $broadcasts_chart[$row->formatted_date] = [
                'broadcasts' => $row->total,
                'sent_emails' => $row->sent_emails,
            ];

            $total['broadcasts'] += $row->total;
            $total['sent_emails'] += $row->sent_emails;
        }

        $broadcasts_chart = get_chart_data($broadcasts_chart);

        /* Broadcast engagement */
        $statistics_convert_tz_sql = get_convert_tz_sql('`broadcasts_statistics`.`datetime`', $this->user->timezone);

        $broadcasts_engagement_chart = [];
        $result = database()->query("
            SELECT
                COUNT(CASE WHEN `type` = 'view' THEN 1 END) AS `views`,
                COUNT(CASE WHEN `type` = 'click' THEN 1 END) AS `clicks`,
                DATE_FORMAT({$statistics_convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `broadcasts_statistics`
            WHERE
                `broadcasts_statistics`.`datetime` >= '{$this->datetime['query_start_date']}' AND `broadcasts_statistics`.`datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $broadcasts_engagement_chart[$row->formatted_date] = [
                'views' => $row->views,
                'clicks' => $row->clicks,
            ];

            $total['views'] += $row->views;
            $total['clicks'] += $row->clicks;
        }

        $broadcasts_engagement_chart = get_chart_data($broadcasts_engagement_chart);

        /* Unique recipients */
        $result = database()->query("
            SELECT
                COUNT(DISTINCT CASE WHEN `type` = 'view' THEN CONCAT('broadcast_', `broadcast_id`, '_', IF(`user_id` IS NOT NULL, CONCAT('user_', `user_id`), CONCAT('broadcast_subscriber_', `broadcast_subscriber_id`))) END) AS `unique_viewers`,
                COUNT(DISTINCT CASE WHEN `type` = 'click' THEN CONCAT('broadcast_', `broadcast_id`, '_', IF(`user_id` IS NOT NULL, CONCAT('user_', `user_id`), CONCAT('broadcast_subscriber_', `broadcast_subscriber_id`))) END) AS `unique_clickers`
            FROM
                `broadcasts_statistics`
            WHERE
                `broadcasts_statistics`.`datetime` >= '{$this->datetime['query_start_date']}' AND `broadcasts_statistics`.`datetime` < '{$this->datetime['query_end_date']}'
        ");
        $row = $result->fetch_object();

        $total['unique_viewers'] = $row->unique_viewers;
        $total['unique_clickers'] = $row->unique_clickers;

        return [
            'total' => $total,
            'broadcasts_chart' => $broadcasts_chart,
            'broadcasts_engagement_chart' => $broadcasts_engagement_chart,
        ];

    }

    protected function broadcast_subscribers() {

        $total = [
            'broadcast_subscribers' => 0,
            'statuses' => 0,
            'sources' => 0,
            'types' => 0,
        ];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        /* Broadcast subscribers */
        $broadcast_subscribers_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `broadcast_subscribers` WHERE `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $broadcast_subscribers_chart[$row->formatted_date] = [
                'broadcast_subscribers' => $row->total,
            ];

            $total['broadcast_subscribers'] += $row->total;
        }

        $broadcast_subscribers_chart = get_chart_data($broadcast_subscribers_chart);

        /* Statuses */
        $statuses = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `status`
            FROM
                 `broadcast_subscribers`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `status`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $statuses[$row->status] = $row->total;
            $total['statuses'] += $row->total;
        }

        /* Sources */
        $sources = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `source`
            FROM
                 `broadcast_subscribers`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `source`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $sources[$row->source] = $row->total;
            $total['sources'] += $row->total;
        }

        /* Subscriber types */
        $types = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 IF(`user_id` IS NULL, 'guest', 'registered') AS `type`
            FROM
                 `broadcast_subscribers`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `type`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $types[$row->type] = $row->total;
            $total['types'] += $row->total;
        }

        return [
            'total' => $total,
            'broadcast_subscribers_chart' => $broadcast_subscribers_chart,
            'statuses' => $statuses,
            'sources' => $sources,
            'types' => $types,
        ];

    }

    protected function internal_notifications() {

        $total = ['internal_notifications' => 0, 'read_notifications' => 0];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $internal_notifications_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`, SUM(`is_read`) AS `read_notifications` FROM `internal_notifications` WHERE `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $internal_notifications_chart[$row->formatted_date] = [
                'internal_notifications' => $row->total,
                'read_notifications' => $row->read_notifications,
            ];

            $total['internal_notifications'] += $row->total;
            $total['read_notifications'] += $row->read_notifications;
        }

        $internal_notifications_chart = get_chart_data($internal_notifications_chart);

        return [
            'total' => $total,
            'internal_notifications_chart' => $internal_notifications_chart,
        ];

    }

    protected function push_notifications() {

        $total = ['push_notifications' => 0, 'sent_push_notifications' => 0];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $push_notifications_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`, SUM(`sent_push_notifications`) AS `sent_push_notifications` FROM `push_notifications` WHERE `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $push_notifications_chart[$row->formatted_date] = [
                'push_notifications' => $row->total,
                'sent_push_notifications' => $row->sent_push_notifications,
            ];

            $total['push_notifications'] += $row->total;
            $total['sent_push_notifications'] += $row->sent_push_notifications;
        }

        $push_notifications_chart = get_chart_data($push_notifications_chart);

        return [
            'total' => $total,
            'push_notifications_chart' => $push_notifications_chart,
        ];

    }

    protected function push_subscribers() {

        $total = ['push_subscribers' => 0];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $push_subscribers_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `push_subscribers` WHERE `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $push_subscribers_chart[$row->formatted_date] = [
                'push_subscribers' => $row->total,
            ];

            $total['push_subscribers'] += $row->total;
        }

        $push_subscribers_chart = get_chart_data($push_subscribers_chart);

        return [
            'total' => $total,
            'push_subscribers_chart' => $push_subscribers_chart,
        ];

    }

    protected function websites() {

        $total = ['websites' => 0];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        /* Websites */
        $websites_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `websites`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $websites_chart[$row->formatted_date] = [
                'websites' => $row->total
            ];

            $total['websites'] += $row->total;
        }

        $websites_chart = get_chart_data($websites_chart);

        return [
            'total' => $total,
            'websites_chart' => $websites_chart,
        ];

    }

    protected function lightweight_events() {

        $total = ['lightweight_events' => 0];

        /* Monitors */
        $convert_tz_sql = get_convert_tz_sql('`date`', $this->user->timezone);

        $lightweight_events_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `lightweight_events`
            WHERE
                `date` >= '{$this->datetime['query_start_date']}' AND `date` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $lightweight_events_chart[$row->formatted_date] = [
                'lightweight_events' => $row->total
            ];

            $total['lightweight_events'] += $row->total;
        }

        $lightweight_events_chart = get_chart_data($lightweight_events_chart);

        return [
            'total' => $total,
            'lightweight_events_chart' => $lightweight_events_chart,
        ];

    }

    protected function sessions_events() {

        $total = ['sessions_events' => 0];

        /* Monitors */
        $convert_tz_sql = get_convert_tz_sql('`date`', $this->user->timezone);

        $sessions_events_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `sessions_events`
            WHERE
                `date` >= '{$this->datetime['query_start_date']}' AND `date` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $sessions_events_chart[$row->formatted_date] = [
                'sessions_events' => $row->total
            ];

            $total['sessions_events'] += $row->total;
        }

        $sessions_events_chart = get_chart_data($sessions_events_chart);

        return [
            'total' => $total,
            'sessions_events_chart' => $sessions_events_chart,
        ];

    }

    protected function events_children() {

        $total = ['click' => 0, 'form' => 0, 'scroll' => 0, 'resize' => 0];

        /* Track conversions */
        $convert_tz_sql = get_convert_tz_sql('`date`', $this->user->timezone);

        $events_children_chart = [];
        $result = database()->query("
            SELECT
                `type`,
                COUNT(`id`) AS `total`,
                DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `events_children`
            WHERE
                `date` >= '{$this->datetime['query_start_date']}' AND `date` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`,
                `type`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {

            /* Handle if the date key is not already set */
            if(!array_key_exists($row->formatted_date, $events_children_chart)) {
                $events_children_chart[$row->formatted_date] = [
                    'click' => 0,
                    'form' => 0,
                    'scroll' => 0,
                    'resize' => 0,
                ];
            }

            $events_children_chart[$row->formatted_date][$row->type] = $row->total;

            $total[$row->type] += $row->total;
        }

        $events_children_chart = get_chart_data($events_children_chart);

        return [
            'total' => $total,
            'events_children_chart' => $events_children_chart
        ];
    }

    protected function sessions_replays() {

        $total = ['sessions_replays' => 0, 'sessions_replays_total_size' => 0];

        /* Monitors */
        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $sessions_replays_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                SUM(`size`) as `total_size`,
                DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `sessions_replays`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $sessions_replays_chart[$row->formatted_date] = [
                'sessions_replays' => $row->total,
                'sessions_replays_total_size' => $row->total_size,
            ];

            $total['sessions_replays'] += $row->total;
            $total['sessions_replays_total_size'] += $row->total_size;
        }

        $sessions_replays_chart = get_chart_data($sessions_replays_chart);

        return [
            'total' => $total,
            'sessions_replays_chart' => $sessions_replays_chart,
        ];

    }

    protected function websites_heatmaps() {

        $total = ['websites_heatmaps' => 0, 'websites_heatmaps_total_size' => 0];

        /* Monitors */
        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $websites_heatmaps_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                SUM(`desktop_size` + `tablet_size` + `mobile_size`) AS `total_size`,
                DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `websites_heatmaps`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $websites_heatmaps_chart[$row->formatted_date] = [
                'websites_heatmaps' => $row->total,
                'websites_heatmaps_total_size' => $row->total_size,
            ];

            $total['websites_heatmaps'] += $row->total;
            $total['websites_heatmaps_total_size'] += $row->total_size;
        }

        $websites_heatmaps_chart = get_chart_data($websites_heatmaps_chart);

        return [
            'total' => $total,
            'websites_heatmaps_chart' => $websites_heatmaps_chart,
        ];

    }

    protected function websites_goals() {

        $total = ['websites_goals' => 0];

        /* Monitors */
        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $websites_goals_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `websites_goals`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $websites_goals_chart[$row->formatted_date] = [
                'websites_goals' => $row->total
            ];

            $total['websites_goals'] += $row->total;
        }

        $websites_goals_chart = get_chart_data($websites_goals_chart);

        return [
            'total' => $total,
            'websites_goals_chart' => $websites_goals_chart,
        ];

    }

    protected function goals_conversions() {

        $total = ['goals_conversions' => 0];

        /* Monitors */
        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $goals_conversions_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `goals_conversions`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $goals_conversions_chart[$row->formatted_date] = [
                'goals_conversions' => $row->total
            ];

            $total['goals_conversions'] += $row->total;
        }

        $goals_conversions_chart = get_chart_data($goals_conversions_chart);

        return [
            'total' => $total,
            'goals_conversions_chart' => $goals_conversions_chart,
        ];

    }


    protected function outbound_clicks() {

        $total = ['outbound_clicks' => 0];

        /* Monitors */
        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $outbound_clicks_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `outbound_clicks`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $outbound_clicks_chart[$row->formatted_date] = [
                'outbound_clicks' => $row->total
            ];

            $total['outbound_clicks'] += $row->total;
        }

        $outbound_clicks_chart = get_chart_data($outbound_clicks_chart);

        return [
            'total' => $total,
            'outbound_clicks_chart' => $outbound_clicks_chart,
        ];

    }


    protected function teams() {

        $total = ['teams' => 0];

        /* Monitors */
        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $teams_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `teams`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $teams_chart[$row->formatted_date] = [
                'teams' => $row->total
            ];

            $total['teams'] += $row->total;
        }

        $teams_chart = get_chart_data($teams_chart);

        return [
            'total' => $total,
            'teams_chart' => $teams_chart,
        ];

    }

    protected function email_shield() {

        $total = ['valid' => 0, 'invalid' => 0];

        $convert_tz_sql = get_convert_tz_sql('`date`', $this->user->timezone);

        $chart = [];
        $result = database()->query("SELECT `valid`, `invalid`, DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `email_shield` WHERE `date` >= '{$this->datetime['query_start_date']}' AND `date` < '{$this->datetime['query_end_date']}'");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $chart[$row->formatted_date] = [
                'valid' => $row->valid,
                'invalid' => $row->invalid,
            ];

            $total['valid'] += $row->valid;
            $total['invalid'] += $row->invalid;
        }

        $chart = get_chart_data($chart);

        return [
            'total' => $total,
            'chart' => $chart,
        ];

    }

    protected function image_optimizer() {

        $total = [
            'total' => 0,
            'saved_size' => 0,
            'average_percentage_difference' => 0,
            'rows' => 0,
        ];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                SUM(`original_size`) AS `original_size`,
                SUM(`new_size`) AS `new_size`,
                AVG(`percentage_difference`) AS `average_percentage_difference`,
                DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `image_optimizations`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
        ");

        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $chart[$row->formatted_date] = [
                'total' => $row->total,
                'saved_size' => ($row->original_size - $row->new_size),
                'average_percentage_difference' => $row->average_percentage_difference,
            ];

            $total['total'] += $row->total;
            $total['saved_size'] += ($row->original_size - $row->new_size);
            $total['average_percentage_difference'] += $row->average_percentage_difference;
            $total['rows']++;
        }

        $total['average_percentage_difference'] = $total['rows'] ? $total['average_percentage_difference'] / $total['rows'] : 0;

        $chart = get_chart_data($chart);

        return [
            'total' => $total,
            'chart' => $chart,
        ];

    }

    protected function email_reports() {

        $total = ['email_reports' => 0];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $email_reports_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `email_reports`
            WHERE
                `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $email_reports_chart[$row->formatted_date] = [
                'email_reports' => $row->total
            ];

            $total['email_reports'] += $row->total;
        }

        $email_reports_chart = get_chart_data($email_reports_chart);

        return [
            'total' => $total,
            'email_reports_chart' => $email_reports_chart
        ];
    }

    protected function annotations() {

        $total = ['annotations' => 0];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $annotations_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `annotations` WHERE `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $annotations_chart[$row->formatted_date] = [
                'annotations' => $row->total,
            ];

            $total['annotations'] += $row->total;
        }

        $annotations_chart = get_chart_data($annotations_chart);

        return [
            'total' => $total,
            'annotations_chart' => $annotations_chart,
        ];

    }

    protected function domains() {

        $total = ['domains' => 0];

        $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

        $domains_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `domains` WHERE `datetime` >= '{$this->datetime['query_start_date']}' AND `datetime` < '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date, true);

            $domains_chart[$row->formatted_date] = [
                'domains' => $row->total,
            ];

            $total['domains'] += $row->total;
        }

        $domains_chart = get_chart_data($domains_chart);

        return [
            'total' => $total,
            'domains_chart' => $domains_chart,
        ];

    }

}
