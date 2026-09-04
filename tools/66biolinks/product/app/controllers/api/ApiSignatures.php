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

class ApiSignatures extends Controller {
    use Apiable;

    public function index() {

        if(!\Altum\Plugin::is_active('email-signatures') || !settings()->signatures->is_enabled) {
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
        $filters = (new \Altum\Filters(['user_id', 'project_id'], ['name'], ['signature_id', 'last_datetime', 'datetime', 'name'], allowed_datetime_fields: ['last_datetime', 'datetime']));
        $filters->set_default_order_by('signature_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `signatures` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/signatures?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `signatures`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->signature_id,
                'user_id' => (int) $row->user_id,
                'project_id' => (int) $row->project_id,
                'name' => $row->name,
                'template' => $row->template,
                'settings' => json_decode($row->settings ?? ''),
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

        $signature_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $signature = db()->where('signature_id', $signature_id)->where('user_id', $this->user->user_id)->getOne('signatures');

        /* We haven't found the resource */
        if(!$signature) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $signature->signature_id,
            'user_id' => (int) $signature->user_id,
            'project_id' => (int) $signature->project_id,
            'name' => $signature->name,
            'template' => $signature->template,
            'settings' => json_decode($signature->settings),
            'last_datetime' => $signature->last_datetime,
            'datetime' => $signature->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('signatures', 'count(`signature_id`)');

        if($this->user->plan_settings->signatures_limit != -1 && $total_rows >= $this->user->plan_settings->signatures_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Check for any errors */
        $required_fields = ['name'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Signature templates */
        $signature_templates = require \Altum\Plugin::get('email-signatures')->path . 'includes/signature_templates.php';

        /* Signature fonts */
        $signature_fonts = require \Altum\Plugin::get('email-signatures')->path . 'includes/signature_fonts.php';

        /* Signature socials */
        $signature_socials = require \Altum\Plugin::get('email-signatures')->path . 'includes/signature_socials.php';

        $_POST['name'] = input_clean($_POST['name'], 256);
        $_POST['template'] = array_key_exists($_POST['template'] ?? '', $signature_templates) ? $_POST['template'] : array_key_first($signature_templates);
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
        $direction = $_POST['direction'] ?? 'ltr';
        $_POST['direction'] = in_array($direction, ['rtl', 'ltr']) ? $direction : 'ltr';
        $_POST['is_removed_branding'] = $this->user->plan_settings->removable_branding ? (bool) ($_POST['is_removed_branding'] ?? false) : false;
        $_POST['image_url'] = get_url($_POST['image_url'] ?? '', 1024);
        $_POST['sign_off'] = input_clean($_POST['sign_off'] ?? l('signatures.sign_off.default'), 64);
        $_POST['full_name'] = input_clean($_POST['full_name'] ?? '', 64);
        $_POST['job_title'] = input_clean($_POST['job_title'] ?? '', 64);
        $_POST['department'] = input_clean($_POST['department'] ?? '', 64);
        $_POST['company'] = input_clean($_POST['company'] ?? '', 64);
        $_POST['email'] = input_clean_email($_POST['email'] ?? '');
        $_POST['website_name'] = input_clean($_POST['website_name'] ?? '', 256);
        $_POST['website_url'] = input_clean($_POST['website_url'] ?? '', 256);
        $_POST['address'] = input_clean($_POST['address'] ?? '', 256);
        $_POST['address_url'] = input_clean($_POST['address_url'] ?? '', 512);
        $_POST['phone_number'] = input_clean($_POST['phone_number'] ?? '', 32);
        $_POST['whatsapp'] = !empty($_POST['whatsapp']) ? (int) input_clean($_POST['whatsapp'], 32) : null;
        $_POST['facebook_messenger'] = input_clean($_POST['facebook_messenger'] ?? '', 64);
        $_POST['telegram'] = input_clean($_POST['telegram'] ?? '', 64);
        $_POST['disclaimer'] = input_clean($_POST['disclaimer'] ?? '', 1024);
        foreach($signature_socials as $key => $social) {
            $_POST[$key] = input_clean($_POST[$key] ?? '', $social['value_max_length']);
        }
        $_POST['font_family'] = array_key_exists($_POST['font_family'] ?? '', $signature_fonts) ? query_clean($_POST['font_family']) : array_key_first($signature_fonts);
        $font_size = (int) ($_POST['font_size'] ?? 14);
        $_POST['font_size'] = $font_size < 12 || $font_size > 18 ? 14 : $font_size;
        $width = (int) ($_POST['width'] ?? 500);
        $_POST['width'] = $width < 300 || $width > 600 ? 500 : $width;
        $image_width = (int) ($_POST['image_width'] ?? 50);
        $_POST['image_width'] = $image_width < 45 || $image_width > 150 ? 50 : $image_width;
        $image_border_radius = (int) ($_POST['image_border_radius'] ?? 0);
        $_POST['image_border_radius'] = $image_border_radius < 0 || $image_border_radius > 100 ? 0 : $image_border_radius;
        $socials_width = (int) ($_POST['socials_width'] ?? 20);
        $_POST['socials_width'] = $socials_width < 15 || $socials_width > 30 ? 20 : $socials_width;
        $socials_padding = (int) ($_POST['socials_padding'] ?? 10);
        $_POST['socials_padding'] = $socials_padding < 5 || $socials_padding > 15 ? 10 : $socials_padding;
        $separator_size = (int) ($_POST['separator_size'] ?? 1);
        $_POST['separator_size'] = $separator_size < 0 || $separator_size > 5 ? 1 : $separator_size;
        $border_radius = (int) ($_POST['border_radius'] ?? 10);
        $_POST['border_radius'] = $border_radius < 0 || $border_radius > 30 ? 10 : $border_radius;
        $_POST['background_color'] = isset($_POST['background_color']) && verify_hex_color($_POST['background_color']) ? $_POST['background_color'] : '#f9f9f9';
        $_POST['border_color'] = isset($_POST['border_color']) && verify_hex_color($_POST['border_color']) ? $_POST['border_color'] : '#e0e0e0';
        $_POST['theme_color'] = isset($_POST['theme_color']) && verify_hex_color($_POST['theme_color']) ? $_POST['theme_color'] : '#000000';
        $_POST['full_name_color'] = isset($_POST['full_name_color']) && verify_hex_color($_POST['full_name_color']) ? $_POST['full_name_color'] : '#000000';
        $_POST['text_color'] = isset($_POST['text_color']) && verify_hex_color($_POST['text_color']) ? $_POST['text_color'] : '#000000';
        $_POST['link_color'] = isset($_POST['link_color']) && verify_hex_color($_POST['link_color']) ? $_POST['link_color'] : '#000000';

        /* Prepare settings */
        $settings = [
            'direction' => $_POST['direction'],
            'is_removed_branding' => $_POST['is_removed_branding'],
            'image_url' => $_POST['image_url'],
            'sign_off' => $_POST['sign_off'],
            'full_name' => $_POST['full_name'],
            'job_title' => $_POST['job_title'],
            'department' => $_POST['department'],
            'company' => $_POST['company'],
            'email' => $_POST['email'],
            'website_name' => $_POST['website_name'],
            'website_url' => $_POST['website_url'],
            'address' => $_POST['address'],
            'address_url' => $_POST['address_url'],
            'phone_number' => $_POST['phone_number'],
            'whatsapp' => $_POST['whatsapp'],
            'facebook_messenger' => $_POST['facebook_messenger'],
            'telegram' => $_POST['telegram'],
            'disclaimer' => $_POST['disclaimer'],
            'font_family' => $_POST['font_family'],
            'font_size' => $_POST['font_size'],
            'width' => $_POST['width'],
            'image_width' => $_POST['image_width'],
            'image_border_radius' => $_POST['image_border_radius'],
            'socials_width' => $_POST['socials_width'],
            'socials_padding' => $_POST['socials_padding'],
            'separator_size' => $_POST['separator_size'],
            'border_radius' => $_POST['border_radius'],
            'background_color' => $_POST['background_color'],
            'border_color' => $_POST['border_color'],
            'theme_color' => $_POST['theme_color'],
            'full_name_color' => $_POST['full_name_color'],
            'text_color' => $_POST['text_color'],
            'link_color' => $_POST['link_color'],
        ];

        foreach($signature_socials as $key => $social) {
            $settings[$key] = $_POST[$key];
        }

        $settings = json_encode($settings);

        /* Database query */
        $signature_id = db()->insert('signatures', [
            'user_id' => $this->user->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'template' => $_POST['template'],
            'settings' => $settings,
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('signatures?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $signature_id,
            'user_id' => (int) $this->user->user_id,
            'project_id' => (int) $_POST['project_id'],
            'name' => $_POST['name'],
            'template' => $_POST['template'],
            'settings' => json_decode($settings),
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->user->user_id)->getValue('signatures', 'count(`signature_id`)');

        if($this->user->plan_settings->signatures_limit != -1 && $total_rows > $this->user->plan_settings->signatures_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->signatures_limit, mb_strtolower(l('signatures.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $signature_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $signature = db()->where('signature_id', $signature_id)->where('user_id', $this->user->user_id)->getOne('signatures');

        /* We haven't found the resource */
        if(!$signature) {
            $this->return_404();
        }

        if(isset($_POST['name']) && trim($_POST['name']) === '') {
            $this->response_error(l('global.error_message.empty_fields'), 401);
        }

        $signature->settings = json_decode($signature->settings ?? '') ?? (object) [];

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Signature templates */
        $signature_templates = require \Altum\Plugin::get('email-signatures')->path . 'includes/signature_templates.php';

        /* Signature fonts */
        $signature_fonts = require \Altum\Plugin::get('email-signatures')->path . 'includes/signature_fonts.php';

        /* Signature socials */
        $signature_socials = require \Altum\Plugin::get('email-signatures')->path . 'includes/signature_socials.php';

        $_POST['name'] = input_clean($_POST['name'] ?? $signature->name, 256);
        $_POST['template'] = array_key_exists($_POST['template'] ?? $signature->template, $signature_templates) ? ($_POST['template'] ?? $signature->template) : $signature->template;
        $project_id = $_POST['project_id'] ?? $signature->project_id;
        $_POST['project_id'] = !empty($project_id) && array_key_exists($project_id, $projects) ? (int) $project_id : null;
        $direction = $_POST['direction'] ?? ($signature->settings->direction ?? 'ltr');
        $_POST['direction'] = in_array($direction, ['rtl', 'ltr']) ? $direction : 'ltr';
        $_POST['is_removed_branding'] = $this->user->plan_settings->removable_branding ? (bool) ($_POST['is_removed_branding'] ?? ($signature->settings->is_removed_branding ?? false)) : false;
        $_POST['image_url'] = get_url($_POST['image_url'] ?? ($signature->settings->image_url ?? ''), 1024);
        $_POST['sign_off'] = input_clean($_POST['sign_off'] ?? ($signature->settings->sign_off ?? l('signatures.sign_off.default')), 64);
        $_POST['full_name'] = input_clean($_POST['full_name'] ?? ($signature->settings->full_name ?? ''), 64);
        $_POST['job_title'] = input_clean($_POST['job_title'] ?? ($signature->settings->job_title ?? ''), 64);
        $_POST['department'] = input_clean($_POST['department'] ?? ($signature->settings->department ?? ''), 64);
        $_POST['company'] = input_clean($_POST['company'] ?? ($signature->settings->company ?? ''), 64);
        $_POST['email'] = input_clean_email($_POST['email'] ?? ($signature->settings->email ?? ''));
        $_POST['website_name'] = input_clean($_POST['website_name'] ?? ($signature->settings->website_name ?? ''), 256);
        $_POST['website_url'] = input_clean($_POST['website_url'] ?? ($signature->settings->website_url ?? ''), 256);
        $_POST['address'] = input_clean($_POST['address'] ?? ($signature->settings->address ?? ''), 256);
        $_POST['address_url'] = input_clean($_POST['address_url'] ?? ($signature->settings->address_url ?? ''), 512);
        $_POST['phone_number'] = input_clean($_POST['phone_number'] ?? ($signature->settings->phone_number ?? ''), 32);
        $whatsapp = $_POST['whatsapp'] ?? ($signature->settings->whatsapp ?? null);
        $_POST['whatsapp'] = !empty($whatsapp) ? (int) input_clean($whatsapp, 32) : null;
        $_POST['facebook_messenger'] = input_clean($_POST['facebook_messenger'] ?? ($signature->settings->facebook_messenger ?? ''), 64);
        $_POST['telegram'] = input_clean($_POST['telegram'] ?? ($signature->settings->telegram ?? ''), 64);
        $_POST['disclaimer'] = input_clean($_POST['disclaimer'] ?? ($signature->settings->disclaimer ?? ''), 1024);
        foreach($signature_socials as $key => $social) {
            $_POST[$key] = input_clean($_POST[$key] ?? ($signature->settings->{$key} ?? ''), $social['value_max_length']);
        }
        $_POST['font_family'] = array_key_exists($_POST['font_family'] ?? ($signature->settings->font_family ?? ''), $signature_fonts) ? query_clean($_POST['font_family'] ?? $signature->settings->font_family) : array_key_first($signature_fonts);
        $font_size = (int) ($_POST['font_size'] ?? ($signature->settings->font_size ?? 14));
        $_POST['font_size'] = $font_size < 12 || $font_size > 18 ? 14 : $font_size;
        $width = (int) ($_POST['width'] ?? ($signature->settings->width ?? 500));
        $_POST['width'] = $width < 300 || $width > 600 ? 500 : $width;
        $image_width = (int) ($_POST['image_width'] ?? ($signature->settings->image_width ?? 50));
        $_POST['image_width'] = $image_width < 45 || $image_width > 150 ? 50 : $image_width;
        $image_border_radius = (int) ($_POST['image_border_radius'] ?? ($signature->settings->image_border_radius ?? 0));
        $_POST['image_border_radius'] = $image_border_radius < 0 || $image_border_radius > 100 ? 0 : $image_border_radius;
        $socials_width = (int) ($_POST['socials_width'] ?? ($signature->settings->socials_width ?? 20));
        $_POST['socials_width'] = $socials_width < 15 || $socials_width > 30 ? 20 : $socials_width;
        $socials_padding = (int) ($_POST['socials_padding'] ?? ($signature->settings->socials_padding ?? 10));
        $_POST['socials_padding'] = $socials_padding < 5 || $socials_padding > 15 ? 10 : $socials_padding;
        $separator_size = (int) ($_POST['separator_size'] ?? ($signature->settings->separator_size ?? 1));
        $_POST['separator_size'] = $separator_size < 0 || $separator_size > 5 ? 1 : $separator_size;
        $border_radius = (int) ($_POST['border_radius'] ?? ($signature->settings->border_radius ?? 10));
        $_POST['border_radius'] = $border_radius < 0 || $border_radius > 30 ? 10 : $border_radius;
        $background_color = $_POST['background_color'] ?? ($signature->settings->background_color ?? '#f9f9f9');
        $_POST['background_color'] = verify_hex_color($background_color) ? $background_color : '#f9f9f9';
        $border_color = $_POST['border_color'] ?? ($signature->settings->border_color ?? '#e0e0e0');
        $_POST['border_color'] = verify_hex_color($border_color) ? $border_color : '#e0e0e0';
        $theme_color = $_POST['theme_color'] ?? ($signature->settings->theme_color ?? '#000000');
        $_POST['theme_color'] = verify_hex_color($theme_color) ? $theme_color : '#000000';
        $full_name_color = $_POST['full_name_color'] ?? ($signature->settings->full_name_color ?? '#000000');
        $_POST['full_name_color'] = verify_hex_color($full_name_color) ? $full_name_color : '#000000';
        $text_color = $_POST['text_color'] ?? ($signature->settings->text_color ?? '#000000');
        $_POST['text_color'] = verify_hex_color($text_color) ? $text_color : '#000000';
        $link_color = $_POST['link_color'] ?? ($signature->settings->link_color ?? '#000000');
        $_POST['link_color'] = verify_hex_color($link_color) ? $link_color : '#000000';

        /* Prepare settings */
        $settings = [
            'direction' => $_POST['direction'],
            'is_removed_branding' => $_POST['is_removed_branding'],
            'image_url' => $_POST['image_url'],
            'sign_off' => $_POST['sign_off'],
            'full_name' => $_POST['full_name'],
            'job_title' => $_POST['job_title'],
            'department' => $_POST['department'],
            'company' => $_POST['company'],
            'email' => $_POST['email'],
            'website_name' => $_POST['website_name'],
            'website_url' => $_POST['website_url'],
            'address' => $_POST['address'],
            'address_url' => $_POST['address_url'],
            'phone_number' => $_POST['phone_number'],
            'whatsapp' => $_POST['whatsapp'],
            'facebook_messenger' => $_POST['facebook_messenger'],
            'telegram' => $_POST['telegram'],
            'disclaimer' => $_POST['disclaimer'],
            'font_family' => $_POST['font_family'],
            'font_size' => $_POST['font_size'],
            'width' => $_POST['width'],
            'image_width' => $_POST['image_width'],
            'image_border_radius' => $_POST['image_border_radius'],
            'socials_width' => $_POST['socials_width'],
            'socials_padding' => $_POST['socials_padding'],
            'separator_size' => $_POST['separator_size'],
            'border_radius' => $_POST['border_radius'],
            'background_color' => $_POST['background_color'],
            'border_color' => $_POST['border_color'],
            'theme_color' => $_POST['theme_color'],
            'full_name_color' => $_POST['full_name_color'],
            'text_color' => $_POST['text_color'],
            'link_color' => $_POST['link_color'],
        ];

        foreach($signature_socials as $key => $social) {
            $settings[$key] = $_POST[$key];
        }

        $settings = json_encode($settings);

        /* Database query */
        db()->where('signature_id', $signature->signature_id)->update('signatures', [
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'template' => $_POST['template'],
            'settings' => $settings,
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('signatures?user_id=' . $this->user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $signature->signature_id,
            'user_id' => (int) $signature->user_id,
            'project_id' => (int) $_POST['project_id'],
            'name' => $_POST['name'],
            'template' => $_POST['template'],
            'settings' => json_decode($settings),
            'last_datetime' => get_date(),
            'datetime' => $signature->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $signature_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $signature = db()->where('signature_id', $signature_id)->where('user_id', $this->user->user_id)->getOne('signatures');

        /* We haven't found the resource */
        if(!$signature) {
            $this->return_404();
        }

        /* Delete the signature */
        db()->where('signature_id', $signature_id)->delete('signatures');

        /* Clear the cache */
        cache()->deleteItem('signatures?user_id=' . $this->user->user_id);

        http_response_code(200);
        die();

    }

}
