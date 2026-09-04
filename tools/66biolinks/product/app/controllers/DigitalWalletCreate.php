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

use Altum\Alerts;

defined('ALTUMCODE') || die();

class DigitalWalletCreate extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('digital-wallets') || !settings()->digital_wallets->is_enabled || (!digital_wallets_is_google_wallet_ready() && !digital_wallets_is_apple_wallet_ready())) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.digital_wallets')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('digital-wallets');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `digital_wallets` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->digital_wallets_limit != -1 && $total_rows >= $this->user->plan_settings->digital_wallets_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('digital-wallets');
        }

        /* Existing links */
        $links = (new \Altum\Models\Link())->get_full_links_by_user_id($this->user->user_id);

        if(!empty($_POST)) {
            $raw_location_url = $_POST['location_url'] ?? '';
            $raw_website = $_POST['website'] ?? '';

            $_POST['name'] = input_clean($_POST['name'], 128);
            $_POST['title'] = input_clean($_POST['title'], 128);
            $_POST['subtitle'] = input_clean($_POST['subtitle'], 128);
            $_POST['location_url'] = get_url($raw_location_url, 2048, false);
            $_POST['link_id'] = isset($_POST['link_id']) ? (int) $_POST['link_id'] : null;
            $_POST['background_color'] = !empty($_POST['background_color']) && preg_match('/^#[a-f0-9]{6}$/i', $_POST['background_color']) ? $_POST['background_color'] : '#111827';
            $_POST['phone'] = input_clean($_POST['phone'], 32);
            $_POST['email'] = input_clean($_POST['email'], 320);
            $_POST['website'] = get_url($raw_website, 2048, false);

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            $domain_id = null;

            if($_POST['link_id']) {
                if(!isset($links[$_POST['link_id']])) {
                    Alerts::add_field_error('link_id', l('global.error_message.basic'));
                } else {
                    $link = $links[$_POST['link_id']];
                    $domain_id = $link->domain_id ?: null;

                    if(!$_POST['location_url']) {
                        $_POST['location_url'] = $link->full_url;
                    }
                }
            } else {
                $_POST['link_id'] = null;
            }

            /* Image uploads */
            $logo_size_limit = settings()->digital_wallets->logo_size_limit ?? get_max_upload();
            $image_size_limit = settings()->digital_wallets->image_size_limit ?? get_max_upload();

            $logo = \Altum\Uploads::process_upload(null, 'digital_wallets', 'logo', 'logo_remove', $logo_size_limit);
            $image = \Altum\Uploads::process_upload(null, 'digital_wallets', 'image', 'image_remove', $image_size_limit);

            /* Check for any errors */
            $required_fields = ['name', 'title', 'location_url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                Alerts::add_field_error('email', l('global.error_message.invalid_email'));
            }

            if(!empty($raw_website) && empty($_POST['website'])) {
                Alerts::add_field_error('website', l('link.error_message.invalid_location_url'));
            }

            if(!empty($raw_location_url) && empty($_POST['location_url'])) {
                Alerts::add_field_error('location_url', l('link.error_message.invalid_location_url'));
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                do {
                    $digital_wallet_hash = bin2hex(random_bytes(16));
                } while(db()->where('digital_wallet_hash', $digital_wallet_hash)->has('digital_wallets'));

                $settings = json_encode([
                    'title' => $_POST['title'],
                    'subtitle' => $_POST['subtitle'],
                    'logo' => $logo,
                    'image' => $image,
                    'background_color' => $_POST['background_color'],
                    'phone' => $_POST['phone'],
                    'email' => $_POST['email'],
                    'website' => $_POST['website'],
                ]);

                /* Database query */
                db()->insert('digital_wallets', [
                    'digital_wallet_hash' => $digital_wallet_hash,
                    'user_id' => $this->user->user_id,
                    'domain_id' => $domain_id,
                    'link_id' => $_POST['link_id'],
                    'location_url' => $_POST['location_url'],
                    'name' => $_POST['name'],
                    'pageviews' => 0,
                    'settings' => $settings,
                    'datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('digital_wallets?user_id=' . $this->user->user_id);

                redirect('digital-wallets');
            }
        }

        $values = [
            'name' => $_POST['name'] ?? '',
            'title' => $_POST['title'] ?? '',
            'subtitle' => $_POST['subtitle'] ?? '',
            'link_id' => $_POST['link_id'] ?? '',
            'location_url' => $_POST['location_url'] ?? '',
            'background_color' => $_POST['background_color'] ?? '#111827',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'website' => $_POST['website'] ?? '',
        ];

        /* Prepare the view */
        $data = [
            'values' => $values,
            'links' => $links,
        ];

        $view = new \Altum\View('digital-wallet-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
