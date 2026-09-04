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

use xPaw\SourceQuery\SourceQuery;

defined('ALTUMCODE') || die();

class Index extends Controller {

    public function index() {

//        require_once APP_PATH . 'helpers/GameQ/Autoloader.php';
//
//        $GameQ = new \GameQ\GameQ();
//        $GameQ->addServer([
//            'type' => 'teamspeak3',
//            'host' => 'ts.udp.ro:9987',
//            'options' => [
//                'query_port' => 10011,
//            ],
//        ]);
//        $results = $GameQ->process();
//
//        $server = reset($results);
//
//        echo '<pre>';
//        echo 'Online: ' . $server['gq_numplayers'] . '/'. $server['gq_maxplayers'];
//        var_dump($results);
//
//        die();


//        $query = new \xPaw\SourceQuery\SourceQuery();
//
//        try
//        {
//            $query->Connect('193.25.252.15', 27016, 5, SourceQuery::SOURCE );
//
//            echo '<pre>';
//            var_dump($query->GetInfo());
//            var_dump($query->GetPlayers());
//            var_dump($query->GetRules());
//            die();
//        }
//        catch(\Exception $exception )
//        {
//            echo $exception->getMessage( );
//        }
//        finally
//        {
//            $query->Disconnect( );
//        }
//die();

//
//        try {
//            $query = new \xPaw\MinecraftPing('minewave.net');
//
//            echo '<pre>';
//            $result = $query->Query();
//
//$description = minecraft_description_to_plain_and_html($result['description']);
//
//dd($description);
//        } catch (\xPaw\MinecraftPingException $exception) {
//            dd($exception->getMessage());
//        } finally {
//            if($query) $query->close();
//        }



        /* Custom index redirect if set */
        if(!empty(settings()->main->index_url)) {
            header('Location: ' . settings()->main->index_url); die();
        }

        /* Opengraph image */
        if(settings()->main->opengraph) {
            \Altum\Meta::set_social_image(\Altum\Uploads::get_full_url('opengraph') . settings()->main->opengraph);
        }

        /* Canonical */
        \Altum\Meta::set_canonical_url(url());

        /* Fix on /index to / */
        \Altum\Router::$original_request = '';

        /* Check if the cache exists */
        $cache_instance = cache()->getItem('index_stats');

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            $total_monitors = database()->query("SELECT MAX(`monitor_id`) AS `total` FROM `monitors`")->fetch_object()->total ?? 0;
            $total_status_pages = database()->query("SELECT MAX(`status_page_id`) AS `total` FROM `status_pages`")->fetch_object()->total ?? 0;
            $total_monitors_logs = database()->query("SELECT MAX(`monitor_log_id`) AS `total` FROM `monitors_logs`")->fetch_object()->total ?? 0;

            $stats = [
                'total_monitors' => $total_monitors,
                'total_status_pages' => $total_status_pages,
                'total_monitors_logs' => $total_monitors_logs,
            ];

            /* Save to cache */
            cache()->save($cache_instance->set($stats)->expiresAfter(3600));

        } else {

            /* Get cache */
            $stats = $cache_instance->get();
            extract($stats);

        }

        /* Get some stats */

        /* Plans View */
        $view = new \Altum\View('partials/plans', (array) $this);
        $this->add_view_content('plans', $view->run());



        if(settings()->main->display_index_latest_blog_posts) {
            $language = \Altum\Language::$name;

            /* Blog posts query */
            $blog_posts_result_query = "
                SELECT * 
                FROM `blog_posts`
                WHERE (`language` = '{$language}' OR `language` IS NULL) AND `is_published` = 1 
                ORDER BY `blog_post_id` DESC
                LIMIT 3
            ";

            $blog_posts = \Altum\Cache::cache_function_result('blog_posts?hash=' . md5($blog_posts_result_query), 'blog_posts', function() use ($blog_posts_result_query) {
                $blog_posts_result = database()->query($blog_posts_result_query);

                /* Iterate over the blog posts */
                $blog_posts = [];

                while($row = $blog_posts_result->fetch_object()) {
                    /* Transform content if needed */
                    $row->content = json_decode($row->content) ? convert_editorjs_json_to_html($row->content) : output_blog_post_content($row->content);

                    $blog_posts[] = $row;
                }

                return $blog_posts;
            });
        }

        $tools = require APP_PATH . 'includes/tools.php';
        $enabled_tools = count(array_filter((array) settings()->tools->available_tools));

        /* Main View */
        $data = [
            'total_monitors' => $total_monitors,
            'total_status_pages' => $total_status_pages,
            'total_monitors_logs' => $total_monitors_logs,
            'tools' => $tools,
            'enabled_tools' => $enabled_tools,
            'blog_posts' => $blog_posts ?? [],
        ];

        $view = new \Altum\View('index/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
