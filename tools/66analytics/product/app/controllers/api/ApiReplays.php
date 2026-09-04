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

use Altum\Models\SessionsReplays;
use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiReplays extends Controller {
    use Apiable;

    public function index() {

        $this->verify_request();

        if(!settings()->analytics->sessions_replays_is_enabled) {
            $this->return_404();
        }

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                if(isset($this->params[0], $this->params[1])) {
                    if(isset($this->params[2])) {
                        $this->return_404();
                    }

                    if($this->params[1] == 'data') {
                        $this->get_data();
                    }

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
        $filters = (new \Altum\Filters(['website_id', 'user_id', 'session_id', 'visitor_id', 'is_offloaded', 'is_too_short'], [], ['replay_id', 'session_id', 'visitor_id', 'events', 'size', 'is_offloaded', 'is_too_short', 'datetime', 'last_datetime', 'expiration_date'], allowed_datetime_fields: ['datetime', 'last_datetime', 'expiration_date']));
        $filters->set_default_order_by('replay_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("
            SELECT
                COUNT(*) AS `total`
            FROM
                `sessions_replays`
            WHERE
                `sessions_replays`.`website_id` = {$website_id}
                {$filters->get_sql_where('sessions_replays')}
        ")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/replays?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                `sessions_replays`.*
            FROM
                `sessions_replays`
            WHERE
                `sessions_replays`.`website_id` = {$website_id}
                {$filters->get_sql_where('sessions_replays')}
                {$filters->get_sql_order_by('sessions_replays')}

            {$paginator->get_sql_limit()}
        ");

        while($row = $data_result->fetch_object()) {
            $data[] = $this->process_replay($row);
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

        $replay_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $replay = database()->query("
            SELECT
                `sessions_replays`.*
            FROM
                `sessions_replays`
            WHERE
                `sessions_replays`.`replay_id` = {$replay_id}
        ")->fetch_object() ?? null;

        /* We haven't found the resource */
        if(!$replay) {
            $this->return_404();
        }

        /* Access check */
        if(!db()->where('user_id', $this->user->user_id)->where('website_id', $replay->website_id)->where('tracking_type', 'advanced')->has('websites')) {
            $this->return_404();
        }

        Response::jsonapi_success($this->process_replay($replay));

    }

    private function get_data() {

        $replay_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $replay = db()->where('replay_id', $replay_id)->getOne('sessions_replays');

        /* We haven't found the resource */
        if(!$replay) {
            $this->return_404();
        }

        /* Access check */
        if(!db()->where('user_id', $this->user->user_id)->where('website_id', $replay->website_id)->where('tracking_type', 'advanced')->has('websites')) {
            $this->return_404();
        }

        Response::jsonapi_success(array_merge([
            'id' => (int) $replay->replay_id,
            'session_id' => (int) $replay->session_id,
            'visitor_id' => (int) $replay->visitor_id,
            'website_id' => (int) $replay->website_id,
        ], $this->get_replay_data($replay)));

    }

    private function delete() {

        $replay_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $replay = db()->where('replay_id', $replay_id)->getOne('sessions_replays');

        /* We haven't found the resource */
        if(!$replay) {
            $this->return_404();
        }

        /* Access check */
        if(!db()->where('user_id', $this->user->user_id)->where('website_id', $replay->website_id)->where('tracking_type', 'advanced')->has('websites')) {
            $this->return_404();
        }

        (new SessionsReplays())->delete($replay_id);

        http_response_code(200);
        die();

    }

    private function get_replay_data($replay) {

        set_time_limit(0);

        $rows = [];
        $pageviews = [];

        /* Offload */
        if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url && $replay->is_offloaded) {

            try {
                $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
                $file_name = base64_encode($replay->session_id . $replay->datetime) . '.txt';

                $result = $s3->getObject([
                    'Bucket' => settings()->offload->storage_name,
                    'Key'    => UPLOADS_URL_PATH . 'store/' . $file_name,
                ]);

                $file_data = unserialize($result['Body']);

            } catch (\Exception $exception) {
                return [
                    'rows' => [],
                    'pageviews' => [],
                ];
            }

            if(!is_array($file_data)) {
                return [
                    'rows' => [],
                    'pageviews' => [],
                ];
            }

            foreach($file_data as $entry) {
                $row = [
                    'type' => (int) $entry->type,
                    'data' => json_decode(gzdecode($entry->data)),
                    'timestamp' => (int) $entry->timestamp,
                ];

                $rows[] = $row;

                if($row['type'] === 4) {
                    $row['datetime'] = (new \DateTime())->setTimestamp((int) ($row['timestamp'] / 1000))->format('Y-m-d H:i:s');
                    $row['path'] = parse_url($row['data']->href, PHP_URL_PATH);
                    $row['time'] = \Altum\Date::get($row['datetime'], 3);

                    $pageviews[] = $row;
                }
            }

        }

        /* Local */
        else {

            \Altum\Cache::store_initialize();

            $index_item = cache('store_adapter')->getItem('session_replay_keys_' . $replay->session_id);
            $session_replay_keys = $index_item->get() ?: [];

            if(empty($session_replay_keys)) {
                return [
                    'rows' => [],
                    'pageviews' => [],
                ];
            }

            foreach($session_replay_keys as $chunk_key) {

                $chunk_item = cache('store_adapter')->getItem($chunk_key);
                $chunk_gzip = $chunk_item->get();

                if(!$chunk_gzip) {
                    continue;
                }

                $batch_events = json_decode(gzdecode($chunk_gzip));

                if(!is_array($batch_events)) {
                    continue;
                }

                foreach($batch_events as $event) {
                    $row = [
                        'type' => (int) $event->type,
                        'data' => $event->data,
                        'timestamp' => (int) ($event->timestamp ?? 0),
                    ];

                    $rows[] = $row;

                    if($row['type'] === 4) {
                        $row['datetime'] = (new \DateTime())->setTimestamp((int) ($row['timestamp'] / 1000))->format('Y-m-d H:i:s');
                        $row['path'] = parse_url($row['data']->href, PHP_URL_PATH);
                        $row['time'] = \Altum\Date::get($row['datetime'], 3);

                        $pageviews[] = $row;
                    }
                }
            }
        }

        return [
            'rows' => $rows,
            'pageviews' => $pageviews,
        ];

    }

    private function process_replay($replay) {

        $duration = $replay->last_datetime ? (new \DateTime($replay->last_datetime))->getTimestamp() - (new \DateTime($replay->datetime))->getTimestamp() : null;

        return [
            'id' => (int) $replay->replay_id,
            'session_id' => (int) $replay->session_id,
            'visitor_id' => (int) $replay->visitor_id,
            'website_id' => (int) $replay->website_id,
            'user_id' => (int) $replay->user_id,
            'events' => (int) $replay->events,
            'size' => (int) $replay->size,
            'duration' => $duration,
            'is_offloaded' => (bool) $replay->is_offloaded,
            'is_too_short' => (bool) $replay->is_too_short,
            'datetime' => $replay->datetime,
            'last_datetime' => $replay->last_datetime,
            'expiration_date' => $replay->expiration_date,
        ];

    }

}
