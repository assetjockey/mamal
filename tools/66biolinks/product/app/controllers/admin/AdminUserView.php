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

use Altum\Models\Plan;
use Altum\Response;

defined('ALTUMCODE') || die();

class AdminUserView extends Controller {

    public function index() {

        $user_id = (isset($this->params[0])) ? (int) $this->params[0] : null;

        /* Check if resource exists */
        if(!$user = db()->where('user_id', $user_id)->getOne('users')) {
            redirect('admin/users');
        }

        /* Get the current plan details */
        $user->plan = (new Plan())->get_plan_by_id($user->plan_id);

        /* Check if its a custom plan */
        if($user->plan_id == 'custom') {
            $user->plan->settings = $user->plan_settings;
        }

        $user->billing = json_decode($user->billing ?? '');

        /* Get lat long of user for map card */
        /* Detect the location */
        try {
            if($user->ip) {
                $maxmind = (new \MaxMind\Db\Reader(APP_PATH . 'includes/GeoLite2-City.mmdb'))->get($user->ip);
            }
        } catch(\Exception $exception) {
            /* :) */
        }
        /* Detect extra details about the user */
        $user_location = [
            'latitude' => $maxmind['location']['latitude'] ?? null,
            'longitude' => $maxmind['location']['longitude'] ?? null,
        ];

        /* Total earned */
        $payments_total_earned = in_array(settings()->license->type, ['Extended License', 'extended']) ? db()->where('user_id', $user->user_id)->getValue('payments', 'sum(`total_amount_default_currency`)') : 0;

        /* Main View */
        $data = [
            'user' => $user,
            'user_location' => $user_location,
            'payments_total_earned' => $payments_total_earned,
        ];

        $view = new \Altum\View('admin/user-view/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function get_stats_ajax() {

        session_write_close();

        if($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw_404();
        }

        set_time_limit(0);

        $user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : null;

        if(!$user_id) {
            throw_404();
        }

        $biolink_links = db()->where('user_id', $user_id)->where('type', 'biolink')->getValue('links', 'count(`link_id`)');
        $shortened_links = db()->where('user_id', $user_id)->where('type', 'link')->getValue('links', 'count(`link_id`)');
        $file_links = db()->where('user_id', $user_id)->where('type', 'file')->getValue('links', 'count(`link_id`)');
        $vcard_links = db()->where('user_id', $user_id)->where('type', 'vcard')->getValue('links', 'count(`link_id`)');
        $event_links = db()->where('user_id', $user_id)->where('type', 'event')->getValue('links', 'count(`link_id`)');
        $static_links = db()->where('user_id', $user_id)->where('type', 'static')->getValue('links', 'count(`link_id`)');
        $projects = db()->where('user_id', $user_id)->getValue('projects', 'count(`project_id`)');
        $pixels = db()->where('user_id', $user_id)->getValue('pixels', 'count(`pixel_id`)');
        $splash_pages = db()->where('user_id', $user_id)->getValue('splash_pages', 'count(`splash_page_id`)');
        $qr_codes = db()->where('user_id', $user_id)->getValue('qr_codes', 'count(`qr_code_id`)');
        $domains = db()->where('user_id', $user_id)->getValue('domains', 'count(`domain_id`)');
        $payments = in_array(settings()->license->type, ['Extended License', 'extended']) ? db()->where('user_id', $user_id)->getValue('payments', 'count(`id`)') : 0;

        /* Newsletters plugin */
        $newsletters = 0;
        $newsletter_subscribers = 0;
        if(\Altum\Plugin::is_active('newsletters')) {
            $newsletters = db()->where('user_id', $user_id)->getValue('newsletters', 'count(`newsletter_id`)');
            $newsletter_subscribers = db()->where('user_id', $user_id)->getValue('newsletter_subscribers', 'count(`newsletter_subscriber_id`)');
        }

        $signatures = 0;
        if(\Altum\Plugin::is_active('email-signatures')) {
            $signatures = db()->where('user_id', $user_id)->getValue('signatures', 'count(`signature_id`)');
        }

        $digital_wallets = 0;
        if(\Altum\Plugin::is_active('digital-wallets')) {
            $digital_wallets = db()->where('user_id', $user_id)->getValue('digital_wallets', 'count(`digital_wallet_id`)');
        }

        $images = $transcriptions = $syntheses = $chats = 0;
        if(\Altum\Plugin::is_active('aix')) {
            $images = db()->where('user_id', $user_id)->getValue('images', 'count(`image_id`)');
            $transcriptions = db()->where('user_id', $user_id)->getValue('transcriptions', 'count(`transcription_id`)');
            $syntheses = db()->where('user_id', $user_id)->getValue('syntheses', 'count(`synthesis_id`)');
            $chats = db()->where('user_id', $user_id)->getValue('chats', 'count(`chat_id`)');
        }

        $data = [
            'biolink_links' => $biolink_links,
            'shortened_links' => $shortened_links,
            'file_links' => $file_links,
            'vcard_links' => $vcard_links,
            'event_links' => $event_links,
            'static_links' => $static_links,
            'projects' => $projects,
            'pixels' => $pixels,
            'splash_pages' => $splash_pages,
            'qr_codes' => $qr_codes,
            'domains' => $domains,
            'payments' => $payments,
            'newsletters' => $newsletters,
            'newsletter_subscribers' => $newsletter_subscribers,
            'signatures' => $signatures,
            'digital_wallets' => $digital_wallets,
            'images' => $images,
            'transcriptions' => $transcriptions,
            'syntheses' => $syntheses,
            'chats' => $chats,
        ];

        Response::json('', 'success', $data);

    }

}
