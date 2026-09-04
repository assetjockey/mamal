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

defined('ALTUMCODE') || die();

class Newsletter extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('newsletters')) {
            throw_404();
        }

        function return_newsletter_image() {
            header('Content-Type: image/gif');
            echo base64_decode('R0lGODlhAQABAIAAAP///wAAACwAAAAAAQABAAACAkQBADs=');
            die();
        }

        if(!isset($_GET['id'])) {
            throw_404();
        }

        /* Decode the base64 id */
        $id = base64_decode($_GET['id']);

        /* Parse the parameters */
        parse_str($id, $parameters);

        /* Make sure all parameters are present */
        if(!isset($parameters['newsletter_id'], $parameters['newsletter_subscriber_id'])) {
            throw_404();
        }

        $parameters['newsletter_id'] = (int) $parameters['newsletter_id'];
        $parameters['newsletter_subscriber_id'] = (int) $parameters['newsletter_subscriber_id'];
        $url = isset($_GET['url']) ? get_url($_GET['url']) : null;

        /* Make sure the newsletter exists properly */
        if(!$newsletter = db()->where('newsletter_id', $parameters['newsletter_id'])->getOne('newsletters')) {
            throw_404();
        }

        if(!in_array($newsletter->status, ['sent', 'processing'])) {
            throw_404();
        }

        $newsletter->subscribers_ids = json_decode($newsletter->subscribers_ids ?? '[]');

        if(!$newsletter_subscriber = db()->where('newsletter_subscriber_id', $parameters['newsletter_subscriber_id'])->where('user_id', $newsletter->user_id)->getOne('newsletter_subscribers', ['newsletter_subscriber_id', 'name', 'email'])) {
            throw_404();
        }

        /* Make sure the subscriber is included in the newsletter */
        if(!in_array($newsletter_subscriber->newsletter_subscriber_id, $newsletter->subscribers_ids)) {
            throw_404();
        }

        /* No statistics needed */
        /* Keep old email links working */
        if(!settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false) || !(settings()->newsletters->statistics_is_enabled ?? true)) {
            if($url) {
                header('Location: ' . $url); die();
            }

            return_newsletter_image();
        }

        /* Prepare for database insertion */
        $type = $url ? 'click' : 'view';
        $target = $url ?? null;

        /* Make sure the log was not already created */
        $newsletter_statistic = db()
            ->where('newsletter_id', $parameters['newsletter_id'])
            ->where('newsletter_subscriber_id', $parameters['newsletter_subscriber_id'])
            ->where('type', $type)
            ->where('target', $target)
            ->getValue('newsletters_statistics', 'id');

        if($newsletter_statistic && $type == 'view') {
            return_newsletter_image();
        }

        if($newsletter_statistic && $type == 'click') {
            header('Location: ' . $url); die();
        }

        if($type == 'click') {
            /* Prepare the same content that was sent */
            $vars = [
                '{{SUBSCRIBER:NAME}}' => $newsletter_subscriber->name ?: $newsletter_subscriber->email,
                '{{SUBSCRIBER:EMAIL}}' => $newsletter_subscriber->email,
            ];

            $email_template = get_email_template(
                $vars,
                $newsletter->subject,
                $vars,
                convert_editorjs_json_to_html($newsletter->content)
            );

            /* Validate tracked links */
            preg_match_all('/<a\s+[^>]*href="([^"]+)"/i', $email_template->body, $matches);
            $valid_urls = array_map(fn($url) => html_entity_decode($url, ENT_QUOTES, 'UTF-8'), $matches[1] ?? []);

            if(!in_array($url, $valid_urls)) {
                throw_404();
            }
        }

        /* Insert log and update stats */
        db()->insert('newsletters_statistics', [
            'user_id' => $newsletter->user_id,
            'newsletter_id' => $parameters['newsletter_id'],
            'newsletter_subscriber_id' => $parameters['newsletter_subscriber_id'],
            'type' => $type,
            'target' => $target,
            'datetime' => get_date(),
        ]);

        switch($type) {
            case 'view':
                db()->where('newsletter_id', $parameters['newsletter_id'])->update('newsletters', [
                    'views' => db()->inc()
                ]);

                return_newsletter_image();
                break;

            case 'click':
                db()->where('newsletter_id', $parameters['newsletter_id'])->update('newsletters', [
                    'clicks' => db()->inc()
                ]);

                header('Location: ' . $url);
                break;
        }

        die();
    }

}
