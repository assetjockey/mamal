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


use Altum\Meta;
use Altum\Title;

defined('ALTUMCODE') || die();

class Plan extends Controller {

	public function index() {

		if(!settings()->payment->is_enabled) {
			throw_404();
		}

		$type = isset($this->params[0]) && in_array($this->params[0], ['renew', 'upgrade', 'new']) ? $this->params[0] : 'new';

		/* If the user is not logged in when trying to upgrade or renew, make sure to redirect them */
		if(in_array($type, ['renew', 'upgrade']) && !is_logged_in()) {
			redirect('plan/new');
		}

		/* Currency specific pages */
		$chosen_currency = isset($_GET['currency']) && array_key_exists($_GET['currency'], (array) settings()->payment->currencies) ? $_GET['currency'] : null;

		if($chosen_currency) {
			\Altum\Currency::$currency = $_GET['currency'];

			/* Update user */
			if(is_logged_in()) {
				/* Database query */
				db()->where('user_id', \Altum\Authentication::$user_id)->update('users', ['currency' => $_GET['currency']]);

				/* Clear the cache */
				cache()->deleteItemsByTag('user_id=' . \Altum\Authentication::$user_id);
			}

			/* Update canonical */
			if(settings()->payment->default_currency != $chosen_currency) {
				Meta::set_canonical_url(url(\Altum\Router::$original_request . '?currency=' . $_GET['currency']));
			}
		}

		/* Set a custom title */
		if($type != 'new') {
			Title::set(l('plan.header_' . $type));
		}

		/* Plans View */
		$data = [
			'currency' => $chosen_currency,
		];

		$view = new \Altum\View('partials/plans', (array) $this);

		$this->add_view_content('plans', $view->run($data));


		/* Prepare the view */
		$data = [
			'type' => $type
		];

		$view = new \Altum\View('plan/index', (array) $this);

		$this->add_view_content('content', $view->run($data));

	}

}
