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

defined('ALTUMCODE') || die();

function minecraft_description_to_plain_and_html($component)
{
    $plain_text = '';
    $html = '';

    $walk = function ($node, $inherited_style) use (&$walk, &$plain_text, &$html) {
        if (is_string($node)) {
            if ($node !== '') {
                $plain_text .= $node;
                $html .= htmlspecialchars($node, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
            return;
        }

        if (!is_array($node)) {
            return;
        }

        $style = $inherited_style;

        if (isset($node['color']) && is_string($node['color']) && $node['color'] !== '') {
            $style['color'] = $node['color'];
        }

        if (array_key_exists('bold', $node)) {
            $style['font-weight'] = $node['bold'] ? '700' : '400';
        }

        if (array_key_exists('italic', $node)) {
            $style['font-style'] = $node['italic'] ? 'italic' : 'normal';
        }

        $decorations = [];
        if (!empty($style['text-decoration']) && is_string($style['text-decoration'])) {
            $decorations[] = $style['text-decoration'];
        }

        if (array_key_exists('underlined', $node) && $node['underlined']) {
            $decorations[] = 'underline';
        }

        if (array_key_exists('strikethrough', $node) && $node['strikethrough']) {
            $decorations[] = 'line-through';
        }

        if (!empty($decorations)) {
            $style['text-decoration'] = implode(' ', array_values(array_unique($decorations)));
        }

        if (isset($node['text']) && is_string($node['text']) && $node['text'] !== '') {
            $plain_text .= $node['text'];

            $safe_text = htmlspecialchars($node['text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $css = '';
            foreach ($style as $key => $value) {
                if ($value === '' || $value === null) {
                    continue;
                }
                $css .= $key . ':' . $value . ';';
            }

            if ($css !== '') {
                $html .= '<span style="' . htmlspecialchars($css, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . $safe_text . '</span>';
            } else {
                $html .= $safe_text;
            }
        }

        if (isset($node['extra']) && is_array($node['extra'])) {
            foreach ($node['extra'] as $extra_node) {
                $walk($extra_node, $style);
            }
        }
    };

    $walk($component, []);

    // normalize line breaks
    $plain_text = str_replace(["\r\n", "\r"], "\n", $plain_text);
    $html = str_replace(["\r\n", "\r"], "\n", $html);

    // trim padding left/right per line
    $plain_text = preg_replace('/^[ \t]+/m', '', $plain_text);
    $plain_text = preg_replace('/[ \t]+$/m', '', $plain_text);
    $html = preg_replace("/^[ \t]+/m", '', $html);
    $html = preg_replace("/[ \t]+$/m", '', $html);

    // collapse huge space runs
    $plain_text = preg_replace('/ {4,}/', '   ', $plain_text);
    $html = preg_replace('/ {4,}/', '   ', $html);

    // make it one line (turn any newlines into a single space)
    $plain_text = preg_replace("/\n+/", ' ', $plain_text);
    $html = preg_replace("/\n+/", ' ', $html);

    // remove double spaces created by newline removal
    $plain_text = preg_replace('/[ \t]{2,}/', ' ', $plain_text);
    $html = preg_replace('/[ \t]{2,}/', ' ', $html);

    // remove empty spans
    $html = preg_replace('/<span[^>]*><\/span>/', '', $html);

    return [
        'plain_text' => trim($plain_text),
        'html' => trim($html),
    ];
}

function get_idn_ascii_domain($domain_name) {
    $domain_name = trim(strtolower($domain_name), ". \t\n\r\0\x0B");

    if($domain_name == '') {
        return null;
    }

    if(function_exists('idn_to_ascii')) {
        $domain_name_ascii = idn_to_ascii($domain_name, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        if($domain_name_ascii !== false && $domain_name_ascii != '') {
            return strtolower($domain_name_ascii);
        }
    }

    return strtolower($domain_name);
}

function get_domain_tld($domain_name) {
    $domain_parts = explode('.', $domain_name);

    if(empty($domain_parts)) {
        return null;
    }

    return strtolower(end($domain_parts));
}

function get_rdap_dns_map() {
    $cache_file = UPLOADS_PATH . 'cache/rdap_dns.json';

    /* Cache for 30 days */
    if(!file_exists($cache_file) || filemtime($cache_file) < time() - 2592000) {
        $json = file_get_contents('https://data.iana.org/rdap/dns.json');

        if($json) {
            file_put_contents($cache_file, $json);
        }
    }

    if(!file_exists($cache_file)) {
        return [];
    }

    $data = json_decode(file_get_contents($cache_file), true);
    if(!is_array($data) || empty($data['services']) || !is_array($data['services'])) {
        return [];
    }

    $map = [];

    foreach($data['services'] as $service) {
        $tlds = $service[0] ?? [];
        $endpoints = $service[1] ?? [];

        if(empty($tlds) || empty($endpoints)) {
            continue;
        }

        $endpoint = rtrim($endpoints[0], '/');

        foreach($tlds as $tld) {
            $map[strtolower($tld)] = $endpoint;
        }
    }

    return $map;
}

function get_domain_info_rdap($rdap_server, $domain) {
    $domain_ascii = get_idn_ascii_domain($domain);

    if(!$domain_ascii) {
        return null;
    }

    $rdap_url = $rdap_server . '/domain/' . urlencode($domain_ascii);

    $headers = [
        'Accept' => 'application/rdap+json',
    ];

    try {
        $response = \Unirest\Request::get($rdap_url, $headers);
    } catch(\Unirest\Exception $exception) {
        return null;
    }

    if($response->code !== 200) {
        return null;
    }

    $rdap_data = json_decode($response->raw_body, true);
    if(!is_array($rdap_data)) {
        return null;
    }

    $start_datetime = null;
    $updated_datetime = null;
    $end_datetime = null;
    $registrar = null;
    $nameservers = [];

    /* registration / last changed / expiration */
    if(!empty($rdap_data['events']) && is_array($rdap_data['events'])) {
        foreach($rdap_data['events'] as $event) {
            if(empty($event['eventAction']) || empty($event['eventDate'])) {
                continue;
            }

            $event_action = $event['eventAction'];

            try {
                $dt = new DateTime($event['eventDate']);
                $dt->setTimezone(new DateTimeZone('UTC'));
                $event_date = $dt->format('Y-m-d H:i:s');
            } catch(Exception $exception) {
                continue;
            }

            if($event_action === 'registration') {
                $start_datetime = $event_date;

            } elseif($event_action === 'expiration') {
                $end_datetime = $event_date;

            } elseif($event_action === 'last changed') {
                $updated_datetime = $event_date;

            } elseif($event_action === 'last update of RDAP database' && $updated_datetime === null) {
                $updated_datetime = $event_date;
            }
        }
    }

    /* Registrar */
    if(!empty($rdap_data['entities']) && is_array($rdap_data['entities'])) {
        foreach($rdap_data['entities'] as $entity) {
            if(empty($entity['roles']) || !is_array($entity['roles'])) {
                continue;
            }

            if(!in_array('registrar', $entity['roles'], true)) {
                continue;
            }

            if(!empty($entity['vcardArray'][1]) && is_array($entity['vcardArray'][1])) {
                foreach($entity['vcardArray'][1] as $vcard_item) {
                    if(!is_array($vcard_item) || count($vcard_item) < 4) {
                        continue;
                    }

                    if($vcard_item[0] === 'fn') {
                        $registrar = $vcard_item[3];
                        break 2;
                    }
                }
            }
        }
    }

    /* Nameservers */
    if(!empty($rdap_data['nameservers']) && is_array($rdap_data['nameservers'])) {
        foreach($rdap_data['nameservers'] as $nameserver_item) {
            if(!empty($nameserver_item['ldhName'])) {
                $nameservers[] = $nameserver_item['ldhName'];
            } elseif(!empty($nameserver_item['unicodeName'])) {
                $nameservers[] = $nameserver_item['unicodeName'];
            }
        }
    }

    return [
        'start_datetime' => $start_datetime,
        'updated_datetime' => $updated_datetime,
        'end_datetime' => $end_datetime,
        'registrar' => $registrar,
        'nameservers' => $nameservers,
        'raw' => $rdap_data,
    ];
}

function get_website_certificate($url, $port = 443, $enabled = false) {
    try {
        $host = str_replace('https://', '', $url);
        $host_ascii = get_idn_ascii_domain($host);

        $get = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => TRUE,
                'verify_peer' => $enabled,
                'verify_peer_name' => $enabled,
                'allow_self_signed' => true,
                'SNI_enabled' => true,
                'SNI_server_name' => $host_ascii,
            ]
        ]);

        $read = @stream_socket_client('ssl://' . $host_ascii . ':' . $port, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $get);

        if(!$read || $errstr) return null;

        $certificate_params = stream_context_get_params($read);

        $certificate = openssl_x509_parse($certificate_params['options']['ssl']['peer_certificate']);

        if(empty($certificate)) return null;

        $start_datetime = $certificate['validFrom_time_t'] ? (new \DateTime())->setTimestamp($certificate['validFrom_time_t']) : null;
        $end_datetime = $certificate['validTo_time_t'] ? (new \DateTime())->setTimestamp($certificate['validTo_time_t']) : null;
        $current_datetime = (new \DateTime());
        $is_valid = $start_datetime && $end_datetime && $current_datetime > $start_datetime && $current_datetime < $end_datetime;

        return empty($certificate) ? null : [
            'organization' => $certificate['issuer']['O'] ?? null,
            'common_name' => $certificate['issuer']['CN'] ?? null,
            'issuer_country' => $certificate['issuer']['C'] ?? null,
            'start_datetime' => $start_datetime ? $start_datetime->format('Y-m-d H:i:s') : null,
            'end_datetime' => $end_datetime ? $end_datetime->format('Y-m-d H:i:s') : null,
            'signature_type' => $certificate['signatureTypeSN'] ?? null,
            'is_valid' => $is_valid,
        ];

    } catch (\Exception $exception) {
        return null;
    }
}

