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

use Altum\Models\AiQrCode;
use Altum\Response;
use Altum\Traits\Apiable;
use Altum\Uploads;
use Unirest\Request;

defined('ALTUMCODE') || die();

class ApiAiQrCodes extends Controller {
    use Apiable;

    public function index() {

        if(!settings()->codes->ai_qr_codes_is_enabled) {
            throw_404();
        }

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':


                /* Detect if we only need an object, or the whole list */
                if(isset($this->params[0])) {
                    $this->get();
                } else {
                    $this->get_all();
                }

                break;

            case 'POST':

                /* Detect what method to use */
                if(isset($this->params[0])) {

                    $this->patch();
                } else {
                    $this->post();
                }

                break;

            case 'DELETE':


                $this->delete();

                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['project_id'], ['name'], ['ai_qr_code_id', 'last_datetime', 'name', 'datetime'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->ai_qr_codes_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `ai_qr_codes` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/ai-qr-codes?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `ai_qr_codes`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->ai_qr_code_id,
                'user_id' => (int) $row->user_id,
                'link_id' => (int) $row->link_id,
                'project_id' => (int) $row->project_id,
                'name' => $row->name,
                'content' => $row->content,
                'prompt' => $row->prompt,
                'ai_qr_code' => \Altum\Uploads::get_full_url('ai_qr_codes') . $row->ai_qr_code,
                'settings' => json_decode($row->settings ?? ''),
                'embedded_data' => $row->embedded_data,
                'last_datetime' => $row->last_datetime,
                'datetime' => $row->datetime,
            ];

            $data[] = $row;
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

        $ai_qr_code_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $ai_qr_code = db()->where('ai_qr_code_id', $ai_qr_code_id)->where('user_id', $this->user->user_id)->getOne('ai_qr_codes');

        /* We haven't found the resource */
        if(!$ai_qr_code) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $ai_qr_code->ai_qr_code_id,
            'user_id' => (int) $ai_qr_code->user_id,
            'link_id' => (int) $ai_qr_code->link_id,
            'project_id' => (int) $ai_qr_code->project_id,
            'name' => $ai_qr_code->name,
            'content' => $ai_qr_code->content,
            'prompt' => $ai_qr_code->prompt,
            'ai_qr_code' => \Altum\Uploads::get_full_url('ai_qr_codes') . $ai_qr_code->ai_qr_code,
            'settings' => json_decode($ai_qr_code->settings),
            'embedded_data' => $ai_qr_code->embedded_data,
            'last_datetime' => $ai_qr_code->last_datetime,
            'datetime' => $ai_qr_code->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for the plan limit */
        $ai_qr_codes_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`qrcode_ai_qr_codes_current_month`');

        if($this->user->plan_settings->ai_qr_codes_per_month_limit != -1 && $ai_qr_codes_current_month >= $this->user->plan_settings->ai_qr_codes_per_month_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Existing projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        $settings = [];

        $_POST['name'] = trim($_POST['name'] ?? null);
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
        $_POST['content'] = input_clean($_POST['content'], 512);
        $_POST['prompt'] = input_clean($_POST['prompt'], 512);
        $_POST['url_dynamic'] = (int) isset($_POST['url_dynamic']);
        $_POST['url_dynamic_existing_link'] = $_POST['url_dynamic'] ? (int) isset($_POST['url_dynamic_existing_link']) : 0;
        $settings['url_dynamic_existing_link'] = $_POST['url_dynamic_existing_link'];

        if($_POST['url_dynamic'] && $_POST['url_dynamic_existing_link']) {
            $link = db()->where('link_id', $_POST['link_id'] ?? null)->where('user_id', $this->user->user_id)->getOne('links', ['link_id', 'domain_id', 'url']);

            if(!$link) {
                $this->response_error(l('global.error_message.empty_fields'), 401);
            }

            $_POST['link_id'] = $link->link_id;
            $_POST['content'] = (new \Altum\Models\Link())->get_link_full_url($link, $this->user);
        } else {
            if($_POST['url_dynamic']) {
                $_POST['link_id'] = null;
                $_POST['content'] = get_url($_POST['content']);

                if(empty(trim($_POST['content']))) {
                    $this->response_error(l('global.error_message.empty_fields'), 401);
                }

                $url_details = parse_url($_POST['content']);

                if(!isset($url_details['scheme'])) {
                    $this->response_error(l('links.error_message.invalid_location_url'), 401);
                }

                $domain = get_domain_from_url($_POST['content']);

                if($domain && in_array($domain, settings()->links->blacklisted_domains)) {
                    $this->response_error(l('links.error_message.blacklisted_domain'), 401);
                }

                if(settings()->links->google_safe_browsing_is_enabled) {
                    if(google_safe_browsing_check($_POST['content'], settings()->links->google_safe_browsing_api_key)) {
                        $this->response_error(l('links.error_message.blacklisted_location_url'), 401);
                    }
                }
            }
        }

        /* Check for any errors */
        $required_fields = ['name', 'content', 'prompt'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        if($_POST['url_dynamic'] && !$_POST['url_dynamic_existing_link']) {
            $total_links_rows = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

            if($this->user->plan_settings->links_limit != -1 && $total_links_rows >= $this->user->plan_settings->links_limit) {
                $this->response_error(l('global.info_message.plan_feature_limit'), 401);
            }

            $is_existing_link = true;
            $url = null;

            while($is_existing_link) {
                $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
                $is_existing_link = database()->query("SELECT `link_id` FROM `links` WHERE `url` = '{$url}' AND `domain_id` IS NULL")->num_rows;
            }

            $link_settings = json_encode([
                'app_linking_is_enabled' => 0,
                'app_linking' => [
                    'ios_location_url' => null,
                    'android_location_url' => null,
                    'app' => null,
                ],
                'cloaking_is_enabled' => 0,
                'cloaking_title' => '',
                'cloaking_meta_description' => '',
                'cloaking_custom_js' => '',
                'cloaking_favicon' => null,
                'cloaking_opengraph' => null,
                'http_status_code' => 301,
                'schedule' => 0,
                'start_date' => null,
                'end_date' => null,
                'pageviews_limit' => null,
                'expiration_url' => null,
                'password' => null,
                'sensitive_content' => 0,
                'targeting_type' => 'false',
                'forward_query_parameters_is_enabled' => 0,
                'utm' => [
                    'source' => '',
                    'medium' => '',
                    'campaign' => '',
                ],
                'is_se_visible' => 0,
            ]);

            $_POST['link_id'] = db()->insert('links', [
                'user_id' => $this->user->user_id,
                'domain_id' => null,
                'project_id' => $_POST['project_id'],
                'pixels_ids' => json_encode([]),
                'url' => $url,
                'location_url' => $_POST['content'],
                'name' => $_POST['name'],
                'settings' => $link_settings,
                'is_enabled' => 1,
                'datetime' => get_date(),
            ]);

            cache()->deleteItem('links?user_id=' . $this->user->user_id);
            cache()->deleteItem('links_total?user_id=' . $this->user->user_id);
            cache()->deleteItem('links_dashboard?user_id=' . $this->user->user_id);

            $_POST['content'] = SITE_URL . $url . '/';
            $settings['url_dynamic_existing_link'] = 1;
        }

        /* Generate the QR Code */
        $request_data = array_merge([
            'api_key' => $this->user->api_key,
            'content' => $_POST['content'],
            'prompt' => $_POST['prompt'],
        ], $settings);

        $request_data = json_encode($request_data);

        try {
            $response = Request::post(url('ai-qr-code-generator'), [], Request\Body::multipart(['json' => $request_data]));
        } catch (\Exception $exception) {
            $this->response_error($exception->getMessage(), 401);
        }

        if($response->body->status == 'error') {
            $this->response_error($response->body->message, 401);
        }

        /* Fake uploaded qr code */
        $_FILES['ai_qr_code'] = [
            'name' => 'altum.png',
            'tmp_name' => Uploads::get_full_path('ai_qr_codes/temp') . $response->body->details->ai_qr_code,
            'error' => null,
            'size' => 0,
        ];

        $ai_qr_code_image = \Altum\Uploads::process_upload_fake('ai_qr_codes', 'ai_qr_code', 'json');

        /* Embedded data */
        $_POST['embedded_data'] = input_clean($response->body->details->embedded_data, 10000);

        $settings = json_encode($settings);

        /* Database query */
        $ai_qr_code_id = db()->insert('ai_qr_codes', [
            'user_id' => $this->user->user_id,
            'link_id' => $_POST['link_id'] ?? null,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'prompt' => $_POST['prompt'],
            'ai_qr_code' => $ai_qr_code_image,
            'settings' => $settings,
            'embedded_data' => $_POST['embedded_data'],
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('ai_qr_codes_total?user_id=' . $this->user->user_id);
        cache()->deleteItem('ai_qr_codes_dashboard?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $ai_qr_code_id,
            'user_id' => (int) $this->user->user_id,
            'link_id' => (int) ($_POST['link_id'] ?? null),
            'project_id' => (int) $_POST['project_id'],
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'prompt' => $_POST['prompt'],
            'ai_qr_code' => \Altum\Uploads::get_full_url('ai_qr_codes') . $ai_qr_code_image,
            'settings' => json_decode($settings),
            'embedded_data' => $_POST['embedded_data'],
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $ai_qr_code_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $ai_qr_code = db()->where('ai_qr_code_id', $ai_qr_code_id)->where('user_id', $this->user->user_id)->getOne('ai_qr_codes');

        /* We haven't found the resource */
        if(!$ai_qr_code) {
            $this->return_404();
        }
        $ai_qr_code->settings = json_decode($ai_qr_code->settings ?? '');

        /* Existing projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        $settings = [];

        $should_regenerate = isset($_POST['prompt']) || isset($_POST['content']) || isset($_POST['link_id']) || isset($_POST['url_dynamic']) || isset($_POST['url_dynamic_existing_link']);

        $_POST['name'] = trim($_POST['name'] ?? $ai_qr_code->name);
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : $ai_qr_code->project_id;
        $_POST['content'] = input_clean($_POST['content'] ?? $ai_qr_code->content, 512);
        $_POST['prompt'] = input_clean($_POST['prompt'] ?? $ai_qr_code->prompt, 512);
        $_POST['url_dynamic'] = isset($_POST['url_dynamic']) ? (int) (bool) $_POST['url_dynamic'] : (int) $ai_qr_code->link_id;
        $_POST['url_dynamic_existing_link'] = $_POST['url_dynamic'] ? (isset($_POST['url_dynamic_existing_link']) ? (int) (bool) $_POST['url_dynamic_existing_link'] : (int) ($ai_qr_code->link_id && (!isset($ai_qr_code->settings->url_dynamic_existing_link) || $ai_qr_code->settings->url_dynamic_existing_link))) : 0;
        $settings['url_dynamic_existing_link'] = $_POST['url_dynamic_existing_link'];

        if($_POST['url_dynamic'] && $_POST['url_dynamic_existing_link']) {
            $link_id = $_POST['link_id'] ?? $ai_qr_code->link_id;
            $link = db()->where('link_id', $link_id)->where('user_id', $this->user->user_id)->getOne('links', ['link_id', 'domain_id', 'url']);

            if(!$link) {
                $this->response_error(l('global.error_message.empty_fields'), 401);
            }

            $_POST['link_id'] = $link->link_id;
            $_POST['content'] = (new \Altum\Models\Link())->get_link_full_url($link, $this->user);
        } else {
            $_POST['link_id'] = null;

            if($_POST['url_dynamic']) {
                $_POST['content'] = get_url($_POST['content']);

                if(empty(trim($_POST['content']))) {
                    $this->response_error(l('global.error_message.empty_fields'), 401);
                }

                $url_details = parse_url($_POST['content']);

                if(!isset($url_details['scheme'])) {
                    $this->response_error(l('links.error_message.invalid_location_url'), 401);
                }

                $domain = get_domain_from_url($_POST['content']);

                if($domain && in_array($domain, settings()->links->blacklisted_domains)) {
                    $this->response_error(l('links.error_message.blacklisted_domain'), 401);
                }

                if(settings()->links->google_safe_browsing_is_enabled) {
                    if(google_safe_browsing_check($_POST['content'], settings()->links->google_safe_browsing_api_key)) {
                        $this->response_error(l('links.error_message.blacklisted_location_url'), 401);
                    }
                }
            }
        }

        $ai_qr_code_image = $ai_qr_code->ai_qr_code;
        $ai_qr_code_embedded = $ai_qr_code->embedded_data;

        if($_POST['url_dynamic'] && !$_POST['url_dynamic_existing_link']) {
            if($ai_qr_code->link_id && isset($ai_qr_code->settings->url_dynamic_existing_link) && !$ai_qr_code->settings->url_dynamic_existing_link) {
                if($link = db()->where('link_id', $ai_qr_code->link_id)->where('user_id', $this->user->user_id)->getOne('links', ['link_id', 'url'])) {
                    db()->where('link_id', $ai_qr_code->link_id)->where('user_id', $this->user->user_id)->update('links', [
                        'project_id' => $_POST['project_id'],
                        'location_url' => $_POST['content'],
                        'name' => $_POST['name'],
                        'last_datetime' => get_date(),
                    ]);

                    cache()->deleteItemsByTag('link_id=' . $ai_qr_code->link_id);
                    cache()->deleteItem('links?user_id=' . $this->user->user_id);
                    cache()->deleteItem('links_total?user_id=' . $this->user->user_id);
                    cache()->deleteItem('links_dashboard?user_id=' . $this->user->user_id);

                    $_POST['link_id'] = $link->link_id;
                    $_POST['content'] = SITE_URL . $link->url . '/';
                }
            } else {
                $total_links_rows = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

                if($this->user->plan_settings->links_limit != -1 && $total_links_rows >= $this->user->plan_settings->links_limit) {
                    $this->response_error(l('global.info_message.plan_feature_limit'), 401);
                }

                $is_existing_link = true;
                $url = null;

                while($is_existing_link) {
                    $url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));
                    $is_existing_link = database()->query("SELECT `link_id` FROM `links` WHERE `url` = '{$url}' AND `domain_id` IS NULL")->num_rows;
                }

                $link_settings = json_encode([
                    'app_linking_is_enabled' => 0,
                    'app_linking' => [
                        'ios_location_url' => null,
                        'android_location_url' => null,
                        'app' => null,
                    ],
                    'cloaking_is_enabled' => 0,
                    'cloaking_title' => '',
                    'cloaking_meta_description' => '',
                    'cloaking_custom_js' => '',
                    'cloaking_favicon' => null,
                    'cloaking_opengraph' => null,
                    'http_status_code' => 301,
                    'schedule' => 0,
                    'start_date' => null,
                    'end_date' => null,
                    'pageviews_limit' => null,
                    'expiration_url' => null,
                    'password' => null,
                    'sensitive_content' => 0,
                    'targeting_type' => 'false',
                    'forward_query_parameters_is_enabled' => 0,
                    'utm' => [
                        'source' => '',
                        'medium' => '',
                        'campaign' => '',
                    ],
                    'is_se_visible' => 0,
                ]);

                $_POST['link_id'] = db()->insert('links', [
                    'user_id' => $this->user->user_id,
                    'domain_id' => null,
                    'project_id' => $_POST['project_id'],
                    'pixels_ids' => json_encode([]),
                    'url' => $url,
                    'location_url' => $_POST['content'],
                    'name' => $_POST['name'],
                    'settings' => $link_settings,
                    'is_enabled' => 1,
                    'datetime' => get_date(),
                ]);

                cache()->deleteItem('links?user_id=' . $this->user->user_id);
                cache()->deleteItem('links_total?user_id=' . $this->user->user_id);
                cache()->deleteItem('links_dashboard?user_id=' . $this->user->user_id);

                $_POST['content'] = SITE_URL . $url . '/';
            }

            $settings['url_dynamic_existing_link'] = 1;
            $should_regenerate = true;
        }

        /* Generate the QR Code */
        if($should_regenerate) {
            $request_data = array_merge([
                'api_key' => $this->user->api_key,
                'content' => $_POST['content'],
                'prompt' => $_POST['prompt'],
            ], $settings);

            $request_data = json_encode($request_data);

            try {
                $response = Request::post(url('ai-qr-code-generator'), [], Request\Body::multipart(['json' => $request_data]));
            } catch (\Exception $exception) {
                $this->response_error($exception->getMessage(), 401);
            }

            if($response->body->status == 'error') {
                $this->response_error($response->body->message, 401);
            }

            /* Fake uploaded synthesis */
            $_FILES['ai_qr_code'] = [
                'name' => 'altum.png',
                'tmp_name' => Uploads::get_full_path('ai_qr_codes/temp') . $response->body->details->ai_qr_code,
                'error' => null,
                'size' => 0,
            ];

            /* Delete old one */
            Uploads::delete_uploaded_file($ai_qr_code->ai_qr_code, 'ai_qr_codes');

            $ai_qr_code_image = \Altum\Uploads::process_upload_fake('ai_qr_codes', 'ai_qr_code', 'json');
            $ai_qr_code_embedded = $response->body->details->embedded_data;
        }

        $settings = json_encode($settings);

        /* Database query */
        db()->where('ai_qr_code_id', $ai_qr_code->ai_qr_code_id)->update('ai_qr_codes', [
            'link_id' => $_POST['link_id'] ?? null,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'prompt' => $_POST['prompt'],
            'ai_qr_code' => $ai_qr_code_image,
            'settings' => $settings,
            'embedded_data' => $ai_qr_code_embedded,
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('ai_qr_codes_dashboard?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $ai_qr_code->ai_qr_code_id,
            'user_id' => (int) $ai_qr_code->user_id,
            'link_id' => (int) ($_POST['link_id'] ?? null),
            'project_id' => (int) $_POST['project_id'],
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'prompt' => $_POST['prompt'],
            'ai_qr_code' => \Altum\Uploads::get_full_url('ai_qr_codes') . $ai_qr_code_image,
            'settings' => json_decode($settings),
            'embedded_data' => $ai_qr_code_embedded,
            'last_datetime' => get_date(),
            'datetime' => $ai_qr_code->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $ai_qr_code_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $ai_qr_code = db()->where('ai_qr_code_id', $ai_qr_code_id)->where('user_id', $this->user->user_id)->getOne('ai_qr_codes');

        /* We haven't found the resource */
        if(!$ai_qr_code) {
            $this->return_404();
        }

        (new AiQrCode())->delete($ai_qr_code->ai_qr_code_id);

        http_response_code(200);
        die();

    }

}
