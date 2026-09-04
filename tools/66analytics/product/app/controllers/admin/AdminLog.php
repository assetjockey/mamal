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

use Altum\Title;

defined('ALTUMCODE') || die();

class AdminLog extends Controller {

    public function index() {

        /* Clear files caches */
        clearstatcache();

        $log_id = isset($this->params[0]) ? input_clean($this->params[0]) : null;

        if(!$log_id) {
            redirect('admin/logs');
        }

        $log_id = preg_replace('/[^a-zA-Z0-9-]/', '', $log_id);

        if(!file_exists(UPLOADS_PATH . 'logs/' . $log_id . '.log')) {
            redirect('admin/logs');
        }

        $log = (object) [
            'name' => $log_id,
            'full_name' => $log_id . '.log',
            'extension' => 'log',
            'size' => filesize(UPLOADS_PATH . 'logs/' . $log_id . '.log'),
            'last_modified' => date('Y-m-d H:i:s', filemtime(UPLOADS_PATH . 'logs/' . $log_id . '.log')),
            'content' => new \SplFileObject(UPLOADS_PATH . 'logs/' . $log_id . '.log'),
        ];

        /* Set a custom title */
        Title::set(sprintf(l('admin_log.title'), $log_id));

        /* Main View */
        $data = [
            'log_id' => $log_id,
            'log' => $log,
        ];

        $view = new \Altum\View('admin/log/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
