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

use Altum\Models\User;

defined('ALTUMCODE') || die();

class Pixel extends Controller {

    public function index() {
        $seconds_to_cache = settings()->notifications->pixel_cache;
        header('Content-Type: application/javascript');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $seconds_to_cache) . ' GMT');
        header('Pragma: cache');
        header('Cache-Control: max-age=' . $seconds_to_cache);

        /* Check against bots */
        $CrawlerDetect = new \Jaybizzle\CrawlerDetect\CrawlerDetect();

        if($CrawlerDetect->isCrawler()) {
            die("console.log('" . settings()->main->title . " (" . SITE_URL. "): Bot usage has been detected, pixel stopped from executing.')");
        }

        $pixel_key = isset($this->params[0]) ? input_clean($this->params[0]) : null;
        $date = get_date();

        /* Get the details of the campaign from the database */
        $campaign = (new \Altum\Models\Campaign())->get_campaign_by_pixel_key($pixel_key);

        /* Make sure the campaign has access */
        if(!$campaign) {
            die("console.log('" . settings()->main->title . " (" . SITE_URL. "): No campaign found for this pixel.')");
        }

        if(!$campaign->is_enabled) {
            die("console.log('" . settings()->main->title . " (" . SITE_URL. "): Campaign disabled.')");
        }

        /* Make sure to get the user data and confirm the user is ok */
        $user = (new \Altum\Models\User())->get_user_by_user_id($campaign->user_id);

        if(!$user) {
            die("console.log('" . settings()->main->title . " (" . SITE_URL. "): Campaign owner not found.')");
        }

        if($user->status != 1) {
            die("console.log('" . settings()->main->title . " (" . SITE_URL. "): Campaign owner is disabled.')");
        }

        /* Check for a custom domain */
        if(isset(\Altum\Router::$data['domain']) && $campaign->domain_id != \Altum\Router::$data['domain']->domain_id) {
            die("console.log('" . settings()->main->title . " (" . SITE_URL. "): Domain id mismatch.')");
        }

        /* Ignore excluded ips */
        $excluded_ips = array_flip($user->preferences->excluded_ips ?? []);
        if(isset($excluded_ips[get_ip()])) {
            die("console.log('" . settings()->main->title . " (" . SITE_URL . "): Tracking disabled for this IP.')");
        }

        /* Process the plan of the user */
        (new User())->process_user_plan_expiration_by_user($user);

        /* Make sure that the user didnt exceed the current plan */
        if($user->plan_settings->notifications_impressions_limit != -1 && $user->current_month_notifications_impressions >= $user->plan_settings->notifications_impressions_limit) {
            die("console.log('" . settings()->main->title . " (" . SITE_URL. "): Notification impressions limit exceeded.')");
        }

        /* Set the default language depending on the user */
        \Altum\Language::set_by_name($user->language);

        /* Get default settings for the notifications */
        $enabled_notifications = require APP_PATH . 'includes/enabled_notifications.php';

        /* Detect the location */
        try {
            $maxmind = (get_maxmind_reader_city())->get(get_ip());
        } catch(\Exception $exception) {
            /* :) */
        }
        $continent_code = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['code'] : null;
        $country_code = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
        $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;

        /* Detect extra details about the user */
        $whichbrowser = get_whichbrowser();
        $browser_name = $whichbrowser->browser->name ?? null;
        $os_name = $whichbrowser->os->name ?? null;
        $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;

        /* Branding processing */
        $replacers = [
            '{{URL}}' => url(),
            '{{WEBSITE_TITLE}}' => settings()->main->title,
            '{{AFFILIATE_URL_TAG}}' => \Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled ? '?ref=' . $user->referral_key : null,
        ];

        settings()->notifications->branding = str_replace(
            array_keys($replacers),
            array_values($replacers),
            settings()->notifications->branding
        );

        /* Get all the available notifications for this campaign */
        $notifications_result = database()->query("
            SELECT `notifications`.*
            FROM 
                `notifications`
            WHERE 
                `notifications`.`user_id` = {$user->user_id} AND
                `notifications`.`campaign_id` = {$campaign->campaign_id} AND
                `notifications`.`is_enabled` = 1 
        ");

        /* Loop over everything, get extra data if needed and return for the view to use */
        $notifications = [];

        while($notification = $notifications_result->fetch_object()) {
            /* Make sure the notification type is still available */
            if(!isset($enabled_notifications[$notification->type])) {
                continue;
            }

            /* Parse JSON */
            $notification->settings = json_decode($notification->settings ?? '');

            /* Default notification settings merging */
            $notification->settings = (object) array_merge((array) $enabled_notifications[$notification->type], (array) $notification->settings ?? '');

            /* Get the custom branding details */
            $notification->branding = json_decode($campaign->branding);

            /* Targeting */
            if($continent_code && count($notification->settings->display_continents ?? []) && !in_array($continent_code, $notification->settings->display_continents ?? [])) {
                continue;
            }

            if($country_code && count($notification->settings->display_countries ?? []) && !in_array($country_code, $notification->settings->display_countries ?? [])) {
                continue;
            }

            if($city_name && count($notification->settings->display_cities ?? []) && !in_array($city_name, $notification->settings->display_cities ?? [])) {
                continue;
            }

            if($os_name && count($notification->settings->display_operating_systems ?? []) && !in_array($os_name, $notification->settings->display_operating_systems ?? [])) {
                continue;
            }

            if($browser_language && count($notification->settings->display_languages ?? []) && !in_array($browser_language, $notification->settings->display_languages ?? [])) {
                continue;
            }

            if($browser_name && count($notification->settings->display_browsers ?? []) && !in_array($browser_name, $notification->settings->display_browsers ?? [])) {
                continue;
            }

            /* Scheduling */
            if(
                $notification->settings->schedule && !empty($notification->settings->start_date) && !empty($notification->settings->end_date) &&
                (
                    \Altum\Date::get('', null) < \Altum\Date::get($notification->settings->start_date, null, \Altum\Date::$default_timezone) ||
                    \Altum\Date::get('', null) > \Altum\Date::get($notification->settings->end_date, null, \Altum\Date::$default_timezone)
                )
            ) {
                continue;
            }

            /* Translations processing */
            if(!empty($notification->settings->translations)) {
                foreach($notification->settings->translations as $translation_key => $translation_array) {
                    $translation_array = (array) $translation_array;

                    if(!empty($translation_array) && array_key_exists($browser_language, $translation_array)) {
                        $notification->settings->{$translation_key} = $translation_array[$browser_language];
                    }
                }
            }

            /* Extra details and data gathering if needed */
            switch($notification->type) {
                case 'inline_conversions':
                case 'conversions':

                    $order_by = ($notification->settings->order ?? 'descending') == 'descending' ? '`datetime` DESC' : 'RAND()';

                    $result = database()->query("
                        SELECT
                            `data`, `location`, `datetime`
                        FROM
                            `track_conversions`
                        WHERE
                            `notification_id` = {$notification->notification_id}
                        ORDER BY
                            {$order_by}
                        LIMIT 
                            {$notification->settings->conversions_count}
                    ");

                    /* If we do not have any conversions */
                    if(!$result->num_rows) {
                        /* Default value for the person, if the name is not found later */
                        $notification->title = $notification->settings->title;
                        $notification->description = $notification->settings->description;

                        $notifications[] = $notification;
                    } else {

                        $i = 0;

                        /* Save the original value for the delay */
                        $notification->settings->display_trigger_value_original = $notification->settings->display_trigger_value;
                        $in_between_delay = ($notification->settings->display_duration == -1 ? 0 : $notification->settings->display_duration) + $notification->settings->in_between_delay;
                        $notifications_to_add = [];

                        while($conversion = $result->fetch_object()) {
                            /* Default value for the person, if the name is not found later */
                            $notification->title = $notification->settings->title;
                            $notification->description = $notification->settings->description;
                            $notification->image = $notification->settings->image;
                            $notification->image_alt = $notification->settings->image_alt;
                            $notification->url = $notification->settings->url;

                            /* Decode the conversion data */
                            $conversion->data = json_decode($conversion->data, true);

                            if($conversion->data) {
                                /* Try to get the location data parsed if possible */
                                $location = json_decode($conversion->location ?? '', true) ?? [];
                                $conversion->data = array_merge($location, $conversion->data);

                                foreach(['title', 'description', 'image', 'url'] as $key) {
                                    /* Get all available variables from the conversion who */
                                    preg_match_all(
                                        '/{([a-zA-Z0-9_\-\.]+)}+/',
                                        $notification->settings->{$key},
                                        $matches
                                    );

                                    foreach($matches[1] as $value) {
                                        $notification->{$key} = str_replace(
                                            '{' . $value . '}',
                                            htmlspecialchars($conversion->data[$value] ?? '', ENT_QUOTES, 'UTF-8'),
                                            $notification->{$key}
                                        );
                                    }
                                }


                                /* Set the date of the conversion */
                                $notification->last_action_date = $conversion->datetime;

                                /* Change the delay of the notifications if needed */
                                if(in_array($notification->settings->display_trigger, ['delay', 'target_visible'])) {

                                    $notification->settings->display_trigger_value = $i == 0 ?
                                        $notification->settings->display_trigger_value :
                                        $notification->settings->display_trigger_value_original + ($i * $in_between_delay);
                                }

                                /* Hackish workaround */
                                $notification_settings = clone $notification->settings;
                                $notification_to_add = clone $notification;
                                $notification_to_add->settings = $notification_settings;

                                /* Add to the notifications array */
                                $notifications_to_add[] = $notification_to_add;

                                $i++;
                            }
                        }

                        foreach($notifications_to_add as $notification_to_add) {
                            $notification_to_add->settings->infinite_rotation_count = count($notifications_to_add);
                            $notifications[] = $notification_to_add;
                        }

                    }

                    break;

                case 'inline_conversions_counter':
                case 'conversions_counter':

                    $date_start = (new \DateTime())->modify('-' . $notification->settings->last_activity . ' hour')->format('Y-m-d H:i:s');

                    $notification->counter = database()->query("
                        SELECT
                            COUNT(`id`) AS `total`
                        FROM
                            `track_conversions`
                        WHERE
                            `notification_id` = {$notification->notification_id}
                        AND (`datetime` BETWEEN '{$date_start}' AND '{$date}')
                    ")->fetch_object()->total;

                    break;

                case 'inline_rating_summary':
                case 'rating_summary':

                    $result = database()->query("
                        SELECT
                            `data`
                        FROM
                            `track_conversions`
                        WHERE
                            `notification_id` = {$notification->notification_id}
                    ");

                    $rating_sum = 0;
                    $total_reviews = 0;

                    while($review = $result->fetch_object()) {
                        $review->data = json_decode($review->data, true);

                        if($review->data && isset($review->data['stars'])) {
                            $stars = (int) $review->data['stars'];

                            if($stars >= 1 && $stars <= 5) {
                                $rating_sum += $stars;
                                $total_reviews++;
                            }
                        }
                    }

                    $notification->has_rating_data = $total_reviews > 0;
                    $notification->average_rating = $total_reviews ? round($rating_sum / $total_reviews, 1) : $notification->settings->average_rating;
                    $notification->total_reviews = $total_reviews ?: $notification->settings->reviews_count;

                    break;

                case 'live_counter':
                case 'live_counter_bar':
                case 'inline_live_counter':

                    /* Count live visitors */
                    $date_start = (new \DateTime())->modify('-' . $notification->settings->last_activity . ' minute')->format('Y-m-d H:i:s');
                    $path = !empty($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH) : null;

                    if($path) {
                        $path_query = ($notification->settings->activity_type ?? 'all') == 'all' ? null : 'AND `path_hashed` = \'' . md5($path) . '\'';
                    } else {
                        $path_query = null;
                    }

                    $notification->counter = database()->query("
                        SELECT
                            COUNT(DISTINCT `ip_binary`) AS `total`
                        FROM
                            `track_logs`
                        WHERE
                            `campaign_id` = {$notification->campaign_id}
                            {$path_query}
                            AND `datetime` > '{$date_start}'
                    ")->fetch_object()->total;

                    break;

                case 'inline_reviews':
                case 'reviews':

                    $order_by = ($notification->settings->order ?? 'descending') == 'descending' ? '`datetime` DESC' : 'RAND()';

                    $result = database()->query("
                        SELECT
                            `data`
                        FROM
                            `track_conversions`
                        WHERE
                            `notification_id` = {$notification->notification_id}
                        ORDER BY
                            {$order_by}
                        LIMIT {$notification->settings->reviews_count}
                    ");

                    /* If we do not have any added reviews */
                    if(!$result->num_rows) {
                        $notifications[] = $notification;
                    } else {

                        $i = 0;

                        /* Save the original value for the delay */
                        $notification->settings->display_trigger_value_original = $notification->settings->display_trigger_value;
                        $in_between_delay = ($notification->settings->display_duration == -1 ? 0 : $notification->settings->display_duration) + $notification->settings->in_between_delay;
                        $notifications_to_add = [];

                        while($review = $result->fetch_object()) {

                            /* Decode the data */
                            $review->data = json_decode($review->data, true);

                            if($review->data) {
                                $notification->settings->title = $review->data['title'];
                                $notification->settings->description = $review->data['description'];
                                $notification->settings->image = !empty($review->data['image']) ? $review->data['image'] : $notification->settings->image;
                                $notification->settings->stars = (int) $review->data['stars'];

                                /* Change the delay of the notifications if needed */
                                if(in_array($notification->settings->display_trigger, ['delay', 'target_visible'])) {

                                    $notification->settings->display_trigger_value = $i == 0 ?
                                        $notification->settings->display_trigger_value :
                                        $notification->settings->display_trigger_value_original + ($i * $in_between_delay);
                                }

                                /* Hackish */
                                $notification_settings = clone $notification->settings;
                                $notification_to_add = clone $notification;
                                $notification_to_add->settings = $notification_settings;

                                /* Add to the notifications array */
                                $notifications_to_add[] = $notification_to_add;

                                $i++;
                            }
                        }

                        foreach($notifications_to_add as $notification_to_add) {
                            $notification_to_add->settings->infinite_rotation_count = count($notifications_to_add);
                            $notifications[] = $notification_to_add;
                        }

                    }

                    break;
            }


            /* Conversions / Reviews adds the notifications by itself */
            if(!in_array($notification->type, ['inline_conversions', 'conversions', 'inline_reviews', 'reviews'])) {
                $notifications[] = $notification;
            }
        }

        /* Main View */
        $data = [
            'notifications'         => $notifications,
            'pixel_key'             => $pixel_key,
            'campaign'              => $campaign,
            'user'                  => $user
        ];

        $view = new \Altum\View('pixel/index', (array) $this);

        $view_data = $view->run($data);

        /* remove only the first <script> */
        $view_data = preg_replace('/<script>/', '', $view_data, 1);

        /* remove only the last </script> */
        $view_data = preg_replace('/<\/script>(?!.*<\/script>)/s', '', $view_data, 1);

        echo $view_data;

    }

}
