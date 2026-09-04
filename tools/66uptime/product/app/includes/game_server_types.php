<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
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

defined('ALTUMCODE') || die();

return [
    'minecraft' => [
        'protocol' => 'minecraft',
        'name' => 'Minecraft',
        'icon' => 'minecraft.svg',
        'port' => 25565,
    ],

    /* GoldSrc */
    'cs1.6' => [
        'protocol' => 'gold_source',
        'name' => 'Counter-Strike 1.6',
        'icon' => 'cs1.6.png',
        'port' => 27015,
    ],
    'cscz' => [
        'protocol' => 'gold_source',
        'name' => 'Counter-Strike: Condition Zero',
        'icon' => 'cscz.png',
        'port' => 27015,
    ],

    /* Source */
    'css' => [
        'protocol' => 'source',
        'name' => 'Counter-Strike: Source',
        'icon' => 'css.png',
        'port' => 27015,
    ],
    'csgo' => [
        'protocol' => 'source',
        'name' => 'Counter-Strike: Global Offensive',
        'icon' => 'csgo.png',
        'port' => 27015,
    ],
    'cs2'  => [
        'protocol' => 'source',
        'name' => 'Counter-Strike 2',
        'icon' => 'cs2.png',
        'port' => 27015,
    ],

    'arkse'  => [
        'protocol' => 'source',
        'name' => 'ARK: Survival Evolved',
        'icon' => 'arkse.png',
        'port' => 27015,
    ],

    'rust'  => [
        'protocol' => 'source',
        'name' => 'Rust',
        'icon' => 'rust.png',
        'port' => 27015,
    ],

    'garrysmod'  => [
        'protocol' => 'source',
        'name' => 'Garry\'s Mod',
        'icon' => 'garrysmod.png',
        'port' => 27015,
    ],

    'dayz'  => [
        'protocol' => 'source',
        'name' => 'Day Z',
        'icon' => 'dayz.png',
        'port' => 2302,
        'query_port' => 27015,
    ],
];
