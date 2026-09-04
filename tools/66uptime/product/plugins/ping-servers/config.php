<?php
defined('ALTUMCODE') || die();

return (object) [
    'plugin_id' => 'ping-servers',
    'name' => 'Multi ping servers',
    'description' => 'The multi-location checker plugin is designed to connect your system with external servers and offer monitoring from across the world.',
    'version' => '1.0.0',
    'url' => 'https://altumco.de/66uptime-ping-servers-plugin',
    'author' => 'AltumCode',
    'author_url' => 'https://altumcode.com/',
    'status' => 'inexistent',
    'actions'=> true,
    'settings_url' => url('admin/settings/affiliate'),
    'avatar_style' => 'background: #70e1f5;background: linear-gradient(to right, #ffd194, #70e1f5);',
    'icon' => '🌎',
];
