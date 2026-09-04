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

use Altum\Date;
use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiLinks extends Controller {
	use Apiable;

	public function index() {

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
        $filters = (new \Altum\Filters(['is_enabled', 'project_id', 'domain_id', 'links_ids'], ['url', 'location_url', 'name'], ['link_id', 'last_datetime', 'datetime', 'url', 'location_url', 'pageviews'], [], ['links_ids' => 'json_contains'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->links_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
		$filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
		$filters->process();

		/* Prepare the paginator */
		$total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `links` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
		$paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/links?' . $filters->get_get() . '&page={{PAGE}}')));

		/* Get the data */
		$data = [];
		$data_result = database()->query("
            SELECT
                *
            FROM
                `links`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
		while($row = $data_result->fetch_object()) {

			/* Prepare the data */
			$row = [
				'id' => (int) $row->link_id,
				'user_id' => (int) $row->user_id,
				'project_id' => (int) $row->project_id,
				'domain_id' => (int) $row->domain_id,
				'pixels_ids' => json_decode($row->pixels_ids ?? '[]'),
				'email_reports_is_enabled' => (int) $row->email_reports_is_enabled,
				'email_reports_last_datetime' => $row->email_reports_last_datetime,
				'url' => $row->url,
                'location_url' => $row->location_url,
                'type' => $row->type,
                'file_name' => $row->file_name,
                'file_size' => (int) $row->file_size,
                'file_mime_type' => $row->file_mime_type,
                'name' => $row->name,
				'settings' => json_decode($row->settings ?? ''),
				'pageviews' => (int) $row->pageviews,
				'is_enabled' => $row->is_enabled,
				'last_datetime' => $row->last_datetime,
				'datetime' => $row->datetime
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

		$link_id = isset($this->params[0]) ? (int) $this->params[0] : null;

		/* Try to get details about the resource id */
		$link = db()->where('link_id', $link_id)->where('user_id', $this->user->user_id)->getOne('links');

		/* We haven't found the resource */
		if(!$link) {
			$this->return_404();
		}

		/* Prepare the data */
		$data = [
			'id' => (int) $link->link_id,
			'user_id' => (int) $link->user_id,
			'project_id' => (int) $link->project_id,
			'domain_id' => (int) $link->domain_id,
			'pixels_ids' => json_decode($link->pixels_ids ?? '[]'),
			'email_reports_is_enabled' => (int) $link->email_reports_is_enabled,
			'email_reports_last_datetime' => $link->email_reports_last_datetime,
			'url' => $link->url,
            'location_url' => $link->location_url,
            'type' => $link->type,
            'file_name' => $link->file_name,
            'file_size' => (int) $link->file_size,
            'file_mime_type' => $link->file_mime_type,
            'name' => $link->name,
			'settings' => json_decode($link->settings ?? ''),
			'pageviews' => (int) $link->pageviews,
			'is_enabled' => $link->is_enabled,
			'last_datetime' => $link->last_datetime,
			'datetime' => $link->datetime
		];

		Response::jsonapi_success($data);

	}

	private function post() {

		/* Check for the plan limit */
		$total_rows = db()->where('user_id', $this->user->user_id)->getValue('links', 'count(`link_id`)');

		if($this->user->plan_settings->links_limit != -1 && $total_rows >= $this->user->plan_settings->links_limit) {
			$this->response_error(l('global.info_message.plan_feature_limit'), 401);
		}

		/* Bulk */
		$_POST['is_bulk'] = (int) isset($_POST['is_bulk']);

		/* Check for any errors */
		$required_fields = $_POST['is_bulk'] ? ['location_urls'] : ['location_url'];
		foreach($required_fields as $field) {
			if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
				$this->response_error(l('global.error_message.empty_fields'), 401);
				break 1;
			}
		}

		/* Get available custom domains */
		$domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

		/* Get available projects */
		$projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

		/* Get available pixels */
		$pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);

		$_POST['domain_id'] = isset($_POST['domain_id']) ? (isset($domains[$_POST['domain_id']]) ? (int) $_POST['domain_id'] : null) : null;

		if(!$_POST['domain_id'] && !settings()->links->main_domain_is_enabled && $this->user->type != 1) {
			$this->response_error(l('global.info_message.plan_feature_limit'), 401);
		}

		/* Location & url */
		$_POST['location_url'] = get_url($_POST['location_url'] ?? '');
		$_POST['url'] = !empty($_POST['url']) && $this->user->plan_settings->custom_url_is_enabled ? get_slug($_POST['url'], '-', false) : false;

		/* Check for duplicate url if needed */
		if(!$_POST['is_bulk']) {
			$this->check_location_url($_POST['location_url']);

			if($_POST['url']) {
				$domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
				$is_existing_link = database()->query("SELECT `link_id` FROM `links` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;

				if($is_existing_link) {
					$this->response_error(l('links.error_message.url_exists'), 401);
				}

				if(array_key_exists($_POST['url'], \Altum\Router::$routes['']) || in_array($_POST['url'], \Altum\Language::$active_languages) || file_exists(ROOT_PATH . $_POST['url'])) {
					$this->response_error(l('links.error_message.blacklisted_url'), 401);
				}

				if(in_array($_POST['url'], settings()->links->blacklisted_keywords)) {
					$this->response_error(l('links.error_message.blacklisted_keyword'), 401);
				}

				/* Make sure the custom url meets the requirements */
				if(mb_strlen($_POST['url']) < $this->user->plan_settings->url_minimum_characters ?? 1) {
					$this->response_error(sprintf(l('links.error_message.url_minimum_characters'), $this->user->plan_settings->url_minimum_characters ?? 1), 401);
				}

				if(mb_strlen($_POST['url']) > $this->user->plan_settings->url_maximum_characters ?? 64) {
					$this->response_error(sprintf(l('links.error_message.url_maximum_characters'), $this->user->plan_settings->url_maximum_characters ?? 64), 401);
				}
			}

			if(!$_POST['url']) {
				$is_existing_link = true;

				/* Generate random url if not specified */
				while ($is_existing_link) {
					$_POST['url'] = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));

					$domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
					$is_existing_link = database()->query("SELECT `link_id` FROM `links` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;
				}
			}
		}

		/* Bulk processing */
		if($_POST['is_bulk']) {
			$location_urls = preg_split('/\r\n|\r|\n/', $_POST['location_urls']);

			foreach($location_urls as $key => $location_url) {
				/* Skip empty lines */
				if(empty(trim($location_url))) {
					unset($location_urls[$key]);
					continue;
				}

				$this->check_location_url($location_url);
			}

			/* error checks */
			$total_bulk_urls = count($location_urls);
			if(!$total_bulk_urls) {
				$this->response_error(l('global.error_message.empty_field'), 401);
			}

			if($this->user->plan_settings->links_limit != -1 && $total_rows + $total_bulk_urls > $this->user->plan_settings->links_limit) {
				$this->response_error(l('global.info_message.plan_feature_limit'), 401);
			}
		}

		/* Process the rest of the data */
		$_POST['is_enabled'] = isset($_POST['is_enabled']) ? (int) (bool) $_POST['is_enabled'] : 1;
        $_POST['name'] = input_clean($_POST['name'] ?? '', 64);

		/* Email reports */
		$_POST['email_reports_is_enabled'] = isset($_POST['email_reports_is_enabled']) ? (int) (bool) $_POST['email_reports_is_enabled'] : 0;
		if(!settings()->links->email_reports_is_enabled || !$this->user->plan_settings->email_reports_is_enabled || $_POST['is_bulk']) {
			$_POST['email_reports_is_enabled'] = 0;
		}
		$email_reports_last_datetime = $_POST['email_reports_is_enabled'] ? get_date() : null;

		$_POST['schedule'] = isset($_POST['schedule']) ? (int) (bool) $_POST['schedule'] : 0;
		if($_POST['schedule'] && !empty($_POST['start_date']) && !empty($_POST['end_date']) && Date::validate($_POST['start_date'], 'Y-m-d H:i:s') && Date::validate($_POST['end_date'], 'Y-m-d H:i:s')) {
			$_POST['start_date'] = (new \DateTime($_POST['start_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
			$_POST['end_date'] = (new \DateTime($_POST['end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
		} else {
			$_POST['start_date'] = $_POST['end_date'] = null;
		}

		$_POST['expiration_url'] = get_url($_POST['expiration_url'] ?? null);
		$_POST['pageviews_limit'] = isset($_POST['pageviews_limit']) ? (int) $_POST['pageviews_limit'] : null;
		$this->check_location_url($_POST['expiration_url'], true);

		/* Pixels */
		$_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map(
			'intval',
			array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
				return array_key_exists($pixel_id, $pixels);
			})
		) : [];
		$_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

		/* Project */
		$_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

		/* Check for a password set */
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
		$_POST['password'] = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

		/* Sensitive content */
		$_POST['sensitive_content'] = (int) isset($_POST['sensitive_content']);

		/* App linking check */
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
		$_POST['cloaking_title'] = input_clean($_POST['cloaking_title'] ?? '', 70);
		$_POST['cloaking_meta_description'] = input_clean($_POST['cloaking_meta_description'] ?? '', 160);
		$_POST['cloaking_custom_js'] = isset($_POST['cloaking_custom_js']) ? mb_substr(trim($_POST['cloaking_custom_js']), 0, 10000) : null;
		$cloaking_favicon = \Altum\Uploads::process_upload(null, 'favicons', 'cloaking_favicon', 'cloaking_favicon_remove', settings()->links->favicon_size_limit, 'json_error');
		$cloaking_opengraph = \Altum\Uploads::process_upload(null, 'opengraphs', 'cloaking_opengraph', 'cloaking_opengraph_remove', settings()->links->opengraph_size_limit, 'json_error');

		/* HTTP */
		$_POST['http_status_code'] = isset($_POST['http_status_code']) && in_array($_POST['http_status_code'], [301, 302, 307, 308]) ? (int) $_POST['http_status_code'] : 301;

		/* Query parameters forwarding */
		$_POST['forward_query_parameters_is_enabled'] = (int) isset($_POST['forward_query_parameters_is_enabled']);

		/* UTM */
		$_POST['utm_medium'] = input_clean($_POST['utm_medium'] ?? '', 128);
		$_POST['utm_source'] = input_clean($_POST['utm_source'] ?? '', 128);
		$_POST['utm_campaign'] = input_clean($_POST['utm_campaign'] ?? '', 128);

        /* SEO */
        $_POST['is_se_visible'] = $this->user->plan_settings->search_engine_visibility_is_enabled ? (int) isset($_POST['is_se_visible']) : 0;

		/* Prepare the settings */
		$targeting_types = ['continent_code', 'country_code', 'city_name', 'device_type', 'browser_language', 'rotation', 'os_name', 'browser_name'];
		$_POST['targeting_type'] = isset($_POST['targeting_type']) && in_array($_POST['targeting_type'], array_merge(['false'], $targeting_types)) ? query_clean($_POST['targeting_type']) : 'false';

		$settings = [
			'schedule' => $_POST['schedule'],
			'start_date' => $_POST['start_date'],
			'end_date' => $_POST['end_date'],
			'pageviews_limit' => $_POST['pageviews_limit'],
			'expiration_url' => $_POST['expiration_url'],
			'password' => $_POST['password'],
			'sensitive_content' => $_POST['sensitive_content'],
			'targeting_type' => $_POST['targeting_type'],
			'app_linking_is_enabled' => $_POST['app_linking_is_enabled'],
			'app_linking' => $app_linking,
			'cloaking_is_enabled' => $_POST['cloaking_is_enabled'],
			'cloaking_title' => $_POST['cloaking_title'],
			'cloaking_meta_description' => $_POST['cloaking_meta_description'],
			'cloaking_custom_js' => $_POST['cloaking_custom_js'],
			'cloaking_favicon' => $cloaking_favicon,
			'cloaking_opengraph' => $cloaking_opengraph,
			'http_status_code' => $_POST['http_status_code'],
			'forward_query_parameters_is_enabled' => $_POST['forward_query_parameters_is_enabled'],

			/* UTM */
			'utm' => [
				'source' => $_POST['utm_source'],
				'medium' => $_POST['utm_medium'],
				'campaign' => $_POST['utm_campaign'],
			],

            /* SEO */
            'is_se_visible' => $_POST['is_se_visible'],
		];

		/* Process the targeting */
		foreach($targeting_types as $targeting_type) {
			${'targeting_' . $targeting_type} = [];

			if(isset($_POST['targeting_' . $targeting_type . '_key'])) {
				foreach($_POST['targeting_' . $targeting_type . '_key'] as $key => $value) {
					if(empty(trim($_POST['targeting_' . $targeting_type . '_value'][$key]))) continue;

					${'targeting_' . $targeting_type}[] = [
						'key' => trim(query_clean($value)),
						'value' => get_url($_POST['targeting_' . $targeting_type . '_value'][$key]),
					];
				}

				$settings['targeting_' . $targeting_type] = ${'targeting_' . $targeting_type};
			}
		}

		/* Clear the cache */
		cache()->deleteItem('links?user_id=' . $this->user->user_id);
		cache()->deleteItem('links_total?user_id=' . $this->user->user_id);
		cache()->deleteItem('links_dashboard?user_id=' . $this->user->user_id);

		/* Single url */
		if(!$_POST['is_bulk']) {
			$url = $_POST['url'] ?: $this->generate_random_url();

			/* Settings */
			$settings = json_encode($settings);

			/* Database query */
			$link_id = db()->insert('links', [
				'user_id' => $this->user->user_id,
				'domain_id' => $_POST['domain_id'],
				'project_id' => $_POST['project_id'],
				'pixels_ids' => $_POST['pixels_ids'],
				'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
				'email_reports_last_datetime' => $email_reports_last_datetime,
				'url' => $url,
				'location_url' => $_POST['location_url'],
                'name' => $_POST['name'],
				'settings' => $settings,
				'is_enabled' => $_POST['is_enabled'],
				'datetime' => get_date(),
			]);

            /* Send webhook notification if needed */
            if(settings()->webhooks->link_new) {
                fire_and_forget('post', settings()->webhooks->link_new, [
                    'user_id' => $this->user->user_id,
                    'link_id' => $link_id,
                    'domain_id' => $_POST['domain_id'],
                    'url' => $url,
                    'location_url' => $_POST['location_url'],
                    'name' => $_POST['name'],
                    'full_url' => $_POST['domain_id'] ? $domains[$_POST['domain_id']]->url . $url : SITE_URL . $url,
                    'datetime' => get_date(),
                ], signature: true);
            }

			/* Prepare the data */
			$data = [
				'id' => (int) $link_id,
				'user_id' => (int) $this->user->user_id,
				'project_id' => (int) $_POST['project_id'],
				'domain_id' => (int) $_POST['domain_id'],
				'pixels_ids' => json_decode($_POST['pixels_ids']),
				'email_reports_is_enabled' => (int) $_POST['email_reports_is_enabled'],
				'email_reports_last_datetime' => $email_reports_last_datetime,
				'url' => $url,
				'location_url' => $_POST['location_url'],
				'name' => $_POST['name'],
				'settings' => json_decode($settings),
				'pageviews' => 0,
				'is_enabled' => (int) $_POST['is_enabled'],
				'last_datetime' => null,
				'datetime' => get_date()
			];
		}

		/* Bulk URLS */
		if($_POST['is_bulk']) {
			$i = 1;
			$data = [];

			foreach($location_urls as $location_url) {
				$url = $this->generate_random_url();

				/* App linking processing per each URL */
				$app_linking = [
					'ios_location_url' => null,
					'android_location_url' => null,
					'app' => null,
				];

				if($_POST['app_linking_is_enabled']) {
					$supported_apps = require APP_PATH . 'includes/app_linking.php';
					$app_linking_location_url = $location_url;

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

				/* Database query */
				$link_id = db()->insert('links', [
					'user_id' => $this->user->user_id,
					'domain_id' => $_POST['domain_id'],
					'project_id' => $_POST['project_id'],
					'pixels_ids' => $_POST['pixels_ids'],
					'email_reports_is_enabled' => 0,
					'email_reports_last_datetime' => null,
					'url' => $url,
					'location_url' => $location_url,
                    'name' => $_POST['name'],
					'settings' => json_encode($settings),
					'is_enabled' => $_POST['is_enabled'],
					'datetime' => get_date(),
				]);

				/* Prepare the data */
				$data[] = [
					'id' => (int) $link_id,
					'user_id' => (int) $this->user->user_id,
					'project_id' => (int) $_POST['project_id'],
					'domain_id' => (int) $_POST['domain_id'],
					'pixels_ids' => json_decode($_POST['pixels_ids']),
					'email_reports_is_enabled' => 0,
					'email_reports_last_datetime' => null,
					'url' => $url,
					'location_url' => $location_url,
					'name' => $_POST['name'],
					'settings' => $settings,
					'pageviews' => 0,
					'is_enabled' => (int) $_POST['is_enabled'],
					'last_datetime' => null,
					'datetime' => get_date()
				];
			}
		}


		Response::jsonapi_success($data, null, 201);
	}

	private function patch() {

		$link_id = isset($this->params[0]) ? (int) $this->params[0] : null;

		/* Try to get details about the resource id */
		$link = db()->where('link_id', $link_id)->where('user_id', $this->user->user_id)->getOne('links');

		/* We haven't found the resource */
		if(!$link) {
			$this->return_404();
		}

		if($link->type == 'file') {
			$this->response_error(l('qr_codes.error_message.file_api_not_available'), 400);
		}

		$link->settings = json_decode($link->settings ?? '');
		$link->pixels_ids = json_decode($link->pixels_ids);

		if(isset($_POST['domain_id']) && $_POST['domain_id'] == 0 && !settings()->links->main_domain_is_enabled && $this->user->type != 1) {
			$this->response_error(l('create_link_modal.error_message.main_domain_is_disabled'), 401);
		}

		/* Get available custom domains */
		$domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user);

		/* Get available projects */
		$projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

		/* Get available pixels */
		$pixels = (new \Altum\Models\Pixel())->get_pixels($this->user->user_id);

		$_POST['domain_id'] = isset($_POST['domain_id']) ? (isset($domains[$_POST['domain_id']]) ? (int) $_POST['domain_id'] : null) : $link->domain_id;

		/* Location & url */
		$_POST['location_url'] = get_url($_POST['location_url'] ?? $link->location_url);
		$_POST['url'] = !empty($_POST['url']) ? get_slug($_POST['url'], '-', false) : $link->url;
		$this->check_location_url($_POST['location_url']);

        $_POST['name'] = isset($_POST['name']) ? input_clean($_POST['name'], 64) : $link->name;

		/* Check for duplicate url if needed */
		if(
			($_POST['url'] && $this->user->plan_settings->custom_url_is_enabled && $_POST['url'] != $link->url)
			|| ($link->domain_id != $_POST['domain_id'])
		) {
			$domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
			$is_existing_link = database()->query("SELECT `link_id` FROM `links` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;

			if($is_existing_link) {
				$this->response_error(l('links.error_message.url_exists'), 401);
			}

			if(array_key_exists($_POST['url'], \Altum\Router::$routes['']) || in_array($_POST['url'], \Altum\Language::$active_languages) || file_exists(ROOT_PATH . $_POST['url'])) {
				$this->response_error(l('links.error_message.blacklisted_url'), 401);
			}

			if(in_array($_POST['url'], settings()->links->blacklisted_keywords)) {
				$this->response_error(l('links.error_message.blacklisted_keyword'), 401);
			}

			/* Make sure the custom url meets the requirements */
			if(mb_strlen($_POST['url']) < $this->user->plan_settings->url_minimum_characters ?? 1) {
				$this->response_error(sprintf(l('links.error_message.url_minimum_characters'), $this->user->plan_settings->url_minimum_characters ?? 1), 401);
			}

			if(mb_strlen($_POST['url']) > $this->user->plan_settings->url_maximum_characters ?? 64) {
				$this->response_error(sprintf(l('links.error_message.url_maximum_characters'), $this->user->plan_settings->url_maximum_characters ?? 64), 401);
			}
		}

		if(!$_POST['url']) {
			$is_existing_link = true;

			/* Generate random url if not specified */
			while($is_existing_link) {
				$_POST['url'] = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));

				$domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
				$is_existing_link = database()->query("SELECT `link_id` FROM `links` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;
			}
		}

		/* Process the rest of the data */
		$_POST['is_enabled'] = isset($_POST['is_enabled']) ? (int) $_POST['is_enabled'] : $link->is_enabled;

		/* Email reports */
		$_POST['email_reports_is_enabled'] = isset($_POST['email_reports_is_enabled']) ? (int) (bool) $_POST['email_reports_is_enabled'] : $link->email_reports_is_enabled;
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

		$_POST['schedule'] = isset($_POST['schedule']) ? (int) (bool) $_POST['schedule'] : $link->settings->schedule;
		if($_POST['schedule'] && !empty($_POST['start_date']) && !empty($_POST['end_date']) && Date::validate($_POST['start_date'], 'Y-m-d H:i:s') && Date::validate($_POST['end_date'], 'Y-m-d H:i:s')) {
			$_POST['start_date'] = (new \DateTime($_POST['start_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
			$_POST['end_date'] = (new \DateTime($_POST['end_date'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
		} else {
			$_POST['start_date'] = $link->settings->start_date;
			$_POST['end_date'] = $link->settings->end_date;
		}

		$_POST['expiration_url'] = get_url($_POST['expiration_url'] ?? $link->settings->expiration_url);
		$_POST['pageviews_limit'] = isset($_POST['pageviews_limit']) ? (int) $_POST['pageviews_limit'] : $link->settings->pageviews_limit;
		$this->check_location_url($_POST['expiration_url'], true);

		/* Existing pixels */
		$_POST['pixels_ids'] = isset($_POST['pixels_ids']) ? array_map(
			'intval',
			array_filter($_POST['pixels_ids'], function($pixel_id) use($pixels) {
				return array_key_exists($pixel_id, $pixels);
			})
		) : [];
		$_POST['pixels_ids'] = json_encode($_POST['pixels_ids']);

		/* Project */
		$_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;

		/* Check for a password set */
        $_POST['password'] = mb_substr($_POST['password'] ?? '', 0, 64);
		$_POST['password'] = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $link->settings->password;

		/* Sensitive content */
		$_POST['sensitive_content'] = isset($_POST['sensitive_content']) ? (int) $_POST['sensitive_content'] : $link->settings->sensitive_content;

		/* App linking check */
		$_POST['app_linking_is_enabled'] = isset($_POST['app_linking_is_enabled']) ? (int) $_POST['app_linking_is_enabled'] : $link->settings->app_linking_is_enabled;

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
		$_POST['cloaking_is_enabled'] = isset($_POST['cloaking_is_enabled']) ? (int) $_POST['cloaking_is_enabled'] : $link->settings->cloaking_is_enabled;
		$_POST['cloaking_title'] = isset($_POST['cloaking_title']) ? input_clean($_POST['cloaking_title'], 70) : $link->settings->cloaking_title;;
		$_POST['cloaking_meta_description'] = isset($_POST['cloaking_meta_description']) ? input_clean($_POST['cloaking_meta_description'], 160) : $link->settings->cloaking_meta_description;;
		$_POST['cloaking_custom_js'] = isset($_POST['cloaking_custom_js']) ? mb_substr(trim($_POST['cloaking_custom_js']), 0, 10000) : $link->settings->cloaking_custom_js;
		$link->settings->cloaking_favicon = \Altum\Uploads::process_upload($link->settings->cloaking_favicon, 'favicons', 'cloaking_favicon', 'cloaking_favicon_remove', settings()->links->favicon_size_limit, 'json_error');
		$link->settings->cloaking_opengraph = \Altum\Uploads::process_upload($link->settings->cloaking_opengraph, 'opengraphs', 'cloaking_opengraph', 'cloaking_opengraph_remove', settings()->links->opengraph_size_limit, 'json_error');

		/* HTTP */
		$_POST['http_status_code'] = isset($_POST['http_status_code']) && in_array($_POST['http_status_code'], [301, 302, 307, 308]) ? (int) $_POST['http_status_code'] : $link->settings->http_status_code;;

		/* Query parameters forwarding */
		$_POST['forward_query_parameters_is_enabled'] = isset($_POST['forward_query_parameters_is_enabled']) ? (int) $_POST['forward_query_parameters_is_enabled'] : $link->settings->forward_query_parameters_is_enabled;

		/* UTM */
		$_POST['utm_medium'] = input_clean($_POST['utm_medium'] ?? $link->settings->utm->medium, 128);
		$_POST['utm_source'] = input_clean($_POST['utm_source'] ?? $link->settings->utm->source, 128);
		$_POST['utm_campaign'] = input_clean($_POST['utm_campaign'] ?? $link->settings->utm->campaign, 128);

        /* SEO */
        $_POST['is_se_visible'] = isset($_POST['is_se_visible']) ? (int) $_POST['is_se_visible'] : $link->settings->is_se_visible;

		/* Prepare the settings */
		$targeting_types = ['continent_code', 'country_code', 'city_name', 'device_type', 'browser_language', 'rotation', 'os_name', 'browser_name'];
		$_POST['targeting_type'] = isset($_POST['targeting_type']) && in_array($_POST['targeting_type'], array_merge(['false'], $targeting_types)) ? query_clean($_POST['targeting_type']) : $link->settings->targeting_type;

		$settings = [
			'schedule' => $_POST['schedule'],
			'start_date' => $_POST['start_date'],
			'end_date' => $_POST['end_date'],
			'pageviews_limit' => $_POST['pageviews_limit'],
			'expiration_url' => $_POST['expiration_url'],
			'password' => $_POST['password'],
			'sensitive_content' => $_POST['sensitive_content'],
			'targeting_type' => $_POST['targeting_type'],
			'app_linking_is_enabled' => $_POST['app_linking_is_enabled'],
			'app_linking' => $app_linking,
			'cloaking_is_enabled' => $_POST['cloaking_is_enabled'],
			'cloaking_title' => $_POST['cloaking_title'],
			'cloaking_meta_description' => $_POST['cloaking_meta_description'],
			'cloaking_custom_js' => $_POST['cloaking_custom_js'],
			'cloaking_favicon' => $link->settings->cloaking_favicon,
			'cloaking_opengraph' => $link->settings->cloaking_opengraph,
			'http_status_code' => $_POST['http_status_code'],
			'forward_query_parameters_is_enabled' => $_POST['forward_query_parameters_is_enabled'],

			/* UTM */
			'utm' => [
				'source' => $_POST['utm_source'],
				'medium' => $_POST['utm_medium'],
				'campaign' => $_POST['utm_campaign'],
			],

            /* SEO */
            'is_se_visible' => $_POST['is_se_visible'],
		];

		/* Process the targeting */
		foreach($targeting_types as $targeting_type) {
			${'targeting_' . $targeting_type} = [];

			if(isset($_POST['targeting_' . $targeting_type . '_key'])) {
				foreach($_POST['targeting_' . $targeting_type . '_key'] as $key => $value) {
					if(empty(trim($_POST['targeting_' . $targeting_type . '_value'][$key]))) continue;

					${'targeting_' . $targeting_type}[] = [
						'key' => trim(query_clean($value)),
						'value' => get_url($_POST['targeting_' . $targeting_type . '_value'][$key]),
					];
				}

				$settings['targeting_' . $targeting_type] = ${'targeting_' . $targeting_type};
			}
		}

		$settings = json_encode($settings);

		/* Database query */
		db()->where('link_id', $link->link_id)->update('links', [
			'project_id' => $_POST['project_id'],
			'domain_id' => $_POST['domain_id'],
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

		/* Prepare the data */
		$data = [
			'id' => (int) $link->link_id,
			'user_id' => (int) $link->user_id,
			'project_id' => (int) $_POST['project_id'],
			'domain_id' => (int) $_POST['domain_id'],
			'pixels_ids' => json_decode($_POST['pixels_ids']),
			'email_reports_is_enabled' => (int) $_POST['email_reports_is_enabled'],
			'email_reports_last_datetime' => $email_reports_last_datetime,
			'url' => $_POST['url'],
			'location_url' => $_POST['location_url'],
			'name' => $_POST['name'],
			'settings' => json_decode($settings),
			'pageviews' => (int) $link->pageviews,
			'is_enabled' => (int) $_POST['is_enabled'],
			'last_datetime' => get_date(),
			'datetime' => $link->datetime
		];

		Response::jsonapi_success($data, null, 200);

	}

	private function delete() {

		$link_id = isset($this->params[0]) ? (int) $this->params[0] : null;

		/* Try to get details about the resource id */
		$link = db()->where('link_id', $link_id)->where('user_id', $this->user->user_id)->getOne('links');

		/* We haven't found the resource */
		if(!$link) {
			$this->return_404();
		}

		/* Delete the resource */
		(new \Altum\Models\Link())->delete($link->link_id);

		http_response_code(200);
		die();

	}

	private function generate_random_url() {
		$is_existing_link = true;
		$url = null;

		/* Generate random url if not specified */
		while($is_existing_link) {
			$url = mb_strtolower(string_generate(settings()->links->random_url_length ?? 7));

			$domain_id_where = !empty($_POST['domain_id']) ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
			$is_existing_link = database()->query("SELECT `link_id` FROM `links` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;
		}

		return $url;
	}

	/* Function to bundle together all the checks of an url */
	private function check_location_url($url, $can_be_empty = false) {

		if(empty(trim($url)) && $can_be_empty) {
			return;
		}

		if(empty(trim($url))) {
			$this->response_error(l('global.error_message.empty_fields'), 401);
		}

		$url_details = parse_url($url);

		if(!isset($url_details['scheme'])) {
			$this->response_error(l('links.error_message.invalid_location_url'), 401);
		}

		/* Make sure the domain is not blacklisted */
		$domain = get_domain_from_url($url);

		if($domain && in_array($domain, settings()->links->blacklisted_domains)) {
			$this->response_error(l('links.error_message.blacklisted_domain'), 401);
		}

		/* Check the url with google safe browsing to make sure it is a safe website */
		if(settings()->links->google_safe_browsing_is_enabled) {
			if(google_safe_browsing_check($url, settings()->links->google_safe_browsing_api_key)) {
				$this->response_error(l('links.error_message.blacklisted_location_url'), 401);
			}
		}
	}
}
