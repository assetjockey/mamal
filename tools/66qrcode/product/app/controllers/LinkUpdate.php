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
use Altum\Date;
use Altum\Title;

defined('ALTUMCODE') || die();

class LinkUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.links')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('links');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->links_limit != -1 && $total_rows > $this->user->plan_settings->links_limit) {
            redirect('links');
        }

        $link_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$link = db()->where('link_id', $link_id)->where('user_id', $this->user->user_id)->getOne('links')) {
            redirect('links');
        }

        if($link->type == 'file') {
            $qr_code_id = db()->where('link_id', $link->link_id)->where('user_id', $this->user->user_id)->getValue('qr_codes', 'qr_code_id');

            if($qr_code_id) {
                redirect('qr-code-update/' . $qr_code_id);
            }

            redirect('links');
        }

        /* Generate the link full URL base */
        $link->full_url = (new \Altum\Models\Link())->get_link_full_url($link, $this->user);

        /* Parse some details */
        $link->settings = json_decode($link->settings ?? '');
        $link->pixels_ids = json_decode($link->pixels_ids ?? '[]');

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available pixels */
        $pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);

        /* Get the amount of QR codes linked */
        $linked_qr_codes = db()->where('link_id', $link->link_id)->getValue('qr_codes', 'count(`qr_code_id`)');

        if(!empty($_POST)) {
            $_POST['url'] = !empty($_POST['url']) ? get_slug($_POST['url'], '-', false) : null;
            $_POST['location_url'] = get_url($_POST['location_url']);
            $_POST['name'] = input_clean($_POST['name'], 64);
            $_POST['domain_id'] = isset($_POST['domain_id']) && isset($domains[$_POST['domain_id']]) ? (!empty($_POST['domain_id']) ? (int) $_POST['domain_id'] : null) : null;
            $_POST['is_enabled'] = (int) isset($_POST['is_enabled']);

            /* Email reports */
            $_POST['email_reports_is_enabled'] = (int) isset($_POST['email_reports_is_enabled']);
            if(!settings()->links->email_reports_is_enabled || !$this->user->plan_settings->email_reports_is_enabled) {
                $_POST['email_reports_is_enabled'] = 0;
            }
            if($_POST['email_reports_is_enabled']) {
                $email_reports_last_datetime = $link->email_reports_last_datetime;
                if(!$link->email_reports_is_enabled || !$email_reports_last_datetime) {
                    $email_reports_last_datetime = get_date();
                }
            } else {
                $email_reports_last_datetime = null;
            }

            /* Pixels */
            $_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map(
                'intval',
                array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
                    return array_key_exists($pixel_id, $pixels);
                })
            ) : [];
            $_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

            /* Temporary URL */
            $_POST['schedule'] = (int) isset($_POST['schedule']);
            if($_POST['schedule'] && !empty($_POST['start_date']) && !empty($_POST['end_date']) && Date::validate($_POST['start_date'], 'Y-m-d H:i:s') && Date::validate($_POST['end_date'], 'Y-m-d H:i:s')) {
                $_POST['start_date'] = (new \DateTime($_POST['start_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
                $_POST['end_date'] = (new \DateTime($_POST['end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
            } else {
                $_POST['start_date'] = $_POST['end_date'] = null;
            }
            $_POST['expiration_url'] = get_url($_POST['expiration_url'] ?? null);
            $_POST['pageviews_limit'] = empty($_POST['pageviews_limit']) ? null : (int) $_POST['pageviews_limit'];

            /* Protection */
            $_POST['sensitive_content'] = (int) isset($_POST['sensitive_content']);
            $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
            $_POST['password'] = !empty($_POST['password']) ? ($_POST['password'] != $link->settings->password ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $link->settings->password) : null;

            /* Advanced */
            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

            /* Targeting */
            $targeting_types = ['continent_code', 'country_code', 'city_name', 'device_type', 'browser_language', 'rotation', 'os_name', 'browser_name'];
            $_POST['targeting_type'] = in_array($_POST['targeting_type'], array_merge(['false'], $targeting_types)) ? query_clean($_POST['targeting_type']) : 'false';

            /* App linking */
            $_POST['app_linking_is_enabled'] = (int) isset($_POST['app_linking_is_enabled']);

            $app_linking = [
                'ios_location_url' => null,
                'android_location_url' => null,
                'app' => null,
            ];

			if($_POST['app_linking_is_enabled']) {
				$supported_apps = require APP_PATH . 'includes/app_linking.php';
				$app_linking_location_url = $_POST['location_url'];

				foreach($supported_apps as $app_key => $app) {
					foreach($app['formats'] as $format => $targets) {

						if(preg_match('~' . $targets['regex'] . '~', $app_linking_location_url, $match)) {

							/* Extract and normalize hostnames */
							$user_host = parse_url($app_linking_location_url, PHP_URL_HOST);
							$format_host = parse_url('https://' . str_replace('%s', 'placeholder', $format), PHP_URL_HOST);

							/* Remove www. and m. prefixes for more flexible comparison */
							$user_host = preg_replace('/^(www\.|m\.)/', '', $user_host);
							$format_host = preg_replace('/^(www\.|m\.)/', '', $format_host);

							/* Compare the normalized hosts */
							if($user_host === $format_host) {

								if(count($match) > 1) {
									array_shift($match);
									$app_linking['ios_location_url'] = vsprintf($targets['iOS'], $match);
									$app_linking['android_location_url'] = vsprintf($targets['Android'], $match);
									$app_linking['app'] = $app_key;
								}

								break 2;
							}
						}

					}
				}
			}

            /* Cloaking */
            $_POST['cloaking_is_enabled'] = (int) isset($_POST['cloaking_is_enabled']);
            $_POST['cloaking_title'] = input_clean($_POST['cloaking_title'], 70);
            $_POST['cloaking_meta_description'] = input_clean($_POST['cloaking_meta_description'], 160);
            $_POST['cloaking_custom_js'] = mb_substr(trim($_POST['cloaking_custom_js']), 0, 10000);
            $link->settings->cloaking_favicon = \Altum\Uploads::process_upload($link->settings->cloaking_favicon, 'favicons', 'cloaking_favicon', 'cloaking_favicon_remove', settings()->links->favicon_size_limit);
            $link->settings->cloaking_opengraph = \Altum\Uploads::process_upload($link->settings->cloaking_opengraph, 'opengraphs', 'cloaking_opengraph', 'cloaking_opengraph_remove', settings()->links->opengraph_size_limit);

            /* HTTP */
            $_POST['http_status_code'] = in_array($_POST['http_status_code'], [301, 302, 307, 308]) ? (int) $_POST['http_status_code'] : 301;

            /* Query parameters forwarding */
            $_POST['forward_query_parameters_is_enabled'] = (int) isset($_POST['forward_query_parameters_is_enabled']);

            /* UTM */
            $_POST['utm_medium'] = input_clean($_POST['utm_medium'], 128);
            $_POST['utm_source'] = input_clean($_POST['utm_source'], 128);
            $_POST['utm_campaign'] = input_clean($_POST['utm_campaign'], 128);

            /* SEO */
            $_POST['is_se_visible'] = $this->user->plan_settings->search_engine_visibility_is_enabled ? (int) isset($_POST['is_se_visible']) : 0;

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['location_url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Check for duplicate url if needed */
            if(
                ($_POST['url'] && $this->user->plan_settings->custom_url_is_enabled && $_POST['url'] != $link->url)
                || ($link->domain_id != $_POST['domain_id'])
            ) {
                $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
                $is_existing_link = database()->query("SELECT `link_id` FROM `links` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;

                if(array_key_exists($_POST['url'], \Altum\Router::$routes['']) || in_array($_POST['url'], \Altum\Language::$active_languages) || file_exists(ROOT_PATH . $_POST['url'])) {
                    Alerts::add_field_error('url', l('links.error_message.blacklisted_url'));
                }

                if(in_array($_POST['url'], settings()->links->blacklisted_keywords)) {
                    Alerts::add_field_error('url', l('links.error_message.blacklisted_keyword'));
                }

                if($is_existing_link) {
                    Alerts::add_field_error('url', l('links.error_message.url_exists'));
                }

                /* Make sure the custom url meets the requirements */
                if(mb_strlen($_POST['url']) < ($this->user->plan_settings->url_minimum_characters ?? 1)) {
                    Alerts::add_field_error('url', sprintf(l('links.error_message.url_minimum_characters'), ($this->user->plan_settings->url_minimum_characters ?? 1)));
                }

                if(mb_strlen($_POST['url']) > ($this->user->plan_settings->url_maximum_characters ?? 64)) {
                    Alerts::add_field_error('url', sprintf(l('links.error_message.url_maximum_characters'), ($this->user->plan_settings->url_maximum_characters ?? 64)));
                }
            }

            $this->check_location_url('location_url', $_POST['location_url']);
            $this->check_location_url('expiration_url', $_POST['expiration_url'], true);

            $settings = [
                'app_linking_is_enabled' => $_POST['app_linking_is_enabled'],
                'app_linking' => $app_linking,
                'cloaking_is_enabled' => $_POST['cloaking_is_enabled'],
                'cloaking_title' => $_POST['cloaking_title'],
                'cloaking_custom_js' => $_POST['cloaking_custom_js'],
                'cloaking_favicon' => $link->settings->cloaking_favicon,
                'cloaking_opengraph' => $link->settings->cloaking_opengraph,
                'http_status_code' => $_POST['http_status_code'],
                'schedule' => $_POST['schedule'],
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'pageviews_limit' => $_POST['pageviews_limit'],
                'expiration_url' => $_POST['expiration_url'],
                'password' => $_POST['password'],
                'sensitive_content' => $_POST['sensitive_content'],
                'targeting_type' => $_POST['targeting_type'],
                'forward_query_parameters_is_enabled' => $_POST['forward_query_parameters_is_enabled'],

                /* UTM */
                'utm' => [
                    'source' => $_POST['utm_source'],
                    'medium' => $_POST['utm_medium'],
                    'campaign' => $_POST['utm_campaign'],
                ],

                /* SEO */
                'is_se_visible' => (int) $_POST['is_se_visible'],
            ];

            /* Process the targeting */
            foreach($targeting_types as $targeting_type) {
                ${'targeting_' . $targeting_type} = [];

                if(isset($_POST['targeting_' . $targeting_type . '_key'])) {
                    foreach($_POST['targeting_' . $targeting_type . '_key'] as $key => $value) {
                        if(empty(trim($_POST['targeting_' . $targeting_type . '_value'][$key]))) continue;

                        $this->check_location_url('targeting_' . $targeting_type . '_value[' . $key . ']', $_POST['targeting_' . $targeting_type . '_value'][$key]);

                        ${'targeting_' . $targeting_type}[] = [
                            'key' => trim(query_clean($value)),
                            'value' => get_url($_POST['targeting_' . $targeting_type . '_value'][$key]),
                        ];
                    }

                    $settings['targeting_' . $targeting_type] = ${'targeting_' . $targeting_type};
                }
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $settings = json_encode($settings);

                if(!$_POST['url']) {
                    $is_existing_link = true;

                    /* Generate random url if not specified */
                    while($is_existing_link) {
                        $_POST['url'] = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));

                        $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
                        $is_existing_link = database()->query("SELECT `link_id` FROM `links` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;
                    }
                }

                /* Database query */
                db()->where('link_id', $link->link_id)->update('links', [
                    'domain_id' => $_POST['domain_id'],
                    'project_id' => $_POST['project_id'],
                    'pixels_ids' => $_POST['pixels_ids'],
                    'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
                    'email_reports_last_datetime' => $email_reports_last_datetime,
                    'url' => $_POST['url'],
                    'location_url' => $_POST['location_url'],
                    'name' => $_POST['name'],
                    'settings' => $settings,
                    'is_enabled' => $_POST['is_enabled'],
                    'last_datetime' => get_date(),
                ]);

                /* Clear the cache */
                cache()->deleteItemsByTag('link_id=' . $link_id);
                cache()->deleteItem('links?user_id=' . $link->user_id);

                /* Send webhook notification if needed */
                if(settings()->webhooks->link_update) {
                    fire_and_forget('post', settings()->webhooks->link_update, [
                        'user_id' => $this->user->user_id,
                        'link_id' => $link_id,
                        'domain_id' => $_POST['domain_id'],
                        'url' => $_POST['url'],
                        'location_url' => $_POST['location_url'],
                        'name' => $_POST['name'],
                        'full_url' => $_POST['domain_id'] ? $domains[$_POST['domain_id']]->url . $_POST['url'] : SITE_URL . $_POST['url'],
                        'datetime' => get_date(),
                    ], signature: true);
                }

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['url'] . '</strong>'));

                redirect('link-update/' . $link->link_id);
            }

        }

        /* Set a custom title */
        Title::set(sprintf(l('link_update.title'), $link->url));

        /* Prepare the view */
        $data = [
            'linked_qr_codes' => $linked_qr_codes,
            'pixels' => $pixels,
            'domains' => $domains,
            'projects' => $projects,
            'link' => $link
        ];

        $view = new \Altum\View('link-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

        /* Clear out pending alerts */
        Alerts::clear_field_errors();
    }

    /* Function to bundle together all the checks of an url */
    private function check_location_url($key, $url, $can_be_empty = false) {

        if(empty(trim($url)) && $can_be_empty) {
            return;
        }

        if(empty(trim($url))) {
            Alerts::add_field_error($key, l('global.error_message.empty_fields'));
        }

        $url_details = parse_url($url);

        if(!isset($url_details['scheme'])) {
            Alerts::add_field_error($key, l('links.error_message.invalid_location_url'));
        }

        /* Make sure the domain is not blacklisted */
        $domain = get_domain_from_url($url);

        if($domain && in_array($domain, settings()->links->blacklisted_domains)) {
            Alerts::add_field_error($key, l('links.error_message.blacklisted_domain'));
        }

        /* Check the url with google safe browsing to make sure it is a safe website */
        if(settings()->links->google_safe_browsing_is_enabled) {
            if(google_safe_browsing_check($url, settings()->links->google_safe_browsing_api_key)) {
                Alerts::add_field_error($key, l('links.error_message.blacklisted_location_url'));
            }
        }
    }

}
