<?php

namespace Altum;

defined('ALTUMCODE') || die();

class NotificationHandlers {
	public const HTML_SUPPORTED_HANDLERS = [
		'telegram',
	];

	public const MARKDOWN_SUPPORTED_HANDLERS = [
		'discord',
		'ntfy',
		'slack',
	];

	public const DYNAMIC_DATA_DEFAULT_ORDER = 9999;
	public const DYNAMIC_DATA_FORMAT_PLAIN = 'plain';
	public const DYNAMIC_DATA_FORMAT_HTML = 'html';
	public const DYNAMIC_DATA_FORMAT_MARKDOWN = 'markdown';

	public static function process(array $notification_handlers, array $allowed_handler_ids, array $notification_data, array $context = []) {
		foreach ($notification_handlers as $notification_handler) {
			if($notification_handler->is_enabled != 1) continue;
			if(!in_array($notification_handler->notification_handler_id, $allowed_handler_ids)) continue;

			$context['message'] = self::build_message($notification_data, $context, $notification_handler->type);

			switch($notification_handler->type) {
				case 'email':
					if(!($notification_handler->settings->email_is_confirmed ?? true)) continue 2;

					self::handle_email($notification_handler, $context);
					break;

				case 'webhook':
					self::handle_webhook($notification_handler, $notification_data);
					break;

				case 'slack':
					self::handle_slack($notification_handler, $context);
					break;

				case 'discord':
					self::handle_discord($notification_handler, $context);
					break;

				case 'google_chat':
					self::handle_google_chat($notification_handler, $context);
					break;

				case 'ntfy':
					self::handle_ntfy($notification_handler, $notification_data, $context);
					break;

				case 'telegram':
					self::handle_telegram($notification_handler, $context);
					break;

				case 'microsoft_teams':
					self::handle_microsoft_teams($notification_handler, $context);
					break;

				case 'x':
					self::handle_x($notification_handler, $context);
					break;

				case 'twilio':
					self::handle_twilio($notification_handler, $context);
					break;

				case 'twilio_call':
					self::handle_twilio_call($notification_handler, $context);
					break;

				case 'whatsapp':
					self::handle_whatsapp($notification_handler, $context);
					break;

				case 'push_subscriber_id':
					self::handle_web_push($notification_handler, $notification_data, $context);
					break;

				case 'internal_notification':
					self::handle_internal_notification($notification_handler, $notification_data, $context);
					break;

				case 'sixsixtext_save_contact':
					self::handle_sixsixtext_save_contact($notification_handler, $notification_data, $context);
					break;

				case 'sixsixtext_send_sms':
					self::handle_sixsixtext_send_sms($notification_handler, $context);
					break;
			}
		}
	}

	public static function build_dynamic_message_data($notification_data, $dynamic_data_type, $format, $language, $timezone = null, $emoji = true) {		$line_break = "\r\n";
		$output = $line_break . $line_break;

		$settings = self::get_dynamic_data_settings();
		$fields = $settings[$dynamic_data_type] ?? [];

		$items = [];

		foreach($notification_data as $key => $value) {
			$field = $fields[$key] ?? [];

			if(isset($field['is_visible']) && !$field['is_visible']) {
				continue;
			}

			$items[] = [
				'key' => $key,
				'value' => $value,
				'field' => $field,
				'order' => $field['order'] ?? self::DYNAMIC_DATA_DEFAULT_ORDER,
			];
		}

		usort($items, fn($a, $b) => $a['order'] <=> $b['order']);

		foreach($items as $item) {
			$key = $item['key'];
			$value = $item['value'];
			$field = $item['field'];

			$label = $field['label'] ?? $key;
			$label = is_callable($label) ? $label($language, $notification_data) : $label;

			if(isset($field['formatter']) && is_callable($field['formatter'])) {
				$value = $field['formatter']($value, $timezone, $notification_data, $language, $format);
			}

			$emoji = !empty($field['emoji']) && $emoji ? $field['emoji'] . ' ' : '';

			switch($format) {
				case self::DYNAMIC_DATA_FORMAT_HTML:
					$label = '<strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</strong>';
					$value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
					break;

				case self::DYNAMIC_DATA_FORMAT_MARKDOWN:
					$label = '**' . $label . '**';
					break;
			}

			$output .= $emoji . $label . ': ' . $value . $line_break;
		}

		return $output . $line_break;
	}

	private static function build_message(array $notification_data, array $context, string $handler_type) {
		if(empty($context['message_template'])) {
			return $context['message'] ?? null;
		}

		$language = $context['language'] ?? ($context['user']->language ?? null);
		$timezone = $context['timezone'] ?? ($context['user']->timezone ?? null);

		$format = self::get_message_format($handler_type);
		$dynamic_data_type = $context['dynamic_data_type'] ?? null;

		$dynamic_message_data = $dynamic_data_type ? self::build_dynamic_message_data(
			$notification_data,
			$dynamic_data_type,
			$format,
			$language,
			$timezone,
		) : '';

		$message_template_arguments = $context['message_template_arguments'] ?? [];

		foreach($message_template_arguments as $key => $value) {
			if($value == '{{DYNAMIC_DATA}}') {
				$message_template_arguments[$key] = $dynamic_message_data;
			}
		}

		return vsprintf($context['message_template'], $message_template_arguments);
	}

	private static function handle_email(object $notification_handler, array $context) {
		if(empty($context['email_template']) || empty($context['user'])) return;

		send_mail(
			$notification_handler->settings->email,
			$context['email_template']->subject,
			$context['email_template']->body,
			[
				'anti_phishing_code' => $context['user']->anti_phishing_code,
				'language' => $context['user']->language
			]
		);
	}

	public static function handle_webhook(object $notification_handler, array $notification_data) {
		fire_and_forget('post', $notification_handler->settings->webhook, $notification_data);
	}

	private static function handle_slack(object $notification_handler, array $context) {
		if(empty($context['message'])) return;

		try {
			\Unirest\Request::post(
				$notification_handler->settings->slack,
				['Accept' => 'application/json'],
				\Unirest\Request\Body::json([
					'text' => $context['message'],
					'username' => settings()->main->title,
					'icon_emoji' => $context['slack_emoji'] ?? ':large_green_circle:'

				])
			);
		} catch (\Exception $exception) {
			error_log('[Notification Handler][Slack] ' . $exception->getMessage());
		}
	}

	private static function handle_discord(object $notification_handler, array $context) {
		if(empty($context['message'])) return;

		try {
			fire_and_forget(
				'POST',
				$notification_handler->settings->discord,
				[
					'embeds' => [[
						'description' => $context['message'],
						'color' => $context['discord_color'] ?? '2664261'
					]]
				],
				'json',
				[
					'Accept' => 'application/json',
					'Content-Type' => 'application/json'
				]
			);
		} catch (\Exception $exception) {
			error_log('[Notification Handler][Discord] ' . $exception->getMessage());
		}
	}

	private static function handle_google_chat(object $notification_handler, array $context) {
		if(empty($context['message'])) return;

		try {
			fire_and_forget(
				'POST',
				$notification_handler->settings->google_chat,
				['text' => $context['message']],
				'json',
				[
					'Accept' => 'application/json',
					'Content-Type' => 'application/json'
				]
			);
		} catch (\Exception $exception) {
			error_log('[Notification Handler][Google Chat] ' . $exception->getMessage());
		}
	}

	private static function handle_ntfy(object $notification_handler, array $notification_data, array $context) {
		if(empty($context['message'])) return;

		/* Prepare ntfy headers */
		$headers = [
			'Content-Type' => 'text/markdown',
			'Markdown' => 'yes',
		];

		if(!empty($context['push_title'])) {
			$headers['Title'] = $context['push_title'];
		}

		if(!empty($notification_data['url'])) {
			$headers['Click'] = $notification_data['url'];
		}

		try {
			$response = \Unirest\Request::post(
				$notification_handler->settings->ntfy,
				$headers,
				$context['message']
			);

			if($response->code < 200 || $response->code > 299) {
				error_log('[Notification Handler][ntfy] ' . $response->code . ': ' . $response->raw_body);
			}
		} catch (\Exception $exception) {
			error_log('[Notification Handler][ntfy] ' . $exception->getMessage());
		}
	}

	private static function handle_telegram(object $notification_handler, array $context) {
		if(empty($context['message'])) return;

		$encoded_message = urlencode($context['message']);

		try {
			fire_and_forget(
				'GET',
				sprintf(
					'https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s&parse_mode=html&link_preview_options=%s',
					$notification_handler->settings->telegram,
					$notification_handler->settings->telegram_chat_id,
					$encoded_message,
					json_encode(['is_disabled' => true])
				),
				[],
				'form',
				[],
				false
			);
		} catch (\Exception $exception) {
			error_log('[Notification Handler][Telegram] ' . $exception->getMessage());
		}
	}

	private static function handle_microsoft_teams(object $notification_handler, array $context) {
		if(empty($context['message'])) return;

		try {
			\Unirest\Request::post(
				$notification_handler->settings->microsoft_teams,
				['Content-Type' => 'application/json'],
				\Unirest\Request\Body::json(['text' => $context['message']])
			);
		} catch (\Exception $exception) {
			error_log('[Notification Handler][Microsoft Teams] ' . $exception->getMessage());
		}
	}

	private static function handle_x(object $notification_handler, array $context) {
		if(empty($context['message'])) return;

		$twitter = new \Abraham\TwitterOAuth\TwitterOAuth(
			$notification_handler->settings->x_consumer_key,
			$notification_handler->settings->x_consumer_secret,
			$notification_handler->settings->x_access_token,
			$notification_handler->settings->x_access_token_secret
		);

		$twitter->setApiVersion('2');

		try {
			$twitter->post('tweets', ['text' => $context['message']]);
		} catch (\Exception $exception) {
			error_log('[Notification Handler][X] ' . $exception->getMessage());
		}
	}

	private static function handle_twilio(object $notification_handler, array $context) {
		if(empty($context['message'])) return;

		try {
			\Unirest\Request::auth(settings()->notification_handlers->twilio_sid, settings()->notification_handlers->twilio_token);

			\Unirest\Request::post(
				sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', settings()->notification_handlers->twilio_sid),
				[],
				[
					'From' => settings()->notification_handlers->twilio_number,
					'To' => $notification_handler->settings->twilio,
					'Body' => $context['message']
				]
			);
		} catch (\Exception $exception) {
			error_log('[Notification Handler][Twilio SMS] ' . $exception->getMessage());
		}

		\Unirest\Request::auth('', '');
	}

	private static function handle_twilio_call(object $notification_handler, array $context) {
		if(empty($context['twilio_call_url'])) return;

		try {
			\Unirest\Request::auth(settings()->notification_handlers->twilio_sid, settings()->notification_handlers->twilio_token);

			\Unirest\Request::post(
				sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Calls.json', settings()->notification_handlers->twilio_sid),
				[],
				[
					'From' => settings()->notification_handlers->twilio_number,
					'To' => $notification_handler->settings->twilio_call,
					'Url' => $context['twilio_call_url']
				]
			);
		} catch (\Exception $exception) {
			error_log('[Notification Handler][Twilio Call] ' . $exception->getMessage());
		}

		\Unirest\Request::auth('', '');
	}

	private static function handle_whatsapp(object $notification_handler, array $context) {
		if(empty($context['whatsapp_template']) || empty($context['whatsapp_parameters'])) return;

		try {
			\Unirest\Request::post(
				'https://graph.facebook.com/v23.0/' . settings()->notification_handlers->whatsapp_number_id . '/messages',
				[
					'Authorization' => 'Bearer ' . settings()->notification_handlers->whatsapp_access_token,
					'Content-Type' => 'application/json'
				],
				\Unirest\Request\Body::json([
					'messaging_product' => 'whatsapp',
					'to' => $notification_handler->settings->whatsapp,
					'type' => 'template',
					'template' => [
						'name' => $context['whatsapp_template'],
						'language' => ['code' => \Altum\Language::$default_code],
						'components' => [[
							'type' => 'body',
							'parameters' => array_map(fn($text) => ['type' => 'text', 'text' => $text], $context['whatsapp_parameters'])
						]]
					]
				])
			);
		} catch (\Exception $exception) {
			error_log('[Notification Handler][WhatsApp] ' . $exception->getMessage());
		}
	}

	private static function handle_web_push(object $notification_handler, array $notification_data, array $context) {
		if(empty($context['push_title']) || empty($context['push_description'])) return;

		$push_subscriber = db()->where('push_subscriber_id', $notification_handler->settings->push_subscriber_id)->getOne('push_subscribers');

		if(!$push_subscriber) {
			db()->where('notification_handler_id', $notification_handler->notification_handler_id)->update('notification_handlers', ['is_enabled' => 0]);
			return;
		}

		$sent = \Altum\Helpers\PushNotifications::send([
			'title' => $context['push_title'],
			'description' => $context['push_description'],
			'url' => $notification_data['url']
		], $push_subscriber);

		if(!$sent) {
			db()->where('push_subscriber_id', $push_subscriber->push_subscriber_id)->delete('push_subscribers');
			db()->where('notification_handler_id', $notification_handler->notification_handler_id)->update('notification_handlers', ['is_enabled' => 0]);
		}
	}

	private static function handle_internal_notification(object $notification_handler, array $notification_data, array $context) {
		if(!settings()->internal_notifications->users_is_enabled) return;
		if(empty($context['user']) || empty($context['push_title']) || empty($context['push_description'])) return;

		db()->insert('internal_notifications', [
			'user_id' => $context['user']->user_id,
			'for_who' => 'user',
			'from_who' => 'system',
			'title' => $context['push_title'],
			'description' => $context['push_description'],
			'url' => $notification_data['url'],
			'icon' => $context['internal_icon'] ?? 'fas fa-bell',
			'datetime' => get_date()
		]);

		db()->where('user_id', $context['user']->user_id)->update('users', [
			'has_pending_internal_notifications' => 1
		]);

		cache()->deleteItem('user?user_id=' . $context['user']->user_id);
	}

	private static function handle_sixsixtext_save_contact(object $notification_handler, array $notification_data, array $context) {
		if(empty($notification_handler->settings->sixsixtext_save_contact_api_key)) return;
		if(empty($notification_handler->settings->sixsixtext_save_contact_phone_number)) return;

		try {
			$response = \Unirest\Request::post(
				settings()->notification_handlers->sixsixtext_url . 'api/contacts',
				[
					'Authorization' => 'Bearer ' . $notification_handler->settings->sixsixtext_save_contact_api_key,
				],
				[
					'phone_number' => $context['phone_number'],
					'name' => $context['contact_name'] ?? '',
					'custom_parameter_key[0]' => 'source',
					'custom_parameter_value[0]' => remove_url_protocol_from_url(SITE_URL),
				]
			);

			if($response->code != 201) {
				error_log('[Notification Handler][66Text Save Contact] ' . $response->code . ': ' . $response->raw_body);
			}
		} catch (\Exception $exception) {
			error_log('[Notification Handler][66Text Save Contact] ' . $exception->getMessage());
		}
	}

	private static function handle_sixsixtext_send_sms(object $notification_handler, array $context) {
		if(empty($context['message'])) return;
		if(empty($notification_handler->settings->sixsixtext_send_sms_api_key)) return;
		if(empty($notification_handler->settings->sixsixtext_send_sms_device_id)) return;
		if(empty($notification_handler->settings->sixsixtext_send_sms_device_sim_subscription_id)) return;

		try {
			$response = \Unirest\Request::post(
				settings()->notification_handlers->sixsixtext_url . 'api/sms',
				[
					'Authorization' => 'Bearer ' . $notification_handler->settings->sixsixtext_send_sms_api_key,
				],
				[
					'device_id' => $notification_handler->settings->sixsixtext_send_sms_device_id,
					'sim_subscription_id' => $notification_handler->settings->sixsixtext_send_sms_device_sim_subscription_id,
					'phone_numbers' => $notification_handler->settings->sixsixtext_send_sms_phone_number,
					'content' => $context['message'],
				]
			);

			if($response->code != 201) {
				error_log('[Notification Handler][66Text Send SMS] ' . $response->code . ': ' . $response->raw_body);
			}
		} catch (\Exception $exception) {
			error_log('[Notification Handler][66Text Send SMS] ' . $exception->getMessage());
		}
	}

	private static function get_message_format($handler_type) {
		if(in_array($handler_type, self::HTML_SUPPORTED_HANDLERS)) {
			return self::DYNAMIC_DATA_FORMAT_HTML;
		}

		if(in_array($handler_type, self::MARKDOWN_SUPPORTED_HANDLERS)) {
			return self::DYNAMIC_DATA_FORMAT_MARKDOWN;
		}

		return self::DYNAMIC_DATA_FORMAT_PLAIN;
	}

	private static function get_dynamic_data_settings() {
		static $settings = null;

		if($settings) {
			return $settings;
		}

		$settings = require APP_PATH . 'includes/notification_handlers_dynamic_data.php';

		return $settings;
	}
}
