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

class PixelServiceWorker extends Controller {

    public function index() {
        $seconds_to_cache = settings()->websites->pixel_cache;
        header('Content-Type: application/javascript');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $seconds_to_cache) . ' GMT');
        header('Pragma: cache');
        header('Cache-Control: max-age=' . $seconds_to_cache);

        /* Main View */
        $data = [];

        $view = new \Altum\View('pixel-service-worker/index', (array) $this);

        $view_data = $view->run($data);

        /* Remove <script> tags */
        $view_data = str_replace('<script>', '', $view_data);
        $view_data = str_replace('</script>', '', $view_data);

        echo $view_data;
    }

}
