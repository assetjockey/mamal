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

use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiDigitalWallets extends Controller {
    use Apiable;

    public function index() {

        if(!\Altum\Plugin::is_active('digital-wallets') || !settings()->digital_wallets->is_enabled) {
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
        $filters = (new \Altum\Filters(['domain_id', 'link_id'], ['name'], ['digital_wallet_id', 'domain_id', 'link_id', 'pageviews', 'last_datetime', 'name', 'datetime'], allowed_datetime_fields: ['last_datetime', 'datetime']));
        $filters->set_default_order_by($this->user->preferences->digital_wallets_default_order_by ?? 'digital_wallet_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `digital_wallets` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/digital-wallets?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `digital_wallets`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {
            /* Prepare the data */
            $row = [
                'id' => (int) $row->digital_wallet_id,
                'hash' => $row->digital_wallet_hash,
                'wallet_urls' => [
                    'google' => digital_wallets_is_google_wallet_ready() ? url('digital-wallet-add/' . $row->digital_wallet_hash . '?provider=google') : null,
                    'apple' => digital_wallets_is_apple_wallet_ready() ? url('digital-wallet-add/' . $row->digital_wallet_hash . '?provider=apple') : null,
                ],
                'user_id' => (int) $row->user_id,
                'domain_id' => $row->domain_id ? (int) $row->domain_id : null,
                'link_id' => $row->link_id ? (int) $row->link_id : null,
                'location_url' => $row->location_url,
                'name' => $row->name,
                'pageviews' => (int) $row->pageviews,
                'settings' => json_decode($row->settings ?? '') ?: (object) [],
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

        $digital_wallet_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $digital_wallet = db()->where('digital_wallet_id', $digital_wallet_id)->where('user_id', $this->user->user_id)->getOne('digital_wallets');

        /* We haven't found the resource */
        if(!$digital_wallet) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $digital_wallet->digital_wallet_id,
            'hash' => $digital_wallet->digital_wallet_hash,
            'wallet_urls' => [
                'google' => digital_wallets_is_google_wallet_ready() ? url('digital-wallet-add/' . $digital_wallet->digital_wallet_hash . '?provider=google') : null,
                'apple' => digital_wallets_is_apple_wallet_ready() ? url('digital-wallet-add/' . $digital_wallet->digital_wallet_hash . '?provider=apple') : null,
            ],
            'user_id' => (int) $digital_wallet->user_id,
            'domain_id' => $digital_wallet->domain_id ? (int) $digital_wallet->domain_id : null,
            'link_id' => $digital_wallet->link_id ? (int) $digital_wallet->link_id : null,
            'location_url' => $digital_wallet->location_url,
            'name' => $digital_wallet->name,
            'pageviews' => (int) $digital_wallet->pageviews,
            'settings' => json_decode($digital_wallet->settings ?? '') ?: (object) [],
            'last_datetime' => $digital_wallet->last_datetime,
            'datetime' => $digital_wallet->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        if(!digital_wallets_is_google_wallet_ready() && !digital_wallets_is_apple_wallet_ready()) {
            $this->return_404();
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('digital_wallets', 'count(`digital_wallet_id`)');

        if($this->user->plan_settings->digital_wallets_limit != -1 && $total_rows >= $this->user->plan_settings->digital_wallets_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        $raw_location_url = $_POST['location_url'] ?? '';
        $raw_website = $_POST['website'] ?? '';

        $_POST['name'] = input_clean($_POST['name'] ?? '', 128);
        $_POST['title'] = input_clean($_POST['title'] ?? '', 128);
        $_POST['subtitle'] = input_clean($_POST['subtitle'] ?? '', 128);
        $_POST['location_url'] = get_url($raw_location_url, 2048, false);
        $_POST['link_id'] = isset($_POST['link_id']) ? (int) $_POST['link_id'] : null;
        $_POST['background_color'] = !empty($_POST['background_color']) && preg_match('/^#[a-f0-9]{6}$/i', $_POST['background_color']) ? $_POST['background_color'] : '#111827';
        $_POST['phone'] = input_clean($_POST['phone'] ?? '', 32);
        $_POST['email'] = input_clean($_POST['email'] ?? '', 320);
        $_POST['website'] = get_url($raw_website, 2048, false);

        $domain_id = null;

        if($_POST['link_id']) {
            $links = (new \Altum\Models\Link())->get_full_links_by_user_id($this->user->user_id);

            if(!isset($links[$_POST['link_id']])) {
                $this->response_error(l('global.error_message.basic'), 401);
            }

            $link = $links[$_POST['link_id']];
            $domain_id = $link->domain_id ?: null;

            if(!$_POST['location_url']) {
                $_POST['location_url'] = $link->full_url;
            }
        } else {
            $_POST['link_id'] = null;
        }

        /* Check for any errors */
        $required_fields = ['name', 'title', 'location_url'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        if(!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $this->response_error(l('global.error_message.invalid_email'), 401);
        }

        if(!empty($raw_website) && empty($_POST['website'])) {
            $this->response_error(l('link.error_message.invalid_location_url'), 401);
        }

        if(!empty($raw_location_url) && empty($_POST['location_url'])) {
            $this->response_error(l('link.error_message.invalid_location_url'), 401);
        }

        /* Image uploads */
        $logo_size_limit = settings()->digital_wallets->logo_size_limit ?? get_max_upload();
        $image_size_limit = settings()->digital_wallets->image_size_limit ?? get_max_upload();

        $logo = \Altum\Uploads::process_upload(null, 'digital_wallets', 'logo', 'logo_remove', $logo_size_limit);
        $image = \Altum\Uploads::process_upload(null, 'digital_wallets', 'image', 'image_remove', $image_size_limit);

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

        do {
            $digital_wallet_hash = bin2hex(random_bytes(16));
        } while(db()->where('digital_wallet_hash', $digital_wallet_hash)->has('digital_wallets'));

        /* Database query */
        $digital_wallet_id = db()->insert('digital_wallets', [
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

        /* Clear the cache */
        cache()->deleteItem('digital_wallets?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $digital_wallet_id,
            'hash' => $digital_wallet_hash,
            'wallet_urls' => [
                'google' => digital_wallets_is_google_wallet_ready() ? url('digital-wallet-add/' . $digital_wallet_hash . '?provider=google') : null,
                'apple' => digital_wallets_is_apple_wallet_ready() ? url('digital-wallet-add/' . $digital_wallet_hash . '?provider=apple') : null,
            ],
            'user_id' => (int) $this->user->user_id,
            'domain_id' => $domain_id ? (int) $domain_id : null,
            'link_id' => $_POST['link_id'] ? (int) $_POST['link_id'] : null,
            'location_url' => $_POST['location_url'],
            'name' => $_POST['name'],
            'pageviews' => 0,
            'settings' => json_decode($settings),
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('digital_wallets', 'count(`digital_wallet_id`)');

        if($this->user->plan_settings->digital_wallets_limit != -1 && $total_rows > $this->user->plan_settings->digital_wallets_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->digital_wallets_limit, mb_strtolower(l('digital_wallets.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $digital_wallet_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $digital_wallet = db()->where('digital_wallet_id', $digital_wallet_id)->where('user_id', $this->user->user_id)->getOne('digital_wallets');

        /* We haven't found the resource */
        if(!$digital_wallet) {
            $this->return_404();
        }

        $digital_wallet->settings = json_decode($digital_wallet->settings ?? '') ?: (object) [];

        $raw_location_url = $_POST['location_url'] ?? $digital_wallet->location_url;
        $raw_website = $_POST['website'] ?? ($digital_wallet->settings->website ?? '');

        $_POST['name'] = input_clean($_POST['name'] ?? $digital_wallet->name, 128);
        $_POST['title'] = input_clean($_POST['title'] ?? ($digital_wallet->settings->title ?? ''), 128);
        $_POST['subtitle'] = input_clean($_POST['subtitle'] ?? ($digital_wallet->settings->subtitle ?? ''), 128);
        $_POST['location_url'] = get_url($raw_location_url, 2048, false);
        $_POST['link_id'] = isset($_POST['link_id']) ? (int) $_POST['link_id'] : $digital_wallet->link_id;
        $_POST['background_color'] = !empty($_POST['background_color']) && preg_match('/^#[a-f0-9]{6}$/i', $_POST['background_color']) ? $_POST['background_color'] : ($digital_wallet->settings->background_color ?? '#111827');
        $_POST['phone'] = input_clean($_POST['phone'] ?? ($digital_wallet->settings->phone ?? ''), 32);
        $_POST['email'] = input_clean($_POST['email'] ?? ($digital_wallet->settings->email ?? ''), 320);
        $_POST['website'] = get_url($raw_website, 2048, false);

        $domain_id = null;

        if($_POST['link_id']) {
            $links = (new \Altum\Models\Link())->get_full_links_by_user_id($this->user->user_id);

            if(!isset($links[$_POST['link_id']])) {
                $this->response_error(l('global.error_message.basic'), 401);
            }

            $link = $links[$_POST['link_id']];
            $domain_id = $link->domain_id ?: null;

            if(!$_POST['location_url']) {
                $_POST['location_url'] = $link->full_url;
            }
        } else {
            $_POST['link_id'] = null;
        }

        /* Check for any errors */
        $required_fields = ['name', 'title', 'location_url'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        if(!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $this->response_error(l('global.error_message.invalid_email'), 401);
        }

        if(isset($_POST['website']) && !empty($raw_website) && empty($_POST['website'])) {
            $this->response_error(l('link.error_message.invalid_location_url'), 401);
        }

        if(isset($_POST['location_url']) && !empty($raw_location_url) && empty($_POST['location_url'])) {
            $this->response_error(l('link.error_message.invalid_location_url'), 401);
        }

        /* Image uploads */
        $logo_size_limit = settings()->digital_wallets->logo_size_limit ?? get_max_upload();
        $image_size_limit = settings()->digital_wallets->image_size_limit ?? get_max_upload();

        $logo = \Altum\Uploads::process_upload($digital_wallet->settings->logo ?? null, 'digital_wallets', 'logo', 'logo_remove', $logo_size_limit);
        $image = \Altum\Uploads::process_upload($digital_wallet->settings->image ?? null, 'digital_wallets', 'image', 'image_remove', $image_size_limit);

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
        db()->where('digital_wallet_id', $digital_wallet->digital_wallet_id)->where('user_id', $this->user->user_id)->update('digital_wallets', [
            'domain_id' => $domain_id,
            'link_id' => $_POST['link_id'],
            'location_url' => $_POST['location_url'],
            'name' => $_POST['name'],
            'settings' => $settings,
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('digital_wallets?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $digital_wallet->digital_wallet_id,
            'hash' => $digital_wallet->digital_wallet_hash,
            'wallet_urls' => [
                'google' => digital_wallets_is_google_wallet_ready() ? url('digital-wallet-add/' . $digital_wallet->digital_wallet_hash . '?provider=google') : null,
                'apple' => digital_wallets_is_apple_wallet_ready() ? url('digital-wallet-add/' . $digital_wallet->digital_wallet_hash . '?provider=apple') : null,
            ],
            'user_id' => (int) $digital_wallet->user_id,
            'domain_id' => $domain_id ? (int) $domain_id : null,
            'link_id' => $_POST['link_id'] ? (int) $_POST['link_id'] : null,
            'location_url' => $_POST['location_url'],
            'name' => $_POST['name'],
            'pageviews' => (int) $digital_wallet->pageviews,
            'settings' => json_decode($settings),
            'last_datetime' => get_date(),
            'datetime' => $digital_wallet->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $digital_wallet_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $digital_wallet = db()->where('digital_wallet_id', $digital_wallet_id)->where('user_id', $this->user->user_id)->getOne('digital_wallets');

        /* We haven't found the resource */
        if(!$digital_wallet) {
            $this->return_404();
        }

        (new \Altum\Models\DigitalWallets())->delete($digital_wallet->digital_wallet_id);

        http_response_code(200);
        die();

    }

}
