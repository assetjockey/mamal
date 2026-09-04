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

use Altum\Models\Domain;
use Altum\Models\User;

defined('ALTUMCODE') || die();

class DigitalWalletAdd extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('digital-wallets') || !settings()->digital_wallets->is_enabled) {
            throw_404();
        }

        $digital_wallet_hash = isset($this->params[0]) ? input_clean($this->params[0], 32) : null;

        if(!$digital_wallet_hash) {
            throw_404();
        }

        if(!$digital_wallet = db()->where('digital_wallet_hash', $digital_wallet_hash)->getOne('digital_wallets')) {
            throw_404();
        }

		/* Get the owner details */
		$user = (new User())->get_user_by_user_id($digital_wallet->user_id);

		/* Make sure to check if the user is active */
		if(!$user || $user->status != 1) {
			throw_404();
		}

		/* Prepare wallet link */
        $digital_wallet->settings = json_decode($digital_wallet->settings ?? '') ?: (object) [];

        $digital_wallet->title = $digital_wallet->settings->title ?? $digital_wallet->name;
        $digital_wallet->subtitle = $digital_wallet->settings->subtitle ?? null;
        $digital_wallet->background_color = $digital_wallet->settings->background_color ?? null;
        $digital_wallet->phone = $digital_wallet->settings->phone ?? null;
        $digital_wallet->email = $digital_wallet->settings->email ?? null;
        $digital_wallet->website = $digital_wallet->settings->website ?? null;
        $digital_wallet->destination_url = $digital_wallet->location_url;
        $digital_wallet->logo_url = !empty($digital_wallet->settings->logo) ? \Altum\Uploads::get_full_url('digital_wallets') . $digital_wallet->settings->logo : null;
        $digital_wallet->banner_url = !empty($digital_wallet->settings->image) ? \Altum\Uploads::get_full_url('digital_wallets') . $digital_wallet->settings->image : null;

        if($digital_wallet->domain_id) {
            $domain = (new Domain())->get_domain_by_domain_id($digital_wallet->domain_id);
        }

        $provider = isset($_GET['provider']) && in_array($_GET['provider'], ['google', 'apple']) ? $_GET['provider'] : null;

        /* Keep existing wallet URLs working by preferring Google Wallet */
        if(!$provider) {
            if(digital_wallets_is_google_wallet_ready()) {
                $provider = 'google';
            } else if(digital_wallets_is_apple_wallet_ready()) {
                $provider = 'apple';
            }
        }

        if(
            ($provider == 'google' && !digital_wallets_is_google_wallet_ready())
            || ($provider == 'apple' && !digital_wallets_is_apple_wallet_ready())
            || !$provider
        ) {
            throw_404();
        }

        $this->create_statistics($digital_wallet, $user);

        switch($provider) {
            case 'google':
                header('Location: ' . get_google_wallet_add_url($digital_wallet, $domain->url ?? null));
                die();

            case 'apple':
                try {
                    $pass = create_apple_wallet_pass($digital_wallet);

                    header('Content-Type: application/vnd.apple.pkpass');
                    header('Content-Disposition: attachment; filename="' . $pass['file_name'] . '"');
                    header('Content-Transfer-Encoding: binary');
                    header('Content-Length: ' . filesize($pass['path']));
                    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

                    readfile($pass['path']);
                    digital_wallets_delete_directory($pass['temporary_path']);
                    die();
                } catch(\Throwable $exception) {
                    if(DEBUG) {
                        die($exception->getMessage());
                    }

                    http_response_code(500);
                    die(l('digital_wallets.apple_wallet_error'));
                }
        }
    }

    private function create_statistics($digital_wallet, $user) {

        $cookie_name = 's_digital_wallet_' . $digital_wallet->digital_wallet_id;

        if(isset($_COOKIE[$cookie_name]) && (int) $_COOKIE[$cookie_name] >= 3) {
            return;
        }

        /* Ignore excluded ips */
        $excluded_ips = array_flip($user->preferences->excluded_ips ?? []);
        if(isset($excluded_ips[get_ip()])) return;

        /* Detect extra details about the user */
        $whichbrowser = get_whichbrowser();

        /* Do not track bots */
        if($whichbrowser->device->type == 'bot') {
            return;
        }

        db()->where('digital_wallet_id', $digital_wallet->digital_wallet_id)->update('digital_wallets', ['pageviews' => db()->inc()]);

        /* Set cookie to try and avoid multiple entrances */
        $cookie_new_value = isset($_COOKIE[$cookie_name]) ? (int) $_COOKIE[$cookie_name] + 1 : 1;
        setcookie($cookie_name, (int) $cookie_new_value, time()+60*60*24*1);
    }

}
