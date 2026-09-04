<?php
defined('ALTUMCODE') || die();

return [
    'transfers' => [
        /* Transfer data */
        'transfer_id' => [
            'is_visible' => false,
        ],

		'transfer_request_id' => [
			'is_visible' => false,
		],

        'name' => [
            'label' => fn($language) => l('global.name', $language),
            'emoji' => '🏷️',
            'order' => 20,
            'is_visible' => false,
        ],

        'total_files' => [
            'label' => fn($language) => l('transfer.total_files', $language),
            'emoji' => '📁',
            'order' => 25,
        ],

        'total_size' => [
            'label' => fn($language) => l('transfer.total_size', $language),
            'emoji' => '💾',
            'order' => 26,
        ],

        'url' => [
            'label' => fn($language) => l('global.view_details', $language),
            'emoji' => '👁️',
            'order' => 30,
            'is_visible' => false,
        ],

        /* Location data */
        'city_name' => [
            'label' => fn($language) => l('global.city', $language),
            'emoji' => '🏙️',
            'order' => 100,
        ],

        'country_code' => [
            'label' => fn($language) => l('global.country', $language),
            'emoji' => '🌎',
            'order' => 110,
        ],

        'continent_code' => [
            'label' => fn($language) => l('global.continent', $language),
            'emoji' => '🌍',
            'order' => 120,
        ],

        /* Device data */
        'os_name' => [
            'label' => fn($language) => l('global.os_name', $language),
            'emoji' => '💻',
            'order' => 200,
        ],

        'browser_name' => [
            'label' => fn($language) => l('global.browser_name', $language),
            'emoji' => '🌐',
            'order' => 210,
        ],

        'browser_language' => [
            'label' => fn($language) => l('global.browser_language', $language),
            'emoji' => '🗣️',
            'order' => 220,
        ],

        'device_type' => [
            'label' => fn($language) => l('global.device', $language),
            'emoji' => '📱',
            'order' => 230,
        ],
    ],
];
