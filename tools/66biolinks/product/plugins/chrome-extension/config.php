<?php
defined('ALTUMCODE') || die();

return (object) [
    'plugin_id' => 'chrome-extension',
    'name' => 'Chrome Extension',
    'description' => 'This plugin displays your chrome extension and lets your users easily connect with it.',
    'version' => '1.0.0',
    'url' => 'https://altumco.de/chrome-extension-plugin',
    'author' => 'AltumCode',
    'author_url' => 'https://altumcode.com/',
    'status' => 'inexistent',
    'actions' => true,
    'settings_url' => url('admin/settings/chrome_extension'),
    'avatar_style' => 'background-color: #ff00c2;background-image: linear-gradient(90deg, #b9c7f0 0%, #ff00c2 100%);',
    'icon' => '🧩',
];

