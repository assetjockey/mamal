<?php
defined('ALTUMCODE') || die();

return [
	'collectors' => [
		/* Submitted data */
		'name' => [
			'label' => fn($language) => l('global.name', $language),
			'emoji' => '👤',
			'order' => 10,
		],

		'email' => [
			'label' => fn($language) => l('global.email', $language),
			'emoji' => '✉️',
			'order' => 20,
		],

		'phone' => [
			'label' => fn($language) => l('global.phone', $language),
			'emoji' => '📞',
			'order' => 30,
		],

		'message' => [
			'label' => fn($language) => l('global.message', $language),
			'emoji' => '💬',
			'order' => 40,
		],

		'input' => [
			'label' => fn($language) => l('global.message', $language),
			'emoji' => '📝',
			'order' => 50,
		],

		/* Internal reference data */
		'notification_name' => [
			'label' => fn($language) => l('notifications.notification', $language),
			'emoji' => '🔔',
			'order' => 100,
		],

		'campaign_name' => [
			'label' => fn($language) => l('campaigns.campaign', $language),
			'emoji' => '📣',
			'order' => 110,
		],

		'tracked_url' => [
			'label' => fn($language) => l('global.url', $language),
			'emoji' => '🔗',
			'order' => 120,
		],

		/* Location data */
		'country' => [
			'label' => fn($language) => l('global.country', $language),
			'emoji' => '🌎',
			'order' => 200,
		],

		'city' => [
			'label' => fn($language) => l('global.city', $language),
			'emoji' => '🏙️',
			'order' => 210,
		],

		'continent' => [
			'label' => fn($language) => l('global.continent', $language),
			'emoji' => '🌍',
			'order' => 220,
		],

		/* Hidden / technical data */
		'notification_id' => [
			'label' => fn($language) => l('notifications.notification', $language),
			'emoji' => '🔔',
			'order' => 300,
			'is_visible' => false,
		],

		'campaign_id' => [
			'label' => fn($language) => l('campaigns.campaign', $language),
			'emoji' => '📣',
			'order' => 310,
			'is_visible' => false,
		],

		'country_code' => [
			'label' => fn($language) => l('global.country_code', $language),
			'emoji' => '🏳️',
			'order' => 320,
			'is_visible' => false,
		],

		'continent_code' => [
			'label' => fn($language) => l('global.continent_code', $language),
			'emoji' => '🧭',
			'order' => 330,
			'is_visible' => false,
		],

		'url' => [
			'label' => fn($language) => l('global.view_details', $language),
			'emoji' => '👁️',
			'order' => 340,
			'is_visible' => false,
		],
	],
];
