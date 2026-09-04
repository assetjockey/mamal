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
use Altum\Meta;
use Altum\Title;

defined('ALTUMCODE') || die();

class Tools extends Controller {
    public $tools_usage = null;

    public function index() {

        if(!settings()->tools->is_enabled) {
            throw_404();
        }

        if(settings()->tools->access == 'users') {
            \Altum\Authentication::guard();
        }

        $tools = require APP_PATH . 'includes/tools.php';
        $this->tools_usage = (new \Altum\Models\Tools())->get_tools_usage();

        /* Prepare the view */
        $data = [
            'tools' => $tools,
            'tools_usage' => $this->tools_usage,
        ];

        $view = new \Altum\View('tools/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    private function initiate() {
        if(!settings()->tools->is_enabled) {
            throw_404();
        }

        if(settings()->tools->access == 'users') {
            \Altum\Authentication::guard();
        }

        if(!settings()->tools->available_tools->{\Altum\Router::$method}) {
            throw_404();
        }

        /* Make sure there are no extra URL additions */
        if(isset($this->params[0])) {
            throw_404();
        }

        /* Detect extra details about the user */
        $whichbrowser = get_whichbrowser();

        /* Add a new view to the page */
        $cookie_name = 'tools_view_' . \Altum\Router::$method;
        if(!isset($_COOKIE[$cookie_name]) && $whichbrowser->device->type != 'bot') {
            setcookie($cookie_name, (int) true, time()+60*60*24*1);
            db()->onDuplicate(['total_views'], 'id');
            db()->insert('tools_usage', [
                'tool_id' => \Altum\Router::$method,
                'total_views' => db()->inc(),
            ]);
        }

        $this->tools_usage = $tools_usage = (new \Altum\Models\Tools())->get_tools_usage();

        /* Popular tools View */
        $view = new \Altum\View('tools/popular_tools', (array) $this);
        $this->add_view_content('popular_tools', $view->run([
            'tools_usage' => $tools_usage,
            'tools' => require APP_PATH . 'includes/tools.php',
        ]));

        /* Similar tools View */
        $view = new \Altum\View('tools/similar_tools', (array) $this);
        $this->add_view_content('similar_tools', $view->run([
            'tools_usage' => $tools_usage,
            'tool' => \Altum\Router::$method,
            'tools' => require APP_PATH . 'includes/tools.php',
        ]));

        /* Ratings View */
        $view = new \Altum\View('tools/ratings', (array) $this);
        $this->add_view_content('ratings', $view->run([
            'tools_usage' => $tools_usage,
            'tool_id' => \Altum\Router::$method,
        ]));

        /* Extra content View */
        $view = new \Altum\View('tools/extra_content', (array) $this);
        $this->add_view_content('extra_content', $view->run());

        /* Meta & title */
        Title::set(sprintf(l('tools.tool_title'), l('tools.' . \Altum\Router::$method . '.name')));
        Meta::set_description(l('tools.' . \Altum\Router::$method . '.description'));
        Meta::set_keywords(l('tools.' . \Altum\Router::$method . '.meta_keywords'));


        /* Set timeout */
        \Unirest\Request::timeout(5);
    }

    private function process_usage($input = null, $data = []) {
        $tool_id = query_clean(\Altum\Router::$method);
        $tool_usage = db()->where('tool_id', $tool_id)->getOne('tools_usage');

        $data_key = $input ? md5(serialize($input)) : null;

        if($tool_usage) {
            $tool_usage->data = json_decode($tool_usage->data ?? '', true);

            if(!is_array($tool_usage->data)) {
                $tool_usage->data = [];
            }

            if($input) {
                $tool_usage->data[$data_key] = array_merge($input, (array)$data);
                $tool_usage->data = array_reverse($tool_usage->data);
                $tool_usage->data = array_slice($tool_usage->data, 0, 10);
            }

            db()->where('tool_id', $tool_id)->update('tools_usage', [
                'total_submissions' => db()->inc(),
                'data' => json_encode($tool_usage->data),
            ]);
        }

        else {
            $data = $input ? array_merge([
                $data_key => $input,
            ], (array) $data) : [];

            db()->insert('tools_usage', [
                'tool_id' => $tool_id,
                'total_views' => 1,
                'total_submissions' => 1,
                'data' => json_encode($data),
            ]);
        }
    }

    public function dns_lookup() {
        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'host' => '',
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        if(!empty($_POST)) {
            $_POST['host'] = trim(query_clean($_POST['host']));

            if(filter_var($_POST['host'], FILTER_VALIDATE_URL)) {
                $_POST['host'] = parse_url($_POST['host'], PHP_URL_HOST);
            }

            /* Check for any errors */
            $required_fields = ['host'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            $data['result'] = [];

            foreach([DNS_A, DNS_AAAA, DNS_CNAME, DNS_MX, DNS_NS, DNS_TXT, DNS_SOA, DNS_CAA] as $dns_type) {
                $dns_records = @dns_get_record($_POST['host'] . '.', $dns_type);

                if($dns_records) {
                    foreach($dns_records as $dns_record) {
                        if(!isset($data['result'][$dns_record['type']])) {
                            $data['result'][$dns_record['type']] = [$dns_record];
                        } else {
                            $data['result'][$dns_record['type']][] = $dns_record;
                        }
                    }
                }
            }

            if(empty($data['result'])) {
                Alerts::add_field_error('host', l('tools.dns_lookup.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate($data['input']['host'], 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/dns_lookup', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function ip_lookup() {
        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'ip' => get_ip(),
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        if(!empty($_POST)) {
            $_POST['ip'] = trim(query_clean($_POST['ip']));

            /* Check for any errors */
            $required_fields = ['ip'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!filter_var($_POST['ip'], FILTER_VALIDATE_IP)) {
                Alerts::add_field_error('ip', l('tools.ip_lookup.error_message'));
            }

            try {
                $maxmind = (get_maxmind_reader_city())->get($_POST['ip']);

                if(is_array($maxmind) && empty(array_intersect_key($maxmind, array_flip(['continent', 'country', 'city', 'location'])))) {
                    Alerts::add_field_error('ip', l('tools.ip_lookup.error_message'));
                }
            } catch(\Exception $exception) {
                Alerts::add_field_error('ip', l('tools.ip_lookup.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $data['result'] = $maxmind;

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input'], ['country_code' => $maxmind['country']['iso_code'] ?? null]);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate($data['input']['ip'], 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/ip_lookup', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function ssl_lookup() {
        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'host' => '',
                'port' => 443,
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        if(!empty($_POST)) {
            $_POST['host'] = trim(query_clean($_POST['host']));
            $_POST['port'] = (int) $_POST['port'];

            if(filter_var($_POST['host'], FILTER_VALIDATE_URL)) {
                $_POST['host'] = parse_url($_POST['host'], PHP_URL_HOST);
            }

            /* Check for any errors */
            $required_fields = ['host'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Check for an SSL certificate */
            $certificate = get_website_certificate('https://' . $_POST['host'], $_POST['port']);

            if(!$certificate) {
                Alerts::add_field_error('host', l('tools.ssl_lookup.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $data['result'] = $certificate;

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate($data['input']['host'] . ':' . $data['input']['port'], 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/ssl_lookup', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function whois_lookup() {
        $this->initiate();

        $data = [];

        if(!empty($_POST)) {
            $_POST['domain_name'] = input_clean($_POST['domain_name']);

            if(filter_var($_POST['domain_name'], FILTER_VALIDATE_URL)) {
                $_POST['domain_name'] = parse_url($_POST['domain_name'], PHP_URL_HOST);
            }

            /* Check for any errors */
            $required_fields = ['domain_name'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            $domain_name = get_domain_from_host($_POST['domain_name']);
            $domain_name_ascii = get_idn_ascii_domain($domain_name);

            /* RDAP check */
            if($domain_name_ascii) {
                $rdap_dns_servers = get_rdap_dns_map();
                $tld = get_domain_tld($domain_name_ascii);

                if($tld && isset($rdap_dns_servers[$tld])) {
                    $whois = get_domain_info_rdap($rdap_dns_servers[$tld], $domain_name_ascii);
                }
            }

            if(
                !isset($whois)
                || empty($whois['start_datetime'])
                || empty($whois['updated_datetime'])
                || empty($whois['end_datetime'])
//                || empty($whois['registrar'])
//                || empty($whois['nameservers'])
            ) {
                try {
                    /* Create the original socket loader */
                    $socket_loader = new \Iodev\Whois\Loaders\SocketLoader();
                    $socket_loader->setTimeout(5); /* connection timeout in seconds */

                    /* Create whois instance using the factory with the custom loader */
                    $get_whois = \Iodev\Whois\Factory::get()->createWhois($socket_loader);

                    /* Load whois information */
                    $whois_info = $get_whois->loadDomainInfo($domain_name);
                } catch (\Exception $exception) {
                    /* handle exception or timeout */
                }

                $whois = isset($whois_info) && $whois_info ? [
                    'start_datetime' => $whois_info->creationDate ? (new \DateTime())->setTimestamp($whois_info->creationDate)->format('Y-m-d H:i:s') : null,
                    'updated_datetime' => $whois_info->updatedDate ? (new \DateTime())->setTimestamp($whois_info->updatedDate)->format('Y-m-d H:i:s') : null,
                    'end_datetime' => $whois_info->expirationDate ? (new \DateTime())->setTimestamp($whois_info->expirationDate)->format('Y-m-d H:i:s') : null,
                    'registrar' => $whois_info->registrar,
                    'nameservers' => $whois_info->nameServers,
                ] : [];
            }

            if(empty($whois)) {
                Alerts::add_field_error('domain_name', l('tools.whois_lookup.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $this->process_usage();


                $data['result'] = $whois;

            }
        }

        $values = [
            'domain_name' => $_POST['domain_name'] ?? '',
        ];

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/whois_lookup', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function ping() {
        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'target' => '',
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        /* Get available ping servers */
        $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();
        $data['ping_servers'] = $ping_servers;

        if(!empty($_POST)) {
            $_POST['target'] = input_clean($_POST['target']);

            /* Check for any errors */
            $required_fields = ['target'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $target = (new \StdClass());
                $target->type = 'website';
                $target->target = $_POST['target'];
                $target->port = $_POST['port'] ?? 0;
                $target->ping_servers_ids = [1];
                $target->settings = (new \StdClass());
                $target->settings->timeout_seconds = 5;
                $target->settings->request_method = 'get';
                $target->settings->request_basic_auth_username = '';
                $target->settings->request_basic_auth_password = '';
                $target->settings->request_headers = [];
                $target->settings->response_status_code = 200;

                /* Do the check */
                $check = \Altum\Helpers\Monitor::check($target, $ping_servers);

                $data['result'] = $check;

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate($data['input']['target'], 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/ping', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function ttfb_checker() {
        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'url' => '',
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        if(!empty($_POST)) {
            $_POST['url'] = get_url($_POST['url']);

            /* Check for any errors */
            $required_fields = ['url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Get the URL source */
            try {
                $response = \Unirest\Request::get($_POST['url']);
            } catch (\Exception $exception) {
                Alerts::add_field_error('url', l('tools.error_message.url'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Get info after the request */
                $info = \Unirest\Request::getInfo();

                $data['result'] = $info['pretransfer_time'];

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate(remove_url_protocol_from_url($data['input']['url']), 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/ttfb_checker', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function website_page_size_checker() {
        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'url' => '',
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        if(!empty($_POST)) {
            $_POST['url'] = get_url($_POST['url']);

            /* Check for any errors */
            $required_fields = ['url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Get the URL source */
            try {
                $response = \Unirest\Request::get($_POST['url']);
            } catch (\Exception $exception) {
                Alerts::add_field_error('url', l('tools.error_message.url'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Get info after the request */
                $html = $response->raw_body;

                $data['result'] = mb_strlen($html);

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate(remove_url_protocol_from_url($data['input']['url']), 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/website_page_size_checker', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function website_text_extractor() {
        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'url' => '',
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        if(!empty($_POST)) {
            $_POST['url'] = get_url($_POST['url']);

            /* Check for any errors */
            $required_fields = ['url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Get the URL source */
            try {
                $response = \Unirest\Request::get($_POST['url']);
            } catch (\Exception $exception) {
                Alerts::add_field_error('url', l('tools.website_text_extractor.error_message'));
            }

            $html = $response->raw_body;

            if(mb_detect_encoding($html, 'UTF-8', true) === false) {
                $html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
            }
            /* Start parsing page content */
            $dom = new \DOMDocument('1.0', 'UTF-8');
            libxml_use_internal_errors(true);

            if(!$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
                Alerts::add_field_error('url', l('tools.website_text_extractor.error_message'));
            }

            libxml_clear_errors();
            libxml_use_internal_errors(false);

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $body = $dom->getElementsByTagName('body')->item(0);

                // Remove script, style, and noscript elements (e.g. schema markup)
                $xpath = new \DOMXPath($dom);
                foreach ($xpath->query('.//script | .//style | .//noscript', $body) as $node) {
                    $node->parentNode->removeChild($node);
                }

                $text = $body->textContent;

                // Collapse multiple spaces/tabs into one space
                $text = preg_replace('/[ \t]+/', ' ', $text);
                // Collapse multiple newlines (with optional spaces) into a single newline
                $text = preg_replace('/\n\s*\n+/', "\n\n", $text);

                $data['result'] = $text;

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate(remove_url_protocol_from_url($data['input']['url']), 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/website_text_extractor', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function meta_tags_checker() {
        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'url' => '',
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        if(!empty($_POST)) {
            $_POST['url'] = get_url($_POST['url']);

            /* Check for any errors */
            $required_fields = ['url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Get the URL source */
            try {
                $response = \Unirest\Request::get($_POST['url']);
            } catch (\Exception $exception) {
                Alerts::add_field_error('url', l('tools.error_message.url'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $doc = new \DOMDocument('1.0', 'UTF-8');
                @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $response->raw_body);

                $meta_tags_array = $doc->getElementsByTagName('meta');
                $meta_tags = [];

                for($i = 0; $i < $meta_tags_array->length; $i++) {
                    $meta_tag = $meta_tags_array->item($i);

                    $meta_tag_key = !empty($meta_tag->getAttribute('name')) ? $meta_tag->getAttribute('name') : $meta_tag->getAttribute('property');

                    if($meta_tag_key) {
                        $meta_tags[$meta_tag_key] = $meta_tag->getAttribute('content');
                    }
                }

                $data['result'] = $meta_tags;

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate(remove_url_protocol_from_url($data['input']['url']), 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/meta_tags_checker', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function website_hosting_checker() {
        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'host' => '',
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }


        if(!empty($_POST)) {
            $_POST['host'] = trim(query_clean($_POST['host']));

            if(filter_var($_POST['host'], FILTER_VALIDATE_URL)) {
                $_POST['host'] = parse_url($_POST['host'], PHP_URL_HOST);
            }

            /* Check for any errors */
            $required_fields = ['host'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Get ip of host */
            $host_ip = gethostbyname($_POST['host']);

            /* Check via ip-api */
            try {
                $response = \Unirest\Request::get('http://ip-api.com/json/' . $host_ip);

                if(empty($response->raw_body) || $response->body->status == 'fail') {
                    Alerts::add_field_error('host', l('tools.website_hosting_checker.error_message'));
                }
            } catch (\Exception $exception) {
                Alerts::add_field_error('host', l('tools.website_hosting_checker.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $data['result'] = $response->body;

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate($data['input']['host'], 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/website_hosting_checker', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function http_headers_lookup() {
        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'url' => '',
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        if(!empty($_POST)) {
            $_POST['url'] = get_url($_POST['url']);

            /* Check for any errors */
            $required_fields = ['url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            try {
                $response = \Unirest\Request::get($_POST['url']);
            } catch (\Exception $exception) {
                Alerts::add_field_error('url', l('tools.error_message.url'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $data['result'] = $response->headers;

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate(remove_url_protocol_from_url($data['input']['url']), 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/http_headers_lookup', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function http2_checker() {

        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'url' => '',
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        if(!empty($_POST)) {
            $_POST['url'] = get_url($_POST['url']);

            /* Check for any errors */
            $required_fields = ['url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            try {
                $response = \Unirest\Request::get($_POST['url']);
            } catch (\Exception $exception) {
                Alerts::add_field_error('url', l('tools.error_message.url'));
            }

            $curl_info = \Unirest\Request::getInfo();

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $data['result'] = $curl_info['http_version'] == 3;

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate(remove_url_protocol_from_url($data['input']['url']), 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/http2_checker', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function brotli_checker() {

        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'url' => '',
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        if(!empty($_POST)) {
            $_POST['url'] = get_url($_POST['url']);

            /* Check for any errors */
            $required_fields = ['url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            try {
                $response = \Unirest\Request::get($_POST['url'], ['Accept-Encoding' => 'br']);
            } catch (\Exception $exception) {
                Alerts::add_field_error('url', l('tools.error_message.url'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $is_brotli_enabled = false;

                $response->headers = array_change_key_case($response->headers, CASE_LOWER);

                if(isset($response->headers['content-encoding']) && str_contains($response->headers['content-encoding'], 'br')) {
                    $is_brotli_enabled = true;
                }

                $data['result'] = $is_brotli_enabled;

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate(remove_url_protocol_from_url($data['input']['url']), 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/brotli_checker', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function google_cache_checker() {
        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'url' => '',
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        if(!empty($_POST)) {
            $_POST['url'] = get_url($_POST['url']);

            /* Check for any errors */
            $required_fields = ['url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Get the URL source */
            $url = 'http://webcache.googleusercontent.com/search?hl=en&q=cache:' . urlencode($_POST['url']) . '&strip=0&vwsrc=1';
            try {
                $response = \Unirest\Request::get($url, [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0'
                ]);
            } catch (\Exception $exception) {
                Alerts::add_field_error('url', l('tools.error_message.url'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                /* Get details from the google query result */
                preg_match('/It is a snapshot of the page as it appeared on ([^\.]+)\./i', $response->raw_body, $matches);

                $data['result'] = empty($matches) ? false : $matches[1];

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate(remove_url_protocol_from_url($data['input']['url']), 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/google_cache_checker', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function url_redirect_checker() {
        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'url' => '',
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        if(!empty($_POST)) {
            $_POST['url'] = get_url($_POST['url']);

            /* Check for any errors */
            $required_fields = ['url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Save locations of each request */
            $locations = [];

            /* Get the URL source */
            $i = 1;
            $url = $_POST['url'];

            /* Start the requests process */
            do {
                try {
                    \Unirest\Request::curlOpt(CURLOPT_FOLLOWLOCATION, 0);
                    $response = \Unirest\Request::get($url, [
                        'User-Agent' => settings()->main->title . ' ' . url('tools/url_redirect_checker') . '/1.0'
                    ]);

                    $locations[] = [
                        'url' => $url,
                        'status_code' => $response->code,
                        'redirect_to' => $response->headers['Location'] ?? $response->headers['location'] ?? null,
                    ];

                    $i++;
                    $url = $response->headers['Location'] ?? $response->headers['location'] ?? null;
                } catch (\Exception $exception) {
                    Alerts::add_field_error('url', l('tools.error_message.url'));
                    break;
                }
            } while($i <= 10 && ($response->code == 301 || $response->code == 302));

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $data['result'] = $locations;

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate(remove_url_protocol_from_url($data['input']['url']), 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/url_redirect_checker', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function reverse_ip_lookup() {

        $this->initiate();

        $data = [
            'tools_usage' => $this->tools_usage,
            'input' => [],
            'input_fields' => [
                'ip' => get_ip(),
            ]
        ];

        if(empty($_POST) && isset($_GET['submit'])) {
            foreach($data['input_fields'] as $field_key => $field_default_value) {
                $_POST[$field_key] = $_GET[$field_key] ?? $field_default_value;
            }
        }

        if(!empty($_POST)) {
            $_POST['ip'] = input_clean($_POST['ip']);

            /* Check for any errors */
            $required_fields = ['ip'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!filter_var($_POST['ip'], FILTER_VALIDATE_IP)) {
                Alerts::add_field_error('ip', l('tools.reverse_ip_lookup.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $data['result'] = gethostbyaddr($_POST['ip']);

                foreach($data['input_fields'] as $field_key => $field_default_value) {
                    $data['input'][$field_key] = $_POST[$field_key] ?? $field_default_value;
                }

                $this->process_usage($data['input']);

                /* Meta & title */
                Title::set(sprintf(l('tools.tool_title_submission'), l('tools.' . \Altum\Router::$method . '.name'), string_truncate($data['input']['ip'], 32)));
                Meta::set_canonical_url(url(\Altum\Router::$method) . '?' . http_build_query((array) $data['input']));
            }
        }

        $values = [];
        foreach($data['input_fields'] as $field_key => $field_default_value) {
            $values[$field_key] = $_POST[$field_key] ?? $_GET[$field_key] ?? $field_default_value;
        }

        /* Prepare the view */
        $data['values'] = $values;

        $view = new \Altum\View('tools/reverse_ip_lookup', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
