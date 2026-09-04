<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
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

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Response;
use Altum\Title;

defined('ALTUMCODE') || die();

class Chat extends Controller {

    public function index() {
        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('aix') || !settings()->aix->chats_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.chats')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('dashboard');
        }

        /* Check for exclusive personal API usage limitation */
        if($this->user->plan_settings->exclusive_personal_api_keys && empty($this->user->preferences->openai_api_key)) {
            Alerts::add_error(sprintf(l('account_preferences.error_message.aix.openai_api_key'), '<a href="' . url('account-preferences') . '"><strong>' . l('account_preferences.menu') . '</strong></a>'));
        }

        $chat_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Get chat details */
        if(!$chat = db()->where('chat_id', $chat_id)->where('user_id', $this->user->user_id)->getOne('chats')) {
            throw_404();
        }

        $chat->settings = json_decode($chat->settings ? $chat->settings : '');
        if(!$chat->settings) {
            $chat->settings = new \stdClass();
        }

        /* Get all the existing chat messages */
        $chat_messages = db()->where('chat_id', $chat->chat_id)->get('chats_messages');

        /* Check for the plan limit */
        $sent_chat_messages = 0;
        foreach($chat_messages as $chat_message) {
            if($chat_message->role == 'user') $sent_chat_messages++;
        }

        /* Chats assistants */
        $chats_assistants = (new \Altum\Models\ChatsAssistants())->get_chats_assistants();
        if(isset($chats_assistants[$chat->chat_assistant_id])) {
            $chat_assistant = $chats_assistants[$chat->chat_assistant_id];
        } else {
            $chat_assistant = db()->where('chat_assistant_id', $chat->chat_assistant_id)->getOne('chats_assistants');

            if(!$chat_assistant) {
                Alerts::add_error(l('chats.error_message.assistant_missing'));
                redirect('chats');
            }

            $chat_assistant->settings = json_decode($chat_assistant->settings);
        }

        /* Safer markdown output */
        $parsedown = new \Parsedown();
        $parsedown->setSafeMode(true);

        /* Set a custom title */
        Title::set(sprintf(l('chat.title'), $chat->name));

        /* Main View */
        $data = [
            'chat' => $chat,
            'chat_assistant' => $chat_assistant,
            'chat_messages' => $chat_messages,
            'sent_chat_messages' => $sent_chat_messages,
            'content' => input_clean(isset($_GET['content']) ? $_GET['content'] : ''),
            'parsedown' => $parsedown,
        ];

        $view = new \Altum\View(\Altum\Plugin::get('aix')->path . 'views/chat/index', (array) $this, true);

        $this->add_view_content('content', $view->run($data));
    }

    public function create_ajax() {
        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Response::json('Please create an account on the demo to test out this function.', 'error');

        if(empty($_POST)) {
            throw_404();
        }

        set_time_limit(0);

        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('aix') || !settings()->aix->chats_is_enabled) {
            throw_404();
        }

        $_POST['chat_id'] = (int) $_POST['chat_id'];

        /* Get chat details */
        if(!$chat = db()->where('chat_id', $_POST['chat_id'])->where('user_id', $this->user->user_id)->getOne('chats')) {
            throw_404();
        }

        $chat->settings = json_decode($chat->settings);
        if(!$chat->settings) {
            $chat->settings = new \stdClass();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.chats')) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        /* Chats assistants */
        $chats_assistants = (new \Altum\Models\ChatsAssistants())->get_chats_assistants();
        if(!isset($chats_assistants[$chat->chat_assistant_id])) {
            Response::json(l('chats.error_message.assistant_missing'), 'error');
        }

        $chat_assistant = $chats_assistants[$chat->chat_assistant_id];

        /* Selected AI model */
        if(!isset($this->user->plan_settings->chats_model)) {
            $this->user->plan_settings->chats_model = 'gpt-5-mini';
        }

        /* */
        $_POST['content'] = trim(mb_substr($_POST['content'], 0, 20000));

        /* Image input */
        $image = null;

        /* Check for any errors */
        $required_fields = ['content'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                Response::json(l('global.error_message.empty_fields'), 'error');
            }
        }

        if(!\Altum\Csrf::check('global_token')) {
            Response::json(l('global.error_message.invalid_csrf_token'), 'error');
        }

        /* API key check */
        $openai_api_key = get_random_line_from_text($this->user->plan_settings->exclusive_personal_api_keys ? $this->user->preferences->openai_api_key : settings()->aix->openai_api_key);
        if(!$openai_api_key) {
            Response::json(l('chats.error_message.openai_api_key'), 'error');
        }

        /* Check for timeouts */
        if(settings()->aix->input_moderation_is_enabled) {
            $cache_instance = cache()->getItem('user?flagged=' . $this->user->user_id);
            if(!is_null($cache_instance->get())) {
                Response::json(l('chats.error_message.timed_out'), 'error');
            }
        }

        /* Get all the existing chat messages */
        $chat_messages = db()->where('chat_id', $chat->chat_id)->get('chats_messages');

        /* Check for the plan limit */
        $sent_chat_messages = 0;
        foreach($chat_messages as $chat_message) {
            if($chat_message->role == 'user') $sent_chat_messages++;
        }

        if($this->user->plan_settings->chat_messages_per_chat_limit != -1 && $sent_chat_messages >= $this->user->plan_settings->chat_messages_per_chat_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Check for moderation */
        if(settings()->aix->input_moderation_is_enabled) {
            try {
                $response = \Unirest\Request::post(
                    'https://api.openai.com/v1/moderations',
                    [
                        'Authorization' => 'Bearer '  . $openai_api_key,
                        'Content-Type' => 'application/json',
                    ],
                    \Unirest\Request\Body::json([
                        'input' => $_POST['content'],
                        'model' => 'omni-moderation-latest',
                    ])
                );

                if($response->code >= 400) {
                    Response::json($response->body->error->message, 'error');
                }

                if(isset($response->body->results[0]->flagged) && $response->body->results[0]->flagged) {
                    /* Time out the user for a few minutes */
                    cache()->save(
                        $cache_instance->set('true')->expiresAfter(3 * 60)->addTag('user_id=' . $this->user->user_id)
                    );

                    /* Return the error */
                    Response::json(l('chats.error_message.flagged'), 'error');
                }

            } catch (\Exception $exception) {
                Response::json($exception->getMessage(), 'error');
            }
        }

        /* Process image input */
        $image = \Altum\Uploads::process_upload(null, 'chats_images', 'image', 'image_remove', $this->user->plan_settings->chat_image_size_limit, 'json_error');

        /* Prepare the main API request */
        $api_endpoint_url = 'https://api.openai.com/v1/responses';

        /* Prepare sent content */
        $input_content = [
            [
                'type' => 'input_text',
                'text' => $_POST['content'],
            ]
        ];

        if($image) {
            if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url) {
                /* Send remote image URL */
                $input_content[] = [
                    'type' => 'input_image',
                    'image_url' => \Altum\Uploads::get_full_url('chats_images') . $image,
                ];
            } else {
                if(!function_exists('mime_content_type')) {
                    /* Delete uploaded image */
                    \Altum\Uploads::delete_uploaded_file($image, 'chats_images');

                    Response::json(sprintf(l('global.error_message.function_required'), 'mime_content_type()'), 'error');
                }

                $image_file_path = UPLOADS_PATH . \Altum\Uploads::get_path('chats_images') . $image;
                if(!file_exists($image_file_path)) {
                    /* Delete uploaded image */
                    \Altum\Uploads::delete_uploaded_file($image, 'chats_images');

                    Response::json(l('global.error_message.basic'), 'error');
                }

                $image_mime_type = mime_content_type($image_file_path);
                if(!$image_mime_type) {
                    /* Delete uploaded image */
                    \Altum\Uploads::delete_uploaded_file($image, 'chats_images');

                    Response::json(l('global.error_message.basic'), 'error');
                }

                $image_contents = file_get_contents($image_file_path);
                if($image_contents === false) {
                    /* Delete uploaded image */
                    \Altum\Uploads::delete_uploaded_file($image, 'chats_images');

                    Response::json(l('global.error_message.basic'), 'error');
                }

                /* Send local image data */
                $input_content[] = [
                    'type' => 'input_image',
                    'image_url' => 'data:' . $image_mime_type . ';base64,' . base64_encode($image_contents),
                ];
            }
        }

        /* Build the input array */
        $input = [];
        if(!isset($chat->settings->latest_response_id) || !$chat->settings->latest_response_id) {
            foreach($chat_messages as $chat_message) {
                $input[] = [
                    'role' => $chat_message->role == 'assistant' ? 'assistant' : 'user',
                    'content' => $chat_message->content,
                ];
            }
        }

        $input[] = [
            'role' => 'user',
            'content' => $input_content,
        ];

        $body = [
            'model' => $this->user->plan_settings->chats_model,
            'input' => $input,
            'store' => true,
            'user' => 'user_id:' . $this->user->user_id,
        ];

        if($chat_assistant->prompt) {
            /* Chat assistant instructions */
            $body['instructions'] = $chat_assistant->prompt;
        }

        if(isset($chat->settings->latest_response_id) && $chat->settings->latest_response_id) {
            $body['previous_response_id'] = $chat->settings->latest_response_id;
        }

        /* Try to increase the database timeout as well */
        database()->query("set session wait_timeout=600;");

        /* Do not use sessions anymore to not lockout the user from doing anything else on the site */
        session_write_close();

        try {
            $response = \Unirest\Request::post(
                $api_endpoint_url,
                [
                    'Authorization' => 'Bearer '  . $openai_api_key,
                    'Content-Type' => 'application/json',
                ],
                \Unirest\Request\Body::json($body)
            );

            if($response->code >= 400) {
                /* Delete uploaded image */
                if($image) {
                    \Altum\Uploads::delete_uploaded_file($image, 'chats_images');
                }

                $error_message = isset($response->body->error->message) ? $response->body->error->message : l('global.error_message.basic');
                Response::json($error_message, 'error');
            }

        } catch (\Exception $exception) {
            /* Delete uploaded image */
            if($image) {
                \Altum\Uploads::delete_uploaded_file($image, 'chats_images');
            }

            Response::json($exception->getMessage(), 'error');
        }

        /* Get info after the request */
        $info = \Unirest\Request::getInfo();

        /* Some needed variables */
        $api_response_time = $info['total_time'] * 1000;

        if(!isset($response->body->id) || !isset($response->body->model)) {
            /* Delete uploaded image */
            if($image) {
                \Altum\Uploads::delete_uploaded_file($image, 'chats_images');
            }

            Response::json(l('chats.error_message.invalid_response'), 'error');
        }

        /* Parse the response text */
        $content = '';
        if(isset($response->body->output_text)) {
            $content = trim($response->body->output_text);
        }

        if($content === '' && isset($response->body->output) && is_array($response->body->output)) {
            foreach($response->body->output as $output) {
                if(isset($output->content) && is_array($output->content)) {
                    foreach($output->content as $content_item) {
                        if(isset($content_item->text)) {
                            $content .= $content_item->text;
                        }
                    }
                }
            }

            $content = trim($content);
        }

        if($content === '') {
            /* Delete uploaded image */
            if($image) {
                \Altum\Uploads::delete_uploaded_file($image, 'chats_images');
            }

            Response::json(l('chats.error_message.invalid_response'), 'error');
        }

        $role = 'assistant';

        /* Database query */
        db()->insert('chats_messages', [
            'user_id' => $this->user->user_id,
            'chat_id' => $chat->chat_id,
            'role' => 'user',
            'content' => $_POST['content'],
            'image' => $image,
            'model' => $response->body->model,
            'api_response_time' => 0,
            'datetime' => get_date(),
        ]);

        /* Database query */
        db()->insert('chats_messages', [
            'user_id' => $this->user->user_id,
            'chat_id' => $chat->chat_id,
            'role' => $role,
            'content' => $content,
            'model' => $response->body->model,
            'api_response_time' => $api_response_time,
            'datetime' => get_date(),
        ]);

        /* Settings */
        $settings = json_encode([
            'api' => 'responses',
            'latest_response_id' => $response->body->id,
        ]);

        /* Database query */
        $chat_update = [
            'total_messages' => db()->inc(2),
            'settings' => $settings,
            'last_datetime' => get_date(),
        ];

        if(isset($response->body->usage->total_tokens)) {
            $chat_update['used_tokens'] = db()->inc($response->body->usage->total_tokens);
        }

        db()->where('chat_id', $chat->chat_id)->update('chats', $chat_update);

        /* Parse the generated markup */
        $parsedown = new \Parsedown();
        $parsedown->setSafeMode(true);
        $content = $parsedown->text($content);

        /* Set a nice success message */
        Response::json(
            l('global.success_message.create2'),
            'success',
            [
                'role' => $role,
                'content' => $content,
                'image_url' => $image ? \Altum\Uploads::get_full_url('chats_images') . $image : null,
                'datetime_his' => \Altum\Date::get(get_date(), 3),
                'datetime_full' => \Altum\Date::get(get_date(), 1)
            ]
        );

    }

}
