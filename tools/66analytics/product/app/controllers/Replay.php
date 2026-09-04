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

class Replay extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!$this->website || !settings()->analytics->sessions_replays_is_enabled || ($this->website && $this->website->tracking_type == 'lightweight')) {
            redirect('websites');
        }

        $session_id = (isset($this->params[0])) ? (int) $this->params[0] : 0;

        /* Get the Visitor basic data and make sure it exists */
        $visitor = database()->query("
            SELECT
                `visitors_sessions`.`session_id`,
                `websites_visitors`.`visitor_uuid_binary`,
                `websites_visitors`.`custom_parameters`,
                `websites_visitors`.`country_code`,
                `websites_visitors`.`city_name`,
                `websites_visitors`.`visitor_id`,
                `websites_visitors`.`date`
            FROM
                `visitors_sessions`
            LEFT JOIN   
                `websites_visitors` ON `visitors_sessions`.`visitor_id` = `websites_visitors`.`visitor_id`
            WHERE
                `visitors_sessions`.`session_id` = {$session_id}
                AND `visitors_sessions`.`website_id` = {$this->website->website_id}
        ")->fetch_object() ?? null;

        if(!$visitor) redirect('replays');

        /* Get the replay */
        if(!$replay = db()->where('session_id', $visitor->session_id)->getOne('sessions_replays')) {
            redirect('replays');
        }

        /* Events Modal */
        $view = new \Altum\View('replay/replay_events_modal', (array) $this);
        \Altum\Event::add_content($view->run(), 'modals');

        /* Prepare the view */
        $data = [
            'visitor'   => $visitor,
            'replay'    => $replay,
        ];

        $view = new \Altum\View('replay/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function read() {
        \Altum\Authentication::guard();

        set_time_limit(0);

        session_write_close();

        $session_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        $replay = db()->where('session_id', $session_id)->where('website_id', $this->website->website_id)->getOne('sessions_replays');

        if(!$replay) {
            die();
        }

        /* We will collect only type 4 events for the modal (page loads) */
        $pageviews_events = [];

        /* Start JSON streaming */
        header('Content-Type: application/json; charset=utf-8');
        echo '{"rows":[';

        $first = true;

        /* Helper for outputting a single event cleanly */
        $output_event = function(array $row) use (&$first) {

            if(!$first) {
                echo ',';
            } else {
                $first = false;
            }

            echo json_encode($row);
        };

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
                echo '],"replay_events_html":""}';
                die();
            }

            foreach($file_data as $entry) {

                $row = [
                    'type' => (int) $entry->type,
                    'data' => json_decode(gzdecode($entry->data)),
                    'timestamp' => (int) $entry->timestamp,
                ];

                $output_event($row);

                if($row['type'] === 4) {
                    /* Get UTC datetime */
                    $row['datetime'] = (new \DateTime())->setTimestamp((int) ($row['timestamp'] / 1000))->format('Y-m-d H:i:s');

                    /* More processing */
                    $row['path'] = parse_url($row['data']->href, PHP_URL_PATH);

                    /* Time */
                    $row['time'] = \Altum\Date::get($row['datetime'], 3);

                    $pageviews_events[] = $row;
                }
            }

        }

        /* Local */
        else {

            \Altum\Cache::store_initialize();

            $index_item = cache('store_adapter')->getItem('session_replay_keys_' . $session_id);
            $session_replay_keys = $index_item->get() ?: [];

            if(empty($session_replay_keys)) {
                echo '],"replay_events_html":""}';
                return;
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

                    $output_event($row);

                    if($row['type'] === 4) {
                        /* Get UTC datetime */
                        $row['datetime'] = (new \DateTime())->setTimestamp((int) ($row['timestamp'] / 1000))->format('Y-m-d H:i:s');

                        /* More processing */
                        $row['path'] = parse_url($row['data']->href, PHP_URL_PATH);

                        /* Time */
                        $row['time'] = \Altum\Date::get($row['datetime'], 3);

                        $pageviews_events[] = $row;
                    }

                }
            }
        }

        echo '], "pageviews":' . json_encode($pageviews_events) . '}';
        die();
    }

}
