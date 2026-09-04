<?php
defined('ALTUMCODE') || die();

return (object) [
    'plugin_id' => 'newsletters',
    'name' => 'Newsletters',
    'description' => 'This plugin adds newsletter campaigns, subscribers, custom newsletter SMTP and email tracking for your users.',
    'version' => '1.0.0',
    'url' => 'https://altumco.de/newsletters-plugin',
    'author' => 'AltumCode',
    'author_url' => 'https://altumcode.com/',
    'status' => 'inexistent',
    'actions'=> true,
    'settings_url' => url('admin/settings/newsletters'),
    'avatar_style' => 'background: #e6cec9;linear-gradient(to right, #b1ebcb, #ffc2c7);',
    'icon' => '📰',
];
