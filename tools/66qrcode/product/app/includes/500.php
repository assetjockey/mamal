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

ini_set('display_errors', 'Off');

/* Error handlers */
function altumcode_shutdown_handler() {
    $last_error = error_get_last();

    if($last_error && ($last_error['type'] === E_ERROR || $last_error['type'] === E_CORE_ERROR || $last_error['type'] === E_PARSE || $last_error['type'] === E_COMPILE_ERROR)) {
        echo <<<ALTUM

<style>
    html {
        background: #161538;
        font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
        width: 100%;
        height: 100%;
        color: #eeeeee;
    }
    body {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    a {
        color: #c3beff;
        text-decoration: none;
    }
    .text-white {
        color: white;
    }
    .altumcode-logo {
        width: 3rem;
        height: auto;
    }
    .buttons {
        margin-top: 1.5rem;
    }
</style>

ALTUM;

        echo '<div>';
        echo '<h1 class="text-white">Internal server error</h1>';
        echo '<p>Our website is having some issues right now.</p>';
        echo '<p>We are actively working on fixing the issue.</p>';
        echo '</div>';
        die();

    }
}

/* Register error handlers */
register_shutdown_function('altumcode_shutdown_handler');

