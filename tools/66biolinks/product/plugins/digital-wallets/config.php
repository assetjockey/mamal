<?php
defined('ALTUMCODE') || die();

return (object) [
    'plugin_id' => 'digital-wallets',
    'name' => 'Digital Wallets',
    'description' => 'Allow your users to save passes, tickets, coupons, and cards directly to Google Wallet.',
    'version' => '1.0.0',
    'url' => 'https://altumco.de/digital-wallets-plugin',
    'author' => 'AltumCode',
    'author_url' => 'https://altumcode.com/',
    'status' => 'inexistent',
    'actions'=> true,
    'settings_url' => url('admin/settings/digital_wallets'),
    'avatar_style' => 'background: #1e3a8a; background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 45%, #06b6d4 100%);',
    'icon' => '🛜',
];


