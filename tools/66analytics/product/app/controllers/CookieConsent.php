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


defined('ALTUMCODE') || die();

class CookieConsent extends Controller {

	public function index() {

		if(!settings()->cookie_consent->is_enabled || !settings()->cookie_consent->logging_is_enabled) {
			throw_404();
		}

		$payload = @file_get_contents('php://input');

		if(empty($payload)) {
			throw_404();
		}

		$_POST = json_decode($payload, true);

		if(!\Altum\Csrf::check('global_token')) {
			throw_404();
		}

		/* Detect extra details about the user */
		$whichbrowser = get_whichbrowser();

		/* Do not track bots */
		if($whichbrowser->device->type == 'bot') {
			return;
		}

		$allowed_levels = ['necessary', 'analytics', 'targeting'];
		$levels = array_filter($_POST['level'], function($level) use ($allowed_levels) {
			return in_array($level, $allowed_levels);
		});

		/* Generate new CSV line */
		$browser_name = $whichbrowser->browser->name ?? null;
		$os_name = $whichbrowser->os->name ?? null;
		$browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
		$device_type = get_this_device_type();
		$ip = get_ip();
		$date = (new \DateTime())->format('Y-m-d');
		$time = (new \DateTime())->format('H:i:s') . ' UTC';
		$accepted_levels = implode('+', $levels);

		$file_path = UPLOADS_PATH . 'cookie_consent/data.csv';
		$is_new_file = !file_exists($file_path);

		$file = fopen($file_path, 'a');

		if($file) {
			if($is_new_file) {
				fputcsv($file, ['IP', 'Date', 'Time', 'Accepted cookies', 'Device type', 'Browser language', 'Browser name', 'OS Name']);
			}

			fputcsv($file, [$ip, $date, $time, $accepted_levels, $device_type, $browser_language, $browser_name, $os_name]);
			fclose($file);
		}

		/* Generate .htaccess if not existing */
		if(!file_exists(UPLOADS_PATH . 'cookie_consent/.htaccess')) {
			file_put_contents(UPLOADS_PATH . 'cookie_consent/.htaccess', 'Deny from all');
		}
	}

}
