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

defined('ALTUMCODE') || die();

if(DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED & ~E_USER_DEPRECATED);
    ini_set('display_errors', 'Off');
}

if(LOGGING) {
    ini_set('log_errors', 1);
    ini_set('error_log', UPLOADS_PATH . 'logs/' . date('Y-m-d') . '.log');
} else {
    ini_set('log_errors', 0);
}

ini_set('html_errors', 0);

function debug_log($message) {
    error_log(
        '[' . date('Y-m-d H:i:s') . '] ' . $message,
        3,
        UPLOADS_PATH . 'logs/debug-' . date('Y-m-d') . '.log'
    );
}

