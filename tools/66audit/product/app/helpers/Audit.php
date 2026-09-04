<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
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

namespace Altum\Helpers;

use Altum\Alerts;

defined('ALTUMCODE') || die();

class Audit {

    public static function process_request($url) {

        /* Prepare curl opts */
        $curl_opts = [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_RETURNTRANSFER => true,
        ];

        /* Proxies */
        if(settings()->audits->proxies_is_enabled && count(settings()->audits->proxies ?? [])) {
            $enabled_proxies = array_values(array_filter(settings()->audits->proxies ?? [], fn($proxy) => !empty($proxy->is_enabled)));

            if(!empty($enabled_proxies)) {
                $proxy_type_map = [
                    'http' => CURLPROXY_HTTP,
                    'socks5' => CURLPROXY_SOCKS5,
                    'socks5_rdns' => CURLPROXY_SOCKS5_HOSTNAME,
                ];

                /* Give a chance for direct connection as a candidate */
                $candidates = $enabled_proxies;

                if(!settings()->audits->proxies_exclusive) {
                    $candidates[] = null; /* Direct connection */
                }

                /* Gamble */
                $proxy = $candidates[array_rand($candidates)];
                $use_proxy = !is_null($proxy);

                /* Add proxy if needed or remove proxy */
                if($use_proxy) {
                    $curl_opts[CURLOPT_PROXY] = $proxy->address;
                    $curl_opts[CURLOPT_PROXYTYPE] = $proxy_type_map[$proxy->type] ?? CURLPROXY_HTTP;
                } else {
                    $curl_opts[CURLOPT_PROXY] = '';
                }
            }
        }

        // Testing only
        //\Unirest\Request::proxy('51.96.154.142', 8080, CURLPROXY_HTTP);

        /* Set timeout */
        \Unirest\Request::timeout(settings()->audits->request_timeout);

        /* Set follow redirects */
        \Unirest\Request::curlOpts($curl_opts);

        /* Verify SSL */
        \Unirest\Request::verifyPeer(false);

        /* Prepare request headers */
        $request_headers = [];

        /* Set custom user agent */
        if(settings()->audits->user_agent) {
            $request_headers['User-Agent'] = settings()->audits->user_agent;
        }

        /* Accept compressed */
        $request_headers['Accept-Encoding'] = settings()->audits->accept_encoding ?? 'gzip, deflate';

        /* Send the request */
        $response = \Unirest\Request::get($url, $request_headers);

        /* Clear custom settings */
        \Unirest\Request::clearCurlOpts();

        if($response->code != 200) {
            throw new \Exception(sprintf(l('audits.error_message.invalid_response_code'), $response->code ?? l('global.unknown')));
        }

        return $response;
    }

    public static function process_request_response($url, $response, $raw_html = null) {

        /* Temp fix for redirect issue */
        $is_api_request = \Altum\Router::$path == 'api';

        /* Get info after the request */
        $info = \Unirest\Request::getInfo();

        /* Get the amount of redirects */
        $total_redirects = $info['redirect_count'] ?? 0;

        /* Parse the requested URL */
        $parsed_url = parse_url($url);

        /* Resolved url */
        $resolved_url = $url;

        /* Normalize headers */
        $normalized_headers = is_array($response->headers) ? array_change_key_case($response->headers, CASE_LOWER) : [];

        /* Detect any potential change */
        $is_external_redirected = 0;

        if(isset($normalized_headers['location'])) {
            $normalized_headers['location'] = is_array($normalized_headers['location']) ? end($normalized_headers['location']) : $normalized_headers['location'];
            $parsed_resolved_url = parse_url($normalized_headers['location']);

            if($parsed_url['host'] != $parsed_resolved_url['host']) {
                $resolved_url = get_url($normalized_headers['location'], 256, false);

                /* Detect external resolved url */
                if(get_domain_from_host($parsed_url['host']) != get_domain_from_host($parsed_resolved_url['host'])){
                    $is_external_redirected = 1;

                    return [
                        'url' => $url,
                        'parsed_url' => $parsed_url,
                        'is_external_redirected' => $is_external_redirected,
                        'total_redirects' => $total_redirects,
                        'resolved_url' => $resolved_url,
                    ];
                }
            }
        }

        /* host without www enforce */
        $parsed_url['host'] = get_domain_from_host($parsed_url['host']);

        /* Redirection location in case its needed */
        $error_redirect = is_logged_in() ? 'dashboard' : 'seo';

        /* Check for the proper content type */
        $content_type = is_array($normalized_headers['content-type'] ?? null) ? end($normalized_headers['content-type']) : $normalized_headers['content-type'] ?? null;

		/* Check for non html pages */
		if(!preg_match('~^text/html\b~i', $content_type)) {
            if($is_api_request) {
                \Altum\Response::jsonapi_error([[
                    'title' => l('audits.error_message.invalid_html'),
                    'status' => '401'
                ]]);
            } else {
                Alerts::add_field_error('url', l('audits.error_message.invalid_html'));
                redirect($error_redirect);
            }
		}

		/* Trim response */
        $html = $raw_html ?: $response->raw_body;
        $html = trim($html);

        if(mb_detect_encoding($html, 'UTF-8', true) === false) {
            $html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
        }

        /* Start parsing page content */
        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);

        if(!$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            if($is_api_request) {
                \Altum\Response::jsonapi_error([[
                    'title' => l('audits.error_message.invalid_html'),
                    'status' => '401'
                ]]);
            } else {
                Alerts::add_field_error('url', l('audits.error_message.invalid_html'));
                redirect($error_redirect);
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        /* IS SEO Friendly URL */
        $is_seo_friendly_url = true;

        if(!preg_match('/^[a-z0-9\-\/]*$/', $parsed_url['path'] ?? '')) {
            $is_seo_friendly_url = false;
        }

        /* Count HTTP requests */
        $http_requests_data = [
            'images' => 0,
            'css' => 0,
            'js' => 0,
            'audios' => 0,
            'videos' => 0,
            'iframes' => 0,
        ];

        /* Response data */
        $response_status_code = $info['http_code'];
        $page_size = mb_strlen($html);
        $download_size = $info['size_download'];
        $redirect_count = $info['redirect_count'];
        $http_protocol = $info['protocol'];
        $http_version = $info['http_version'];

        /* Timing data */
        $response_time = $info['total_time'] * 1000;
        $ttfb = $info['pretransfer_time'];
        $average_download_speed = $info['speed_download'];

        /* Security */
        $is_https = (int) ($parsed_url['scheme'] == 'https');
        $ssl_certificate = get_website_certificate($parsed_url['host']);
        $is_ssl_valid = (int) ($ssl_certificate && $ssl_certificate['is_valid']);

        /* General tags */
        $dom_size = $dom->getElementsByTagName('*')->count();
        $doctype = $dom->doctype->name ?? null;
        $language = trim($dom->getElementsByTagName('html')->item(0)?->getAttribute('lang') ?? '');
        $title = string_truncate(trim($dom->getElementsByTagName('title')->item(0)?->textContent ?? ''), 510);

        /* Deprecated HTML tags */
        $deprecated_html_tags = [];
        foreach(get_deprecated_html_tags_array() as $html_tag) {
            foreach($dom->getElementsByTagName($html_tag) as $tag) {
                $deprecated_html_tags[$html_tag] = ($deprecated_html_tags[$html_tag] ?? 0) + 1;
            }
        }

        /* Meta tags */
        $meta_tags = $dom->getElementsByTagName('meta');
        $meta_description = null;
        $meta_keywords = null;
        $meta_charset = null;
        $meta_robots = [];
        $meta_viewport = null;
        $meta_googlebot = null;
        $meta_theme_color = null;
        $meta_refresh = null;

        /* Inline CSS */
        $inline_css = [];
        foreach($dom->getElementsByTagName('*') as $element) {
            if(!empty($element->getAttribute('style'))) {
                $inline_css[] = [
                    'style' => $element->getAttribute('style'),
                    'tag' => $element->tagName,
                ];
            }
        }

        /* Opengraph */
        $opengraph = [];

        /* Process meta tags */
        foreach($meta_tags as $meta) {
            $name = strtolower($meta->getAttribute('name'));
            $charset = strtolower($meta->getAttribute('charset'));
            $property = strtolower($meta->getAttribute('property'));
            $http_equiv = strtolower($meta->getAttribute('http-equiv') ?? '');

            if($name == 'description') {
                $meta_description = string_truncate($meta->getAttribute('content'), 510); continue;
            }

            if($name == 'keywords') {
                $meta_keywords = string_truncate($meta->getAttribute('content'), 510); continue;
            }

            if(!empty($charset)) {
                $meta_charset = $meta->getAttribute('charset'); continue;
            }

            if($name == 'viewport') {
                $meta_viewport = $meta->getAttribute('content'); continue;
            }

            if($name == 'robots') {
                $content = $meta->getAttribute('content');
                $content_array = array_map('trim', explode(',', $content));
                $meta_robots = array_filter(array_merge($meta_robots, $content_array));
                continue;
            }

            if($name == 'googlebot') {
                $meta_googlebot = $meta->getAttribute('content'); continue;
            }

            if($name == 'theme-color') {
                $meta_theme_color = $meta->getAttribute('content'); continue;
            }

            if(str_starts_with($property, 'og:')) {
                $opengraph[$property] = $meta->getAttribute('content'); continue;
            }

            if($http_equiv == 'refresh') {
                $meta_refresh = $meta->getAttribute('content');
            }
        }

        /* Link tags */
        $link_tags = $dom->getElementsByTagName('link');

        $favicon = null;
        $canonical = null;
        $manifest = null;

        /* Headings */
        $headings = [];
        foreach(['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $heading_type) {
            if(!isset($headings[$heading_type])) {
                $headings[$heading_type] = [];
            }

            foreach($dom->getElementsByTagName($heading_type) as $heading) {
                $headings[$heading_type][] = trim($heading->textContent);
            }
        }

        /* Forms processing */
        $unsafe_forms = [];

        foreach($dom->getElementsByTagName('form') as $form) {
            $action = $form->getAttribute('action');

            if(!empty($action) && $is_https && str_starts_with($action, 'http://')) {
                $name = $form->getAttribute('name');
                $id = $form->getAttribute('id');
                $method = $form->getAttribute('method');

                $unsafe_forms[] = [
                    'action' => $action,
                    'id' => $id ?? '',
                    'method' => $method ?? '',
                    'name' => $name ?? '',
                ];
            }
        }

        /* Links processing */
        $links = [];
        $external_links_count = 0;
        $internal_links_count = 0;
        $unsafe_external_links_count = 0;
        $social_links = [];
        $special_protocols = ['mailto', 'tel', 'sms', 'facetime'];

        foreach($dom->getElementsByTagName('a') as $a) {
            $href = $a->getAttribute('href');
            $is_internal = true;

            $type = 'url';

            /* Special protocol scheme mailto:, , etc */
            foreach($special_protocols as $protocol) {
                if(str_starts_with($href, $protocol . ':')) {
                    $type = $protocol;
                    break;
                }
            }

            /* Hotlinks check # */
            if($type == 'url' && str_starts_with($href, '#')) {
                $type = 'hotlink';
            }

            if($type == 'url') {
                $href = get_full_url_from_relative_paths($url, $href);
                $href_host = parse_url($href, PHP_URL_HOST);

                if($href_host != $parsed_url['host']) {
                    $is_internal = false;
                }
            }

            $rel = $a->getAttribute('rel');
            $is_noreferrer = false;
            $is_noopener = false;
            if($rel) {
                if(str_contains($rel, 'noreferrer')) {
                    $is_noreferrer = true;
                }

                if(str_contains($rel, 'noopener')) {
                    $is_noopener = true;
                }
            }

            $link = [
                'href' => $href,
                'title' => trim($a->getAttribute('title')),
                'text' => trim($a->textContent),
                'is_mixed_content' => $is_https && $type == 'url' && !str_starts_with($href, 'https://'),
                'type' => $type,
                'is_internal' => $is_internal,
                'is_noopener' => $is_noopener,
                'is_noreferrer' => $is_noreferrer,
                'is_unsafe' => !$is_internal && !$is_noopener && !$is_noreferrer,
            ];

            $links[] = $link;

            /* Unsafe external links */
            if($link['is_unsafe']) {
                $unsafe_external_links_count++;
            }

            /* Links count */
            if($is_internal) {
                $internal_links_count++;
            } else {
                $external_links_count++;
            }

            /* Social links */
            $social_platforms = [
                'facebook' => 'facebook.com',
                'twitter' => 'twitter.com',
                'x' => 'x.com',
                'instagram' => 'instagram.com',
                'linkedin' => 'linkedin.com',
                'youtube' => 'youtube.com',
                'pinterest' => 'pinterest.com',
                'tiktok' => 'tiktok.com',
                'threads' => 'threads.com',
                'whatsapp' => 'wa.me',
                'telegram' => 't.me',
                'facebook_messenger' => 'm.me',
                'snapchat' => 'snapchat.com',
                'twitch' => 'twitch.tv',
                'discord' => 'discord.gg',
                'reddit' => 'reddit.com',
                'vk' => 'vk.com',
                'onlyfans' => 'onlyfans.com',
                'bluesky' => 'bsky.app',
            ];

            if($type == 'url') {
                $href_host_without_www = preg_replace('/^www\./', '', $href_host);
                if(!$is_internal && in_array($href_host_without_www, $social_platforms)) {
                    $platform_key = array_search($href_host_without_www, $social_platforms);
                    $social_links[] = [
                        'type' => $platform_key,
                        'href' => $href,
                        'title' => trim($a->getAttribute('title')),
                        'text' => trim($a->textContent),
                    ];
                }
            }
        }

        /* Images processing */
        $images = [];
        foreach($dom->getElementsByTagName('img') as $img) {
            $src = $img->getAttribute('src');
            $type = str_starts_with($src, 'data:image/') ? 'embedded' : 'url';

            if($type == 'url') {
                $src = get_full_url_from_relative_paths($url, $src);
                $http_requests_data['images']++;
            }

            $is_internal = true;
            if(parse_url($src, PHP_URL_HOST) != $parsed_url['host']) {
                $is_internal = false;
            }

            $images[] = [
                'src' => $src,
                'alt' => $img->getAttribute('alt'),
                'title' => $img->getAttribute('title'),
                'loading' => $img->getAttribute('loading'),
                'is_mixed_content' => $is_https && $type == 'url' && !str_starts_with($src, 'https://'),
                'is_internal' => $is_internal,
                'type' => $type,
                'extension' => $type == 'url' ? mb_strtolower(pathinfo(strtok($src, '?'), PATHINFO_EXTENSION)) : null,
            ];
        }

        /* Javascript processing */
        $scripts = [];
        $non_deferred_scripts_count = 0;
        $schemas = [];

        foreach($dom->getElementsByTagName('script') as $script) {
            $src = $script->getAttribute('src');
            $type = $src ? 'url' : 'embedded';

            if($type == 'url') {
                $src = get_full_url_from_relative_paths($url, $src);
                $http_requests_data['js']++;
            }

            $is_internal = true;
            if(parse_url($src, PHP_URL_HOST) != $parsed_url['host']) {
                $is_internal = false;
            }

            $script_data = [
                'src' => $src,
                'is_mixed_content' => $is_https && $type == 'url' && !str_starts_with($src, 'https://'),
                'is_deferred' => (bool) $script->getAttribute('defer'),
                'is_async' => (bool) $script->getAttribute('async'),
                'is_internal' => $is_internal,
                'type' => $type,
            ];

            $scripts[] = $script_data;

            /* Non deferred scripts count */
            if(!$script_data['is_deferred']) {
                $non_deferred_scripts_count++;
            }

            /* Detect schemas */
            if($script->getAttribute('type') && $script->getAttribute('type') == 'application/ld+json') {
                $parsed_schema = json_decode($script->nodeValue);

                $schemas[] = [
                    'raw_value' => $script->nodeValue,
                    'is_valid' => (bool) $parsed_schema
                ];
            }
        }

        /* Stylesheets */
        $stylesheets = [];

        /* Process link tags */
        foreach($link_tags as $link) {
            $rel = strtolower($link->getAttribute('rel'));
            $rels = array_map('trim', explode(' ', $rel));
            $href = $link->getAttribute('href');
            if(!$href) continue;

            if(in_array('icon', $rels) || in_array('shortcut', $rels) || in_array('alternate', $rels)) {
                $favicon = get_full_url_from_relative_paths($url, $href);
                $http_requests_data['images']++;
                continue;
            }

            if(in_array('canonical', $rels)) {
                $canonical = $href;
                continue;
            }

            if(in_array('manifest', $rels)) {
                $manifest = $href;
                continue;
            }

            if(in_array('stylesheet', $rels)) {
                $href = get_full_url_from_relative_paths($url, $href);
                $is_internal = parse_url($href, PHP_URL_HOST) == $parsed_url['host'];
                $http_requests_data['css']++;
                $stylesheets[] = [
                    'href' => $href,
                    'is_mixed_content' => $is_https && !str_starts_with($href, 'https://'),
                    'is_internal' => $is_internal,
                ];
            }
        }

        /* Iframes processing */
        $iframes = [];
        foreach($dom->getElementsByTagName('iframe') as $iframe) {
            $src = $iframe->getAttribute('src');
            $src = get_full_url_from_relative_paths($url, $src);

            $is_internal = true;
            if(parse_url($src, PHP_URL_HOST) != $parsed_url['host']) {
                $is_internal = false;
            }

            $http_requests_data['iframes']++;

            $iframes[] = [
                'src' => $src,
                'title' => $iframe->getAttribute('title'),
                'is_mixed_content' => $is_https && !str_starts_with($src, 'https://'),
                'is_internal' => $is_internal,
            ];
        }

        /* Videos processing */
        $videos = [];
        foreach($dom->getElementsByTagName('video') as $video) {
            $src = $video->getAttribute('src');

            if(!$src) {
                // Check for <source> child elements
                foreach($video->getElementsByTagName('source') as $source) {
                    $src = $source->getAttribute('src');
                    if($src) break;
                }
            }

            $src = get_full_url_from_relative_paths($url, $src);

            $is_internal = true;
            if(parse_url($src, PHP_URL_HOST) != $parsed_url['host']) {
                $is_internal = false;
            }

            $http_requests_data['videos']++;

            $videos[] = [
                'src' => $src,
                'preload' => $video->getAttribute('preload'),
                'is_mixed_content' => $is_https && $type == 'url' && !str_starts_with($src, 'https://'),
                'is_internal' => $is_internal,
            ];
        }

        /* Audios processing */
        $audios = [];
        foreach($dom->getElementsByTagName('audio') as $audio) {
            $src = $audio->getAttribute('src');

            if(!$src) {
                // Check for <source> child elements
                foreach($audio->getElementsByTagName('source') as $source) {
                    $src = $source->getAttribute('src');
                    if($src) break;
                }
            }

            $src = get_full_url_from_relative_paths($url, $src);

            $is_internal = true;
            if(parse_url($src, PHP_URL_HOST) != $parsed_url['host']) {
                $is_internal = false;
            }

            $http_requests_data['audios']++;

            $audios[] = [
                'src' => $src,
                'preload' => $audio->getAttribute('preload'),
                'is_mixed_content' => $is_https && !str_starts_with($src, 'https://'),
                'is_internal' => $is_internal,
            ];
        }

        /* Response headers */
        $response_headers = [
            'content_type' => is_array($normalized_headers['content-type'] ?? null) ? reset($normalized_headers['content-type']) : $normalized_headers['content-type'] ?? null,
            'content_encoding' => is_array($normalized_headers['content-encoding'] ?? null) ? reset($normalized_headers['content-encoding']) : $normalized_headers['content-encoding'] ?? null,
            'server' => is_array($normalized_headers['server'] ?? null) ? reset($normalized_headers['server']) : $normalized_headers['server'] ?? null,
            'x_robots_tag' => is_array($normalized_headers['x-robots-tag'] ?? null) ? reset($normalized_headers['x-robots-tag']) : $normalized_headers['x-robots-tag'] ?? null,
			'strict_transport_security' => is_array($normalized_headers['strict-transport-security'] ?? null) ? reset($normalized_headers['strict-transport-security']) : $normalized_headers['strict-transport-security'] ?? null,
            'content_security_policy' => is_array($normalized_headers['content-security-policy'] ?? null) ? reset($normalized_headers['content-security-policy']) : $normalized_headers['content-security-policy'] ?? null,
            'referrer_policy' => is_array($normalized_headers['referrer-policy'] ?? null) ? reset($normalized_headers['referrer-policy']) : $normalized_headers['referrer-policy'] ?? null,
        ];

		/* CSP header */
		$csp_headers = $normalized_headers['content-security-policy'] ?? [];

		if (!is_array($csp_headers)) {
			$csp_headers = [$csp_headers];
		}

        /* X Robots Tag */
        $x_robots_tag_headers = $normalized_headers['x-robots-tag'] ?? [];

        if (!is_array($x_robots_tag_headers)) {
            $x_robots_tag_headers = [$x_robots_tag_headers];
        }

        $x_robots_tags = [];

        foreach ($x_robots_tag_headers as $header_value) {
            foreach (explode(',', $header_value) as $part) {
                $part = trim(strtolower($part));

                if (strpos($part, ':') !== false) {
                    [, $part] = array_map('trim', explode(':', $part, 2));
                }

                $x_robots_tags[] = $part;
            }
        }

        $x_robots_tags = array_values(array_unique($x_robots_tags));

        /* HSTS */
        $hsts = $response_headers['strict_transport_security'];

        /* Find all emails */
        $pattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
        preg_match_all($pattern, $html, $matches);
        $emails = $matches[0];

        /* Get total word count of the page */
        foreach (['script', 'style'] as $tag) {
            $elements = $dom->getElementsByTagName($tag);
            while ($elements->length > 0) {
                $elements->item(0)->parentNode->removeChild($elements->item(0));
            }
        }


        /* Remove <script> & <style> tags so they don't count */
        foreach (['script', 'style'] as $tag) {
            $nodes = $dom->getElementsByTagName($tag);
            $to_remove = [];
            foreach ($nodes as $node) {
                $to_remove[] = $node;
            }
            foreach ($to_remove as $node) {
                $node->parentNode->removeChild($node);
            }
        }

        /* Get the body HTML */
        $body_content = $dom->getElementsByTagName('body')->item(0);
        $body_html_content = $body_content ? $dom->saveHTML($body_content) : '';

        /* Count the total length and the stripped text length */
        $total_length = mb_strlen($body_html_content);
        $text_content = strip_tags($body_html_content);
        $text_length = mb_strlen($text_content);

        /* Calculate text-to-HTML ratio */
        $text_to_html_ratio = $total_length > 0 ? number_format(($text_length / $total_length) * 100, 2) : 0;

        /* Page size */
        $page_size = mb_strlen($html);

        /* Top keywords */

        /* Convert to lowercase and remove all non-letters/numbers */
        $text_content = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', mb_strtolower($text_content));

        /* Split text into words using preg_split with unicode support */
        $words = preg_split('/\s+/u', $text_content, -1, PREG_SPLIT_NO_EMPTY);

        /* Remove stop words and numbers */
        $words = array_filter($words, function ($word) {
            return !in_array($word, settings()->audits->excluded_words ?? [], true) && !is_numeric($word);
        });

        /* Count word usage */
        $word_usage = array_count_values($words);
        arsort($word_usage);

        /* Get top 25 words */
        $top_words = array_slice($word_usage, 0, 25, true);

        /* Total word count */
        $words_count = count($words);

        /* Count HTTP requests */
        $http_requests = 0;
        foreach($http_requests_data as $key => $value) $http_requests += $value;

        /* Google Safe Browsing Check */
        $threats = [];
        if(!empty(settings()->audits->google_safe_browsing_api_key) && settings()->audits->available_tests->safe_browsing) {
            $threats = google_safe_browsing_check($url, settings()->audits->google_safe_browsing_api_key);
        }

        /* SPF records check */
        $spf_records = get_spf_records($parsed_url['host']);

        return [
            'url' => $url,
            'parsed_url' => $parsed_url,
            'is_seo_friendly_url' => $is_seo_friendly_url,

            /* Response data */
            'response_status_code' => $response_status_code,
            'page_size' => $page_size,
            'download_size' => $download_size,
            'redirect_count' => $redirect_count,
            'http_protocol' => $http_protocol,
            'http_version' => $http_version,

            /* Timing data */
            'response_time' => $response_time,
            'ttfb' => $ttfb,
            'average_download_speed' => $average_download_speed,

            /* Security */
            'is_https' => $is_https,
            'is_ssl_valid' => $is_ssl_valid,
            'ssl_certificate' => $ssl_certificate,
            'hsts' => $hsts,

            /* General tags */
            'dom_size' => $dom_size,
            'language' => $language,
            'doctype' => $doctype,
            'title' => $title,

            /* Deprecated HTML tags */
            'deprecated_html_tags' => $deprecated_html_tags,

            /* Meta tags */
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'meta_charset' => $meta_charset,
            'meta_viewport' => $meta_viewport,
            'meta_robots' => $meta_robots,
            'meta_googlebot' => $meta_googlebot,
            'meta_theme_color' => $meta_theme_color,
            'meta_refresh' => $meta_refresh,

            /* Inline CSS */
            'inline_css' => $inline_css,

            /* Opengraph */
            'opengraph' => $opengraph,

            /* Link tags */
            'favicon' => $favicon,
            'canonical' => $canonical,
            'manifest' => $manifest,

            /* Headings */
            'headings' => $headings,

            /* Response headers */
            'response_headers' => $response_headers,

            /* X robots tags headers */
            'x_robots_tags' => $x_robots_tags,

			/* CSP headers */
			'csp_headers' => $csp_headers,

            /* Links */
            'links' => $links,
            'internal_links_count' => $internal_links_count,
            'external_links_count' => $external_links_count,
            'unsafe_external_links_count' => $unsafe_external_links_count,

            /* Forms */
            'unsafe_forms' => $unsafe_forms,

            /* Social links */
            'social_links' => $social_links,

            /* Images */
            'images' => $images,

            /* Scripts */
            'scripts' => $scripts,
            'non_deferred_scripts_count' => $non_deferred_scripts_count,

            /* Schemas */
            'schemas' => $schemas,

            /* Iframes */
            'iframes' => $iframes,

            /* Stylesheets */
            'stylesheets' => $stylesheets,

            /* Audios */
            'audios' => $audios,

            /* Videos */
            'videos' => $videos,

            /* Emails */
            'emails' => $emails,

            /* Words */
            'words_count' => $words_count,
            'top_words' => $top_words,

            /* Text to HTML ratio */
            'text_to_html_ratio' => $text_to_html_ratio,

            /* Total amount of requests */
            'http_requests' => $http_requests,
            'http_requests_data' => $http_requests_data,

            /* Google safe browsing */
            'threats' => $threats,

            /* SPF records */
            'spf_records' => $spf_records,

            /* Resolved URL + external redirect check */
            'is_external_redirected' => $is_external_redirected,
            'resolved_url' => $resolved_url,

            /* Total redirects */
            'total_redirects' => $total_redirects,
        ];

    }

    public static function process_not_found($parsed_url) {
        return \Altum\Cache::cache_function_result('audit_not_found?host=' . md5($parsed_url['host']), null, function() use ($parsed_url) {

            /* Prepare curl opts */
            $curl_opts = [];

            /* Proxies */
            if(settings()->audits->proxies_is_enabled && count(settings()->audits->proxies ?? [])) {
                $enabled_proxies = array_values(array_filter(settings()->audits->proxies ?? [], fn($proxy) => !empty($proxy->is_enabled)));

                if(!empty($enabled_proxies)) {
                    $proxy_type_map = [
                        'http' => CURLPROXY_HTTP,
                        'socks5' => CURLPROXY_SOCKS5,
                        'socks5_rdns' => CURLPROXY_SOCKS5_HOSTNAME,
                    ];

                    /* Give a chance for direct connection as a candidate */
                    $candidates = $enabled_proxies;

                    if(!settings()->audits->proxies_exclusive) {
                        $candidates[] = null; /* Direct connection */
                    }

                    /* Gamble */
                    $proxy = $candidates[array_rand($candidates)];
                    $use_proxy = !is_null($proxy);

                    /* Add proxy if needed or remove proxy */
                    if($use_proxy) {
                        $curl_opts[CURLOPT_PROXY] = $proxy->address;
                        $curl_opts[CURLOPT_PROXYTYPE] = $proxy_type_map[$proxy->type] ?? CURLPROXY_HTTP;
                    } else {
                        $curl_opts[CURLOPT_PROXY] = '';
                    }
                }
            }

            // Testing only
            //\Unirest\Request::proxy('51.96.154.142', 8080, CURLPROXY_HTTP);

            /* Set timeout */
            \Unirest\Request::timeout(settings()->audits->request_timeout);

            /* Set follow redirects */
            \Unirest\Request::curlOpts($curl_opts);

            /* Verify SSL */
            \Unirest\Request::verifyPeer(false);

            /* Prepare request headers */
            $request_headers = [];

            /* Set custom user agent */
            if(settings()->audits->user_agent) {
                $request_headers['User-Agent'] = settings()->audits->user_agent;
            }

            /* 404 page check */
            $has_404_page = false;
            $not_found_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/404-page-test-' . md5(time() . time());
            try {
                /* Send the request */
                $not_found_response = \Unirest\Request::get($not_found_url, $request_headers);

                if($not_found_response->code === 404) {
                    $has_404_page = true;
                }
            } catch(\Exception $exception) {
                /* :) */
            }

            /* Clear custom settings */
            \Unirest\Request::clearCurlOpts();

            return [
                /* 404 page check */
                'has_404_page' => $has_404_page,
                'not_found_status_code' => $not_found_response->code,
                'not_found_url' => $not_found_url,
            ];
        });
    }

    public static function process_robots($parsed_url) {
        return \Altum\Cache::cache_function_result('audit_robots?host=' . md5($parsed_url['host']), null, function() use ($parsed_url) {

            /* Prepare curl opts */
            $curl_opts = [];

            /* Proxies */
            if(settings()->audits->proxies_is_enabled && count(settings()->audits->proxies ?? [])) {
                $enabled_proxies = array_values(array_filter(settings()->audits->proxies ?? [], fn($proxy) => !empty($proxy->is_enabled)));

                if(!empty($enabled_proxies)) {
                    $proxy_type_map = [
                        'http' => CURLPROXY_HTTP,
                        'socks5' => CURLPROXY_SOCKS5,
                        'socks5_rdns' => CURLPROXY_SOCKS5_HOSTNAME,
                    ];

                    /* Give a chance for direct connection as a candidate */
                    $candidates = $enabled_proxies;

                    if(!settings()->audits->proxies_exclusive) {
                        $candidates[] = null; /* Direct connection */
                    }

                    /* Gamble */
                    $proxy = $candidates[array_rand($candidates)];
                    $use_proxy = !is_null($proxy);

                    /* Add proxy if needed or remove proxy */
                    if($use_proxy) {
                        $curl_opts[CURLOPT_PROXY] = $proxy->address;
                        $curl_opts[CURLOPT_PROXYTYPE] = $proxy_type_map[$proxy->type] ?? CURLPROXY_HTTP;
                    } else {
                        $curl_opts[CURLOPT_PROXY] = '';
                    }
                }
            }

            // Testing only
            //\Unirest\Request::proxy('51.96.154.142', 8080, CURLPROXY_HTTP);

            /* Set timeout */
            \Unirest\Request::timeout(settings()->audits->request_timeout);

            /* Set follow redirects */
            \Unirest\Request::curlOpts($curl_opts);

            /* Verify SSL */
            \Unirest\Request::verifyPeer(false);

            /* Prepare request headers */
            $request_headers = [];

            /* Set custom user agent */
            if(settings()->audits->user_agent) {
                $request_headers['User-Agent'] = settings()->audits->user_agent;
            }

            /* Robots check */
            $has_robots_page = false;
            $robots_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/robots.txt';
            $robots_disallowed = [];
            $robots_sitemaps = [];
            try {
                /* Send the request */
                $robots_response = \Unirest\Request::get($robots_url, $request_headers);
                $robots_response->headers = is_array($robots_response->headers) ? array_change_key_case($robots_response->headers, CASE_LOWER) : [];
                $robots_response->headers['content-type'] = is_array($robots_response->headers['content-type'] ?? null) ? reset($robots_response->headers['content-type']) : $robots_response->headers['content-type'] ?? null;

                if(
                    !empty($robots_response->raw_body)
                    &&  $robots_response->code == 200
                    && isset($robots_response->headers['content-type'])
                    && str_starts_with($robots_response->headers['content-type'], 'text/plain')
                ) {
                    $has_robots_page = true;
                    $robots_lines = preg_split('/\r\n|\r|\n/', $robots_response->raw_body);
                    $user_agent = '*';

                    foreach($robots_lines as $robots_line) {
                        $robots_line = trim($robots_line);

                        /* Ignore if commented */
                        if(str_starts_with($robots_line, '#')) continue;

                        /* Get data pairs */
                        $data_pair = explode(':', $robots_line, 2);

                        /* Skip line if needed */
                        if(!isset($data_pair[0], $data_pair[1])) continue;

                        $key = trim(mb_strtolower($data_pair[0]));
                        $value = trim($data_pair[1]);

                        /* Check for user agent */
                        if($key == 'user-agent') {
                            $user_agent = $value;
                        }

                        /* Check for sitemap */
                        if($key == 'sitemap' && !in_array($value, $robots_sitemaps)) {
                            $robots_sitemaps[] = $value; continue;
                        }

                        /* Check for disallow */
                        if($key == 'disallow') {
                            if($value === '') {
                                /* Empty disallow means allow all – skip */
                                continue;
                            }

                            $pattern = preg_quote($value, '/');
                            $pattern = str_replace('\*', '.*', $pattern);
                            $pattern = '/^' . $pattern . '/';

                            if(preg_match($pattern, $parsed_url['path'] ?? '/')) {
                                $robots_disallowed[] = [
                                    'user_agent' => $user_agent,
                                    'path' => $value,
                                ];
                                continue;
                            }
                        }
                    }
                }
            } catch(\Exception $exception) {
                /* :) */
            }

            /* Clear custom settings */
            \Unirest\Request::clearCurlOpts();

            return [
                /* Robots page check */
                'has_robots_page' => $has_robots_page,
                'robots_url' => $robots_url,
                'robots_disallowed' => $robots_disallowed,
                'robots_sitemaps' => $robots_sitemaps,
            ];
        });
    }

    public static function process_audit_data($data) {
        $audit_data = [
            'major_issues' => [],
            'moderate_issues' => [],
            'minor_issues' => [],

            'major_issues_percentage_weight' => 60,
            'moderate_issues_percentage_weight' => 30,
            'minor_issues_percentage_weight' => 10,

            'potential_major_issues' => 0,
            'potential_moderate_issues' => 0,
            'potential_minor_issues' => 0,

            'found_major_issues' => 0,
            'found_moderate_issues' => 0,
            'found_minor_issues' => 0,
        ];

        /* Title missing */
        $audit_data['potential_major_issues'] += 1;
        if(empty($data['title'])) {
            $audit_data['major_issues']['title'][] = 'missing';
            $audit_data['found_major_issues'] += 1;
        }

        /* Title length */
        $audit_data['potential_major_issues'] += 1;
        if(!empty($data['title'])) {
            if(mb_strlen($data['title']) < 30) {
                $audit_data['major_issues']['title'][] = 'too_short';
                $audit_data['found_major_issues'] += 1;
            }

            if(mb_strlen($data['title']) > 60) {
                $audit_data['major_issues']['title'][] = 'too_long';
                $audit_data['found_major_issues'] += 1;
            }
        }

        /* Meta description missing */
        $audit_data['potential_major_issues'] += 1;
        if(empty($data['meta_description'])) {
            $audit_data['major_issues']['meta_description'][] = 'missing';
            $audit_data['found_major_issues'] += 1;
        }

        /* Meta description length */
        $audit_data['potential_moderate_issues'] += 1;
        if(!empty($data['meta_description'])) {
            if(mb_strlen($data['meta_description']) < 50) {
                $audit_data['moderate_issues']['meta_description'][] = 'too_short';
                $audit_data['found_moderate_issues'] += 1;
            }

            if(mb_strlen($data['meta_description']) > 160) {
                $audit_data['moderate_issues']['meta_description'][] = 'too_long';
                $audit_data['found_moderate_issues'] += 1;
            }
        }

        /* H1 missing */
        $audit_data['potential_major_issues'] += 1;
        if(!count($data['headings']['h1'])) {
            $audit_data['major_issues']['h1'][] = 'missing';
            $audit_data['found_major_issues'] += 1;
        }

        /* H1 too many */
        $audit_data['potential_moderate_issues'] += 1;
        if(count($data['headings']['h1']) > 1) {
            $audit_data['moderate_issues']['h1'][] = 'too_many';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* H1 length */
        $audit_data['potential_minor_issues'] += 1;
        if(!empty(reset($data['headings']['h1']))) {
            if(mb_strlen(reset($data['headings']['h1'])) < 10) {
                $audit_data['minor_issues']['h1'][] = 'too_short';
                $audit_data['found_minor_issues'] += 1;
            }

            if(mb_strlen(reset($data['headings']['h1'])) > 120) {
                $audit_data['minor_issues']['h1'][] = 'too_long';
                $audit_data['found_minor_issues'] += 1;
            }
        } else {
            $audit_data['minor_issues']['h1'][] = 'too_short';
            $audit_data['found_minor_issues'] += 1;
        }

        /* 404 page */
        $audit_data['potential_moderate_issues'] += 1;
        if(!$data['has_404_page']) {
            $audit_data['moderate_issues']['not_found'][] = 'missing';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* robots.txt */
        $audit_data['potential_minor_issues'] += 1;
        if(!$data['has_robots_page']) {
            $audit_data['minor_issues']['robots'][] = 'missing';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Make sure page is not excluded */
        else {
            $audit_data['potential_major_issues'] += 1;
            foreach($data['robots_disallowed'] as $disallowed) {
                if(
                    $data['parsed_url']['scheme'] . '://' . $data['parsed_url']['host'] . $disallowed['path'] == $data['url']
                    && in_array($disallowed['user_agent'], ['Googlebot', 'Bingbot', 'Yahoo! Slurp', 'Baiduspider', 'YandexBot', '*'])
                ) {
                    $audit_data['major_issues']['robots'][] = 'excluded';
                    $audit_data['found_major_issues'] += 1;
                }
            }
        }

        /* Language missing */
        $audit_data['potential_minor_issues'] += 1;
        if(empty($data['language'])) {
            $audit_data['minor_issues']['language'][] = 'missing';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Favicon missing */
        $audit_data['potential_moderate_issues'] += 1;
        if(empty($data['favicon'])) {
            $audit_data['moderate_issues']['favicon'][] = 'missing';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* Images Noindex tag */
        $audit_data['potential_moderate_issues'] += 1;
        if(!empty($data['meta_robots']) && in_array('noimageindex', $data['meta_robots'])) {
            $audit_data['moderate_issues']['images_meta_robots'][] = 'excluded';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* Images Noindex header */
        $audit_data['potential_moderate_issues'] += 1;
        if(!empty($data['x_robots_tags']) && in_array('noimageindex', $data['x_robots_tags'])) {
            $audit_data['moderate_issues']['header_robots'][] = 'excluded';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* Noindex tag */
        $audit_data['potential_major_issues'] += 1;
        if(!empty($data['meta_robots']) && (in_array('noindex', $data['meta_robots']) || in_array('none', $data['meta_robots']))) {
            $audit_data['major_issues']['meta_robots'][] = 'excluded';
            $audit_data['found_major_issues'] += 1;
        }

        /* Noindex header */
        $audit_data['potential_major_issues'] += 1;
        if(!empty($data['x_robots_tags']) && (in_array('noindex', $data['x_robots_tags']) || in_array('none', $data['x_robots_tags']))) {
            $audit_data['major_issues']['header_robots'][] = 'excluded';
            $audit_data['found_major_issues'] += 1;
        }

        /* Meta refresh */
        $audit_data['potential_minor_issues'] += 1;
        if($data['meta_refresh']) {
            $audit_data['minor_issues']['meta_refresh'][] = 'existing';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Response time */
        $audit_data['potential_major_issues'] += 1;
        if($data['response_time'] > 2000) {
            $audit_data['major_issues']['response_time'][] = 'too_slow';
            $audit_data['found_major_issues'] += 1;
        }

        /* Page size */
        $audit_data['potential_moderate_issues'] += 1;
        if($data['page_size'] > 150000) {
            $audit_data['moderate_issues']['page_size'][] = 'too_big';
            $audit_data['found_moderate_issues'] += 1;
        }

        $audit_data['potential_major_issues'] += 1;
        if($data['page_size'] > 2000000) {
            $audit_data['major_issues']['page_size'][] = 'too_big_major';
            $audit_data['found_major_issues'] += 1;
        }

        /* DOM size */
        $audit_data['potential_minor_issues'] += 1;
        if($data['dom_size'] > 1500) {
            $audit_data['minor_issues']['dom_size'][] = 'too_big';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Meta viewport missing */
        $audit_data['potential_moderate_issues'] += 1;
        if(empty($data['meta_viewport'])) {
            $audit_data['moderate_issues']['meta_viewport'][] = 'missing';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* Meta charset missing */
        $audit_data['potential_moderate_issues'] += 1;
        if(empty($data['meta_charset'])) {
            $audit_data['moderate_issues']['meta_charset'][] = 'missing';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* Deprecated HTML tags */
        $audit_data['potential_minor_issues'] += 1;
        if(count($data['deprecated_html_tags'])) {
            $audit_data['minor_issues']['deprecated_html_tags'][] = 'existing';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Server header */
        $audit_data['potential_minor_issues'] += 1;
        if($data['response_headers']['server']) {
            $audit_data['minor_issues']['header_server'][] = 'existing';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Server compression missing */
        $audit_data['potential_moderate_issues'] += 1;
        if(empty($data['response_headers']['content_encoding']) || $data['response_headers']['content_encoding'] == 'identity') {
            $audit_data['moderate_issues']['server_compression'][] = 'missing';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* Doctype missing */
        $audit_data['potential_major_issues'] += 1;
        if(empty($data['doctype'])) {
            $audit_data['major_issues']['doctype'][] = 'missing';
            $audit_data['found_major_issues'] += 1;
        }

        /* Words too few */
        $audit_data['potential_moderate_issues'] += 1;
        if($data['words_count'] < 500) {
            $audit_data['moderate_issues']['words_count'][] = 'too_few';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* Plaintext emails */
        $audit_data['potential_minor_issues'] += 1;
        if(count($data['emails'])) {
            $audit_data['minor_issues']['emails'][] = 'existing';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Text to HTML ratio */
        $audit_data['potential_minor_issues'] += 1;
        if($data['text_to_html_ratio'] < 15) {
            $audit_data['minor_issues']['text_to_html_ratio'][] = 'too_low';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Is HTTPS */
        $audit_data['potential_major_issues'] += 1;
        if(!$data['is_https']) {
            $audit_data['major_issues']['is_https'][] = 'missing';
            $audit_data['found_major_issues'] += 1;
        }

        /* Is SSL valid */
        $audit_data['potential_major_issues'] += 1;
        if(!$data['is_ssl_valid']) {
            $audit_data['major_issues']['is_ssl_valid'][] = 'invalid';
            $audit_data['found_major_issues'] += 1;
        }

        /* Inline CSS */
        $audit_data['potential_minor_issues'] += 1;
        if(count($data['inline_css'])) {
            $audit_data['minor_issues']['inline_css'][] = 'existing';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Image formats */
        $audit_data['potential_moderate_issues'] += 1;
        foreach($data['images'] as $image) {
            if($image['extension'] && !in_array($image['extension'], ['webp', 'avif', 'svg'])) {
                $audit_data['moderate_issues']['image_formats'][] = 'existing';
                $audit_data['found_moderate_issues'] += 1;
                break;
            }
        }

        /* Image alt */
        $audit_data['potential_moderate_issues'] += 1;
        foreach($data['images'] as $image) {
            if(empty($image['alt'])) {
                $audit_data['moderate_issues']['image_alt'][] = 'missing';
                $audit_data['found_moderate_issues'] += 1;
                break;
            }
        }

        /* Canonical */
        $audit_data['potential_minor_issues'] += 1;
        if(empty($data['canonical'])) {
            $audit_data['minor_issues']['canonical'][] = 'missing';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Canonical */
        $audit_data['potential_minor_issues'] += 1;
        if(!$data['is_seo_friendly_url']) {
            $audit_data['minor_issues']['is_seo_friendly_url'][] = 'false';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Is HTTP2 */
        $audit_data['potential_moderate_issues'] += 1;
        if($data['http_version'] != 3) {
            $audit_data['moderate_issues']['is_http2'][] = 'missing';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* Unsafe external links */
        $audit_data['potential_minor_issues'] += 1;
        if($data['unsafe_external_links_count']) {
            $audit_data['minor_issues']['unsafe_external_links'][] = 'existing';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Mixed content form actions */
        $audit_data['potential_moderate_issues'] += 1;
        if(count($data['unsafe_forms'])) {
            $audit_data['moderate_issues']['unsafe_forms'][] = 'existing';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* Non deferred scripts */
        $audit_data['potential_minor_issues'] += 1;
        if($data['non_deferred_scripts_count']) {
            $audit_data['minor_issues']['non_deferred_scripts'][] = 'existing';
            $audit_data['found_minor_issues'] += 1;
        }

        /* HTTP requests */
        $audit_data['potential_moderate_issues'] += 1;
        if($data['http_requests'] > 50) {
            $audit_data['moderate_issues']['http_requests'][] = 'too_many';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* internal links */
        $audit_data['potential_minor_issues'] += 1;
        if($data['internal_links_count'] > 100) {
            $audit_data['minor_issues']['internal_links'][] = 'too_many';
            $audit_data['found_minor_issues'] += 1;
        }

        /* external links */
        $audit_data['potential_moderate_issues'] += 1;
        if($data['external_links_count'] > 25) {
            $audit_data['moderate_issues']['external_links'][] = 'too_many';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Open Graph */
        $audit_data['potential_minor_issues'] += 1;
        if(!count($data['opengraph'])) {
            $audit_data['minor_issues']['opengraph'][] = 'missing';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Schema.org structured data */
        $audit_data['potential_minor_issues'] += 1;
        if(!count($data['schemas'])) {
            $audit_data['minor_issues']['schemas'][] = 'missing';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Schema.org invalid structured data */
        $audit_data['potential_minor_issues'] += 1;
        foreach($data['schemas'] as $schema) {
            if(!$schema['is_valid']) {
                $audit_data['minor_issues']['schemas'][] = 'invalid';
                $audit_data['found_minor_issues'] += 1;
                break;
            }
        }

        /* Google Safe Browsing */
        if(!empty(settings()->audits->google_safe_browsing_api_key) && settings()->audits->available_tests->safe_browsing) {
            $audit_data['potential_major_issues'] += 1;
            if(count($data['threats'])) {
                $audit_data['major_issues']['safe_browsing'][] = 'false';
                $audit_data['found_major_issues'] += 1;
            }
        }

        /* HSTS exists */
        $audit_data['potential_moderate_issues'] += 1;
        if(!$data['hsts']) {
            $audit_data['moderate_issues']['hsts'][] = 'missing';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* Check the validity of the directive */
        $audit_data['potential_moderate_issues'] += 1;
        if($data['hsts']) {
            $is_valid = false;
            if(preg_match('/max-age\s*=\s*(\d+)/i', $data['response_headers']['strict_transport_security'], $match) && intval($match[1]) > 0) {
                $is_valid = true;
            }

            if(!$is_valid) {
                $audit_data['moderate_issues']['hsts'][] = 'invalid';
                $audit_data['found_moderate_issues'] += 1;
            }
        }

		/* Has CSP header */
		$audit_data['potential_moderate_issues'] += 1;
		if(empty($data['csp_headers'])) {
			$audit_data['moderate_issues']['csp'][] = 'missing';
			$audit_data['found_moderate_issues'] += 1;
		}

        /* SPF */
        $audit_data['potential_moderate_issues'] += 1;
        if(empty($data['spf_records'])) {
            $audit_data['moderate_issues']['spf'][] = 'missing';
            $audit_data['found_moderate_issues'] += 1;
        }

        $audit_data['potential_moderate_issues'] += 1;
        if(count($data['spf_records']) > 1) {
            $audit_data['moderate_issues']['spf'][] = 'too_many';
            $audit_data['found_moderate_issues'] += 1;
        }

        $audit_data['potential_moderate_issues'] += 1;
        if(count($data['spf_records']) == 1 && !empty(validate_spf_syntax($data['spf_records'][0]))) {
            $audit_data['moderate_issues']['spf'][] = 'invalid';
            $audit_data['found_moderate_issues'] += 1;
        }

        /* Referrer Policy Header */
        $audit_data['potential_minor_issues'] += 1;
        if(!$data['response_headers']['referrer_policy']) {
            $audit_data['minor_issues']['referrer_policy'][] = 'missing';
            $audit_data['found_minor_issues'] += 1;
        }

        $audit_data['potential_minor_issues'] += 1;
        if($data['response_headers']['referrer_policy'] == 'unsafe-url') {
            $audit_data['minor_issues']['referrer_policy'][] = 'unsafe';
            $audit_data['found_minor_issues'] += 1;
        }

        /* Calculate total issues */
        $audit_data['total_issues'] = $audit_data['found_major_issues'] + $audit_data['found_moderate_issues'] + $audit_data['found_minor_issues'];

        /* Calculate total tests */
        $audit_data['total_tests'] = $audit_data['potential_major_issues'] + $audit_data['potential_moderate_issues'] + $audit_data['potential_minor_issues'];

        /* Calculate passed tests */
        $audit_data['passed_tests'] = $audit_data['total_tests'] - $audit_data['total_issues'];

        /* Initialize the score to 100 */
        $audit_data['score'] = 100;

        /* Calculate deductions for major issues */
        if($audit_data['potential_major_issues'] > 0) {
            $major_issue_deduction = ($audit_data['found_major_issues'] / $audit_data['potential_major_issues']) * $audit_data['major_issues_percentage_weight'];
            $audit_data['score'] -= $major_issue_deduction;
        }

        /* Calculate deductions for moderate issues */
        if($audit_data['potential_moderate_issues'] > 0) {
            $moderate_issue_deduction = ($audit_data['found_moderate_issues'] / $audit_data['potential_moderate_issues']) * $audit_data['moderate_issues_percentage_weight'];
            $audit_data['score'] -= $moderate_issue_deduction;
        }

        /* Calculate deductions for minor issues */
        if($audit_data['potential_minor_issues'] > 0) {
            $minor_issue_deduction = ($audit_data['found_minor_issues'] / $audit_data['potential_minor_issues']) * $audit_data['minor_issues_percentage_weight'];
            $audit_data['score'] -= $minor_issue_deduction;
        }

        /* Ensure the score doesn't go below 0 */
        $audit_data['score'] = round(max(0, $audit_data['score']));

        return $audit_data;
    }

    public static function process_ai_summary($data, $issues, $in_parallel = true) {
        $custom_data = [
            'url' => $data['url'],
            'is_seo_friendly_url' => $data['is_seo_friendly_url'],

            /* Status & security */
            'response_status_code' => $data['response_status_code'],
            'is_https' => $data['is_https'],
            'is_ssl_valid' => $data['is_ssl_valid'],
            'hsts' => $data['hsts'],
            'threats' => $data['threats'],

            /* Performance */
            'response_time' => $data['response_time'],
            'ttfb' => $data['ttfb'],
            'page_size' => $data['page_size'],
            'average_download_speed' => $data['average_download_speed'],

            /* Content & structure */
            'title' => $data['title'],
            'meta_description' => $data['meta_description'],
            'headings' => $data['headings'],
            'words_count' => $data['words_count'],
            'text_to_html_ratio' => $data['text_to_html_ratio'],

            /* Indexing & metadata */
            'canonical' => $data['canonical'],
            'meta_robots' => $data['meta_robots'],
            'schemas' => $data['schemas'],

            /* Links & media */
            'internal_links_count' => $data['internal_links_count'],
            'external_links_count' => $data['external_links_count'],
            'unsafe_external_links_count' => $data['unsafe_external_links_count'],
        ];

        /* OpenAI request */
        \Unirest\Request::timeout(30);
        \Unirest\Request::verifyPeer(true);

        try {
            $response = \Unirest\Request::post(
                settings()->audits->openai_api_url . 'v1/responses',
                [
                    'Authorization' => 'Bearer ' . settings()->audits->openai_api_key,
                    'Content-Type' => 'application/json'
                ],
                json_encode([
                    'model' => settings()->audits->openai_model,
                    'background' => $in_parallel,
                    'input' => [
                        [
                            'role' => 'developer',
                            'content' => [[
                                'type' => 'input_text',
                                'text' => 'You are an SEO expert. Analyze ONLY the SEO data provided in the JSON input. Do not guess, infer, or add information that is not present in the JSON.

Return a clear and actionable SEO audit written for non-technical website owners, using simple and direct language. Limit the response to exactly 2–3 short paragraphs.

The output MUST be valid HTML only. Use <br /> to separate paragraphs and <strong> for emphasis where helpful. Do NOT use titles, lists, headings, code blocks, or explanations.

Every recommendation must be directly supported by the JSON data. Avoid generic or best-practice SEO advice.

Use no emojis, or at most one emoji if it clearly improves understanding.

Write the response in the '. \Altum\Language::$name . ' language.

Return ONLY the HTML paragraphs. Do not include anything else.',
                            ]],
                        ],
                        [
                            'role' => 'user',
                            'content' => [[
                                'type' => 'input_text',
                                'text' => "Website data: \n\n" . json_encode($custom_data) . "\n\n Issues found: \n\n" . json_encode($issues)
                            ]],
                        ]
                    ]
                ])
            );

        } catch (\Exception $exception) {
            error_log('[AI AUDIT SUMMARY ERROR]: ' . $exception->getMessage());
            return null;
        }

        if($response->code >= 400) {
            error_log('[AI AUDIT SUMMARY ERROR]: ' . $response->raw_body);
            return null;
        }

        /* Respond with the id if needed in parallel */
        if($in_parallel) return $response->body->id;

        /* get response */
        $ai_summary = null;

        foreach($response->body->output as $item) {
            if (($item->type ?? null) !== 'message') {
                continue;
            }

            foreach ($item->content ?? [] as $content) {
                if (($content->type ?? null) === 'output_text') {
                    $ai_summary = $content->text;
                }
            }
        }

        return $ai_summary;
    }


}
