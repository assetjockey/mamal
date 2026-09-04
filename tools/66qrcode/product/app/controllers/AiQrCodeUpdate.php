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

use Altum\Alerts;
use Altum\Uploads;
use Unirest\Request;

defined('ALTUMCODE') || die();

class AiQrCodeUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!settings()->codes->ai_qr_codes_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.ai_qr_codes')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('ai-qr-codes');
        }

        $ai_qr_code_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$ai_qr_code = db()->where('ai_qr_code_id', $ai_qr_code_id)->where('user_id', $this->user->user_id)->getOne('ai_qr_codes')) {
            redirect('ai-qr-codes');
        }
        $ai_qr_code->settings = json_decode($ai_qr_code->settings ?? '');

        /* Existing projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Existing links */
        $links = (new \Altum\Models\Link())->get_full_links_by_user_id($this->user->user_id);
        foreach($links as $link_id => $link) {
            if($link->type == 'file') unset($links[$link_id]);
        }

        if(!empty($_POST)) {
            $required_fields = ['name', 'content', 'prompt', 'ai_qr_code'];
            $settings = [];

            $_POST['name'] = input_clean($_POST['name'], 64);
            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
            $_POST['embedded_data'] = input_clean($_POST['embedded_data'], 10000);
            $_POST['content'] = input_clean($_POST['content'], 512);
            $_POST['prompt'] = input_clean($_POST['prompt'], 512);
            $_POST['ai_qr_code'] = input_clean($_POST['ai_qr_code'], 64);
            $_POST['url_dynamic'] = (int) isset($_POST['url_dynamic']);
            $_POST['url_dynamic_existing_link'] = $_POST['url_dynamic'] ? (int) isset($_POST['url_dynamic_existing_link']) : 0;
            $settings['url_dynamic_existing_link'] = $_POST['url_dynamic_existing_link'];

            if($_POST['url_dynamic'] && $_POST['url_dynamic_existing_link']) {
                $link = db()->where('link_id', $_POST['link_id'] ?? null)->where('user_id', $this->user->user_id)->where('type', 'file', '<>')->getOne('links', ['link_id', 'domain_id', 'url']);

                if(!$link) {
                    unset($_POST['link_id']);
                    Alerts::add_field_error('link_id', l('global.error_message.empty_field'));
                }

                else {
                    $_POST['link_id'] = $link->link_id;
                    $_POST['content'] = (new \Altum\Models\Link())->get_link_full_url($link, $this->user);
                }
            } else {
                $_POST['link_id'] = null;

                if($_POST['url_dynamic']) {
                    $_POST['content'] = get_url($_POST['content']);

                    if(empty(trim($_POST['content']))) {
                        Alerts::add_field_error('content', l('global.error_message.empty_fields'));
                    }

                    $url_details = parse_url($_POST['content']);

                    if(!isset($url_details['scheme'])) {
                        Alerts::add_field_error('content', l('links.error_message.invalid_location_url'));
                    }

                    $domain = get_domain_from_url($_POST['content']);

                    if($domain && in_array($domain, settings()->links->blacklisted_domains)) {
                        Alerts::add_field_error('content', l('links.error_message.blacklisted_domain'));
                    }

                    if(settings()->links->google_safe_browsing_is_enabled) {
                        if(google_safe_browsing_check($_POST['content'], settings()->links->google_safe_browsing_api_key)) {
                            Alerts::add_field_error('content', l('links.error_message.blacklisted_location_url'));
                        }
                    }
                }
            }

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            $ai_qr_code_image = $ai_qr_code->ai_qr_code;

            if(!Alerts::has_field_errors() && !Alerts::has_errors() && $_POST['url_dynamic'] && !$_POST['url_dynamic_existing_link']) {
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
                        Alerts::add_field_error('content', l('global.info_message.plan_feature_limit'));
                    } else {
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
                }

                if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                    $settings['url_dynamic_existing_link'] = 1;

                    $request_data = json_encode([
                        'api_key' => $this->user->api_key,
                        'content' => $_POST['content'],
                        'prompt' => $_POST['prompt'],
                    ]);

                    try {
                        $response = Request::post(url('ai-qr-code-generator'), [], Request\Body::multipart(['json' => $request_data]));
                    } catch (\Exception $exception) {
                        Alerts::add_error($exception->getMessage());
                    }

                    if(($response->body->status ?? null) == 'error') {
                        Alerts::add_error($response->body->message);
                    }

                    $_POST['ai_qr_code'] = $response->body->details->ai_qr_code ?? $_POST['ai_qr_code'];
                    $_POST['embedded_data'] = $response->body->details->embedded_data ?? $_POST['content'];
                }
            }

            if($_POST['ai_qr_code'] && $ai_qr_code->ai_qr_code != $_POST['ai_qr_code']) {
                /* Fake uploaded synthesis */
                $_FILES['ai_qr_code'] = [
                    'name' => 'altum.png',
                    'tmp_name' => Uploads::get_full_path('ai_qr_codes/temp') . $_POST['ai_qr_code'],
                    'error' => null,
                    'size' => 0,
                ];

                /* Delete old one */
                Uploads::delete_uploaded_file($ai_qr_code->ai_qr_code, 'ai_qr_codes');

                $ai_qr_code_image = \Altum\Uploads::process_upload_fake('ai_qr_codes', 'ai_qr_code');
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $settings = json_encode($settings);

                /* Database query */
                db()->where('ai_qr_code_id', $ai_qr_code->ai_qr_code_id)->update('ai_qr_codes', [
                    'project_id' => $_POST['project_id'],
                    'link_id' => $_POST['link_id'] ?? null,
                    'name' => $_POST['name'],
                    'content' => $_POST['content'],
                    'prompt' => $_POST['prompt'],
                    'ai_qr_code' => $ai_qr_code_image,
                    'settings' => $settings,
                    'embedded_data' => $_POST['embedded_data'],
                    'last_datetime' => get_date(),
                ]);

                /* Clear the cache */
                cache()->deleteItem('ai_qr_codes_dashboard?user_id=' . $this->user->user_id);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('ai-qr-code-update/' . $ai_qr_code_id);
            }
        }

        /* Prepare the view */
        $data = [
            'ai_qr_code' => $ai_qr_code,
            'projects' => $projects,
            'links' => $links,
        ];

        $view = new \Altum\View('ai-qr-code-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
