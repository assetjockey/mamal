<?php

/*
|--------------------------------------------------------------------------
| Channel Broadcast — channel capabilities, AI options & billing
|--------------------------------------------------------------------------
|
| Single source of truth for what each broadcast channel supports. The compose
| UI, the connect forms, the per-channel content rules and the delivery worker
| all read from `channels`.
|
| Credentials are owned by the USER: each connected destination stores its own
| API credentials (encrypted). The admin only toggles whether a channel is
| available at all. Users may connect as many destinations per channel as they
| like (multiple WhatsApp numbers, Telegram chats, Slack channels, …).
|
| Broadcasting is ALWAYS free. The only billable action is generating /
| optimizing a message with AI (spends the user's shared credits).
|
| Plugin slug: magicads-channel-broadcast
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Broadcast channels
    |----------------------------------------------------------------------
    |
    | Each entry declares:
    |   label / short     — display strings
    |   tagline           — one-line blurb used on connect cards/modals
    |   icon              — Font Awesome class
    |   flux_icon         — Heroicon name
    |   color             — brand hex (icon accents only)
    |   accent            — tint key
    |   supports_media    — can attach an image/video
    |   supports_button   — can render a CTA link button
    |   format            — text|markdown|mrkdwn|html (formatting dialect)
    |   max_text          — soft body limit (chars), 0 = unlimited
    |   connect_fields    — the form a user fills to connect ONE destination.
    |                       each: key,label,type(text|password|textarea),
    |                       placeholder,help,required,multi(=list of values),
    |                       optional(=excluded from "required" enforcement)
    |
    */
    'channels' => [

        'whatsapp' => [
            'label'           => 'WhatsApp',
            'short'           => 'WhatsApp',
            'tagline'         => 'Message individual opted-in numbers via your own WhatsApp Business (Cloud API). No group chats.',
            'icon'            => 'fa-brands fa-whatsapp',
            'flux_icon'       => 'chat-bubble-left-right',
            'color'           => '#25D366',
            'accent'          => 'emerald',
            'supports_media'  => true,
            'supports_button' => false,
            'format'          => 'text',
            'max_text'        => 4096,
            'connect_fields'  => [
                ['key' => 'phone_number_id', 'label' => 'Phone Number ID', 'type' => 'text', 'placeholder' => 'Enter WhatsApp Phone Number ID…', 'help' => 'From your WhatsApp Business API dashboard.', 'required' => true],
                ['key' => 'access_token', 'label' => 'Access Token', 'type' => 'password', 'placeholder' => 'Enter WhatsApp Business API Access Token…', 'help' => 'Your permanent access token from the Meta Developer Console.', 'required' => true],
                ['key' => 'recipients', 'label' => 'Recipient phone numbers', 'type' => 'textarea', 'placeholder' => "14155550101\n14155550102", 'help' => 'One number per line, digits only with country code (e.g. 14155550101 — no +, spaces or dashes). Each number is messaged individually — WhatsApp business messaging is one-to-one, so you target people, not group chats. Important: WhatsApp only delivers to numbers that have opted in and messaged your WhatsApp Business number within the last 24 hours; outside that window you must use an approved message template.', 'required' => true, 'multi' => true],
            ],
        ],

        'telegram' => [
            'label'           => 'Telegram',
            'short'           => 'Telegram',
            'tagline'         => 'Post to a Telegram group or channel using your own bot.',
            'icon'            => 'fa-brands fa-telegram',
            'flux_icon'       => 'paper-airplane',
            'color'           => '#229ED9',
            'accent'          => 'sky',
            'supports_media'  => true,
            'supports_button' => true,
            'format'          => 'html',
            'max_text'        => 4096,
            'connect_fields'  => [
                ['key' => 'bot_token', 'label' => 'Bot Token', 'type' => 'password', 'placeholder' => 'Enter Telegram bot token…', 'help' => 'Create a bot with @BotFather and paste its token.', 'required' => true],
                ['key' => 'chat_id', 'label' => 'Chat / Group ID', 'type' => 'text', 'placeholder' => '-1001234567890', 'help' => 'Add your bot to the group as an admin, then paste the group chat ID.', 'required' => true],
            ],
        ],

        'slack' => [
            'label'           => 'Slack',
            'short'           => 'Slack',
            'tagline'         => 'Push announcements to a Slack channel via your Incoming Webhook.',
            'icon'            => 'fa-brands fa-slack',
            'flux_icon'       => 'hashtag',
            'color'           => '#611f69',
            'accent'          => 'indigo',
            'supports_media'  => true,
            'supports_button' => true,
            'format'          => 'mrkdwn',
            'max_text'        => 3000,
            'connect_fields'  => [
                ['key' => 'webhook_url', 'label' => 'Incoming Webhook URL', 'type' => 'text', 'placeholder' => 'https://hooks.slack.com/services/T000/B000/XXXX', 'help' => 'Create an Incoming Webhook in your Slack app and paste the URL.', 'required' => true],
            ],
        ],

        'messenger' => [
            'label'           => 'Facebook Messenger',
            'short'           => 'Messenger',
            'tagline'         => 'Reach Messenger subscribers using your own Facebook Page token.',
            'icon'            => 'fa-brands fa-facebook-messenger',
            'flux_icon'       => 'chat-bubble-oval-left',
            'color'           => '#0084FF',
            'accent'          => 'sky',
            'supports_media'  => true,
            'supports_button' => true,
            'format'          => 'text',
            'max_text'        => 2000,
            'connect_fields'  => [
                ['key' => 'page_token', 'label' => 'Page Access Token', 'type' => 'password', 'placeholder' => 'Enter your Facebook Page Access Token…', 'help' => 'Generate a Page Access Token in your Facebook App.', 'required' => true],
                ['key' => 'recipients', 'label' => 'Recipient PSIDs', 'type' => 'textarea', 'placeholder' => "24607515246589216\n61557892014369830", 'help' => 'One Page-Scoped ID (PSID) per line — people who have messaged your Page.', 'required' => true, 'multi' => true],
            ],
        ],

        'email' => [
            'label'           => 'Email',
            'short'           => 'Email',
            'tagline'         => 'Email your broadcast to a subscriber list from the platform mailer.',
            'icon'            => 'fa-solid fa-envelope',
            'flux_icon'       => 'envelope',
            'color'           => '#4F46E5',
            'accent'          => 'indigo',
            'supports_media'  => true,
            'supports_button' => true,
            'format'          => 'html',
            'max_text'        => 0,
            'connect_fields'  => [
                ['key' => 'recipients', 'label' => 'Recipient email addresses', 'type' => 'textarea', 'placeholder' => "jane@example.com\njohn@example.com", 'help' => 'One email per line. Recipients are BCC’d so they never see each other.', 'required' => true, 'multi' => true],
                ['key' => 'from_name', 'label' => 'From name', 'type' => 'text', 'placeholder' => 'Acme Marketing', 'help' => 'Optional — overrides the default sender name.', 'required' => false, 'optional' => true],
                ['key' => 'from_email', 'label' => 'From / Reply-to email', 'type' => 'text', 'placeholder' => 'hello@acme.com', 'help' => 'Optional — overrides the default sender address.', 'required' => false, 'optional' => true],
            ],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | AI message generation — billing
    |----------------------------------------------------------------------
    */
    'ai' => [
        'word_unit'    => 1000,
        'default_rate' => 2,
        'max_words'    => 400,
        'model'        => 'gpt-4o-mini',
    ],

    /*
    |----------------------------------------------------------------------
    | AI message — tone options
    |----------------------------------------------------------------------
    */
    'tones' => [
        'professional' => 'Professional',
        'friendly'     => 'Friendly',
        'promotional'  => 'Promotional',
        'urgent'       => 'Urgent / FOMO',
        'exciting'     => 'Exciting',
        'inspiring'    => 'Inspiring',
        'casual'       => 'Casual',
        'persuasive'   => 'Persuasive',
        'concise'      => 'Short & punchy',
        'informative'  => 'Informative',
    ],

    /*
    |----------------------------------------------------------------------
    | Recurring broadcast cadence options
    |----------------------------------------------------------------------
    */
    'recurrence_days' => [
        'all'       => 'Every day',
        'weekdays'  => 'Weekdays only',
        'weekends'  => 'Weekends only',
        'even'      => 'Even days',
        'odd'       => 'Odd days',
        'monday'    => 'Mondays',
        'tuesday'   => 'Tuesdays',
        'wednesday' => 'Wednesdays',
        'thursday'  => 'Thursdays',
        'friday'    => 'Fridays',
        'saturday'  => 'Saturdays',
        'sunday'    => 'Sundays',
    ],

    /*
    |----------------------------------------------------------------------
    | Delivery worker tuning
    |----------------------------------------------------------------------
    */
    'worker' => [
        'stuck_after_minutes' => 15,
        'max_attempts'        => 3,
        'batch'               => 25,
    ],
];
