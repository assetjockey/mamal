<?php
defined('ALTUMCODE') || die();

return [
	'biolink_blocks' => [
		/* Base biolink data */
		'link_id' => [
			'label' => fn($language) => l('link.biolink', $language),
			'emoji' => '🔗',
			'order' => 10,
			'is_visible' => false,
		],

		'biolink_block_id' => [
			'label' => fn($language) => l('biolink_block.biolink_block', $language),
			'emoji' => '🧩',
			'order' => 20,
			'is_visible' => false,
		],


		'biolink_block_name' => [
			'label' => fn($language) => l('biolink_block.title', $language),
			'emoji' => '🧩',
			'order' => 21,
		],

		'link_url' => [
			'label' => fn($language) => l('link.settings.url', $language),
			'emoji' => '🔗',
			'order' => 22,
		],

		/* View data url */
		'url' => [
			'label' => fn($language) => l('global.url', $language),
			'emoji' => '👁️',
			'order' => 30,
			'is_visible' => false,
		],

		/* Submitted form data */
		'name' => [
			'label' => fn($language) => l('global.name', $language),
			'emoji' => '👤',
			'order' => 100,
		],

		'email' => [
			'label' => fn($language) => l('global.email', $language),
			'emoji' => '✉️',
			'order' => 110,
		],

        'phone' => [
            'label' => fn($language) => l('global.phone', $language),
            'emoji' => '📞',
            'order' => 120,
        ],

        'message' => [
            'label' => fn($language) => l('global.message', $language),
            'emoji' => '💬',
            'order' => 130,
        ],

        'date' => [
            'label' => fn($language) => l('biolink_appointment_calendar.date', $language),
            'emoji' => '📅',
            'order' => 140,
        ],

        'time' => [
            'label' => fn($language) => l('biolink_appointment_calendar.time', $language),
            'emoji' => '🕒',
            'order' => 150,
        ],

        'duration' => [
            'label' => fn($language) => l('biolink_appointment_calendar.duration', $language),
            'emoji' => '⏱️',
            'order' => 160,
        ],

        'processor' => [
            'label' => fn($language) => l('guests_payments.processor', $language),
            'emoji' => '💳',
            'order' => 170,
            'formatter' => fn($value, $timezone, $notification_data, $language) => l('pay.custom_plan.' . $value , $language),
        ],

        'total_amount' => [
            'label' => fn($language) => l('guests_payments.total_amount', $language),
            'emoji' => '💰',
            'order' => 180,
            'formatter' => fn($value, $timezone, $notification_data) => $value . ' ' . $notification_data['currency'],
        ],

        'currency' => [
            'label' => fn($language) => l('global.currency', $language),
            'emoji' => '💱',
            'order' => 190,
            'is_visible' => false,
        ],
	],
];
