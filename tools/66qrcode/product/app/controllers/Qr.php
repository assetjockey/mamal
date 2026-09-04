<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
 *
 * This software is proprietary software owned and licensed by AltumCode.
 * A valid license is required to use, modify, or distribute this software.
 * Unauthorized use, reproduction, modification, or distribution is prohibited.
 *
 * 🌍 Explore all AltumCode projects: https://altumcode.com/
 * 📧 Support & general inquiries: https://altumcode.com/contact
 * 📤 Download the latest version: https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 */

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Meta;
use Altum\Title;

defined('ALTUMCODE') || die();

class Qr extends Controller {

    public function index() {

        if(!settings()->codes->qr_codes_is_enabled) {
            throw_404();
        }

        if(is_logged_in()) {
            redirect('qr-code-create');
        }

        if(!settings()->plan_guest->status) {
            Alerts::add_error(l('global.info_message.plan_feature_no_access'));
            redirect('dashboard');
        }

        $available_qr_codes = require APP_PATH . 'includes/enabled_qr_codes.php';
        unset($available_qr_codes['file']);
        $frames = require APP_PATH . 'includes/qr_codes_frames.php';
        $frames_fonts = require APP_PATH . 'includes/qr_codes_frames_text_fonts.php';
        $styles = require APP_PATH . 'includes/qr_codes_styles.php';
        $inner_eyes = require APP_PATH . 'includes/qr_codes_inner_eyes.php';
        $outer_eyes = require APP_PATH . 'includes/qr_codes_outer_eyes.php';

        $type = isset($this->params[0]) && array_key_exists($this->params[0], $available_qr_codes) ? $this->params[0] : null;

        if($type) {
            if(!isset($this->user->plan_settings->enabled_qr_codes->{$type}) || !$this->user->plan_settings->enabled_qr_codes->{$type}) {
                Alerts::add_error(l('global.info_message.plan_feature_no_access'));
                redirect('qr');
            }

            /* Set a custom title */
            Title::set(sprintf(l('qr.title_dynamic'), l('qr_codes.type.' . $type)));
            Meta::set_description(l('qr_codes.type.' . $type . '_description'));
            Meta::set_keywords(l('qr_codes.type.' . $type . '_meta_keywords'));

            if($type == 'url' && is_logged_in()) {
                /* Existing links */
                $links = (new \Altum\Models\Link())->get_full_links_by_user_id($this->user->user_id);
                foreach($links as $link_id => $link) {
                    if($link->type == 'file') unset($links[$link_id]);
                }
            }

            /* Process dynamic view */
            $data = [
                'available_qr_codes' => $available_qr_codes,
                'frames_fonts' => $frames_fonts,
                'frames' => $frames,
                'styles' => $styles,
                'inner_eyes' => $inner_eyes,
                'outer_eyes' => $outer_eyes,
                'links' => $links ?? [],
            ];
            $view = new \Altum\View('qr/partials/' . $type . '_form', (array) $this);
            $this->add_view_content('qr_form', $view->run($data));
        }

        /* Main View */
        $data = [
            'type' => $type,
            'available_qr_codes' => $available_qr_codes,
            'frames_fonts' => $frames_fonts,
            'frames' => $frames,
            'styles' => $styles,
            'inner_eyes' => $inner_eyes,
            'outer_eyes' => $outer_eyes,
            'links' => $links ?? [],
        ];

        $view = new \Altum\View('qr/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
