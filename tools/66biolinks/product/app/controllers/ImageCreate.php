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
use Altum\Uploads;

defined('ALTUMCODE') || die();

class ImageCreate extends Controller {

    public function index() {
        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('aix') || !settings()->aix->images_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.images')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('images');
        }

        /* Check for the plan limit */
        $images_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`aix_images_current_month`');
        if($this->user->plan_settings->images_per_month_limit != -1 && $images_current_month >= $this->user->plan_settings->images_per_month_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('images');
        }

        /* Check for exclusive personal API usage limitation */
        $api_key = 'openai_api_key';
        if($this->user->plan_settings->exclusive_personal_api_keys && empty($this->user->preferences->{$api_key})) {
            Alerts::add_error(sprintf(l('account_preferences.error_message.aix.' . $api_key), '<a href="' . url('account-preferences') . '"><strong>' . l('account_preferences.menu') . '</strong></a>'));
        }

        /* Ai image models */
        $ai_image_models = require \Altum\Plugin::get('aix')->path . 'includes/ai_image_models.php';

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Selected AI model */
        if(!isset($this->user->plan_settings->images_api)) {
            $this->user->plan_settings->images_api = 'gpt-image-1';
        }

        /* Free text model metadata */
        if(isset($ai_image_models[$this->user->plan_settings->images_api])) {
            $ai_model = $ai_image_models[$this->user->plan_settings->images_api];
        } else {
            $ai_model = [
                'api' => 'openai',
                'name' => $this->user->plan_settings->images_api,
                'max_length' => 30000,
                'sizes' => ['1024x1024'],
                'variants' => [1],
                'qualities' => ['auto', 'low', 'medium', 'high'],
                'output_formats' => ['png', 'webp', 'jpeg'],
                'backgrounds' => ['auto', 'transparent', 'opaque'],
            ];
        }

        /* Clear $_GET */
        foreach($_GET as $key => $value) {
            $_GET[$key] = input_clean($value);
        }

        $values = [
            'name' => isset($_POST['name']) ? $_POST['name'] : (isset($_GET['name']) ? $_GET['name'] : sprintf(l('image_create.name_x'), \Altum\Date::get())),
            'input' => isset($_GET['input']) ? $_GET['input'] : (isset($_POST['input']) ? $_POST['input'] : ''),
            'size' => isset($_GET['size']) ? $_GET['size'] : (isset($_POST['size']) ? $_POST['size'] : reset($ai_model['sizes'])),
            'quality' => isset($_GET['quality']) ? $_GET['quality'] : (isset($_POST['quality']) ? $_POST['quality'] : reset($ai_model['qualities'])),
            'output_format' => isset($_GET['output_format']) ? $_GET['output_format'] : (isset($_POST['output_format']) ? $_POST['output_format'] : (count($ai_model['output_formats']) ? reset($ai_model['output_formats']) : null)),
            'background' => isset($_GET['background']) ? $_GET['background'] : (isset($_POST['background']) ? $_POST['background'] : (count($ai_model['backgrounds']) ? reset($ai_model['backgrounds']) : null)),
            'variants' => isset($_GET['variants']) ? $_GET['variants'] : (isset($_POST['variants']) ? $_POST['variants'] : 1),
            'project_id' => isset($_GET['project_id']) ? $_GET['project_id'] : (isset($_POST['project_id']) ? $_POST['project_id'] : null),
        ];

        /* Prepare the view */
        $data = [
            'values' => $values,
            'ai_model' => $ai_model,
            'projects' => $projects,
        ];

        $view = new \Altum\View(\Altum\Plugin::get('aix')->path . 'views/image-create/index', (array) $this, true);

        $this->add_view_content('content', $view->run($data));

    }

    public function create_ajax() {
        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Response::json('Please create an account on the demo to test out this function.', 'error');

        if(empty($_POST)) {
            throw_404();
        }

        set_time_limit(0);

        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('aix') || !settings()->aix->images_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.images')) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        /* Check for the plan limit */
        $images_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`aix_images_current_month`');
        if($this->user->plan_settings->images_per_month_limit != -1 && $images_current_month >= $this->user->plan_settings->images_per_month_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Ai image models */
        $ai_image_models = require \Altum\Plugin::get('aix')->path . 'includes/ai_image_models.php';

        /* Selected AI model */
        if(!isset($this->user->plan_settings->images_api)) {
            $this->user->plan_settings->images_api = 'gpt-image-1';
        }

        /* Free text model metadata */
        if(isset($ai_image_models[$this->user->plan_settings->images_api])) {
            $ai_model = $ai_image_models[$this->user->plan_settings->images_api];
        } else {
            $ai_model = [
                'api' => 'openai',
                'name' => $this->user->plan_settings->images_api,
                'max_length' => 30000,
                'sizes' => ['1024x1024'],
                'variants' => [1],
                'qualities' => ['auto', 'low', 'medium', 'high'],
                'output_formats' => ['png', 'webp', 'jpeg'],
                'backgrounds' => ['auto', 'transparent', 'opaque'],
            ];
        }

        $_POST['name'] = input_clean($_POST['name'], 64);
        $_POST['input'] = input_clean($_POST['input'], $ai_model['max_length']);
        $_POST['size'] = $_POST['size'] && in_array($_POST['size'], $ai_model['sizes']) ? $_POST['size'] : reset($ai_model['sizes']);
        $_POST['variants'] = in_array((int) $_POST['variants'], $ai_model['variants']) ? (int) $_POST['variants'] : 1;
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
        $_POST['quality'] = count($ai_model['qualities']) && !empty($_POST['quality']) && in_array($_POST['quality'], $ai_model['qualities']) ? $_POST['quality'] : reset($ai_model['qualities']);
        $_POST['output_format'] = count($ai_model['output_formats']) && !empty($_POST['output_format']) && in_array($_POST['output_format'], $ai_model['output_formats']) ? $_POST['output_format'] : (count($ai_model['output_formats']) ? reset($ai_model['output_formats']) : 'png');
        $_POST['background'] = count($ai_model['backgrounds']) && !empty($_POST['background']) && in_array($_POST['background'], $ai_model['backgrounds']) ? $_POST['background'] : (count($ai_model['backgrounds']) ? reset($ai_model['backgrounds']) : 'auto');

        /* Check the selected variant count against the plan limit */
        if($this->user->plan_settings->images_per_month_limit != -1 && $images_current_month + $_POST['variants'] > $this->user->plan_settings->images_per_month_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Transparent backgrounds require a compatible format */
        if($_POST['background'] == 'transparent' && $_POST['output_format'] == 'jpeg') {
            Response::json(l('images.error_message.transparent_background'), 'error');
        }

        /* Check for any errors */
        $required_fields = ['name', 'input'];
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
            Response::json(l('images.error_message.openai_api_key'), 'error');
        }

        if(!is_writable(UPLOADS_PATH . Uploads::get_path('images'))) {
            Response::json(sprintf(l('global.error_message.directory_not_writable'), UPLOADS_PATH . Uploads::get_path('images')), 'error');
        }

        /* Check for timeouts */
        if(settings()->aix->input_moderation_is_enabled) {
            $cache_instance = cache()->getItem('user?flagged=' . $this->user->user_id);
            if(!is_null($cache_instance->get())) {
                Response::json(l('images.error_message.timed_out'), 'error');
            }
        }

        /* Input */
        $input = $_POST['input'];

        /* Try to increase the database timeout as well */
        database()->query("set session wait_timeout=600;");

        /* Do not use sessions anymore to not lockout the user from doing anything else on the site */
        session_write_close();

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
                        'input' => $input,
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
                    Response::json(l('images.error_message.flagged'), 'error');
                }

            } catch (\Exception $exception) {
                Response::json($exception->getMessage(), 'error');
            }
        }

        /* Variants */
        $variants_ids = [];

        /* Request based on the chosen API */
        switch($this->user->plan_settings->images_api) {
            case 'gpt-image-1':
            case 'dall-e-3':
            default:

                $request_body = [
                    'model' => $this->user->plan_settings->images_api,
                    'prompt' => $input,
                    'size' => $_POST['size'],
                    'n' => $_POST['variants'],
                    'user' => 'user_id:' . $this->user->user_id,
                ];

                if(count($ai_model['qualities'])) {
                    $request_body['quality'] = $_POST['quality'];
                }

                if(count($ai_model['output_formats'])) {
                    $request_body['output_format'] = $_POST['output_format'];
                }

                if(count($ai_model['backgrounds'])) {
                    $request_body['background'] = $_POST['background'];
                }

                if($this->user->plan_settings->images_api == 'dall-e-3') {
                    $request_body['response_format'] = 'b64_json';
                }

                try {
                    $response = \Unirest\Request::post(
                        'https://api.openai.com/v1/images/generations',
                        [
                            'Authorization' => 'Bearer '  . $openai_api_key,
                            'Content-Type' => 'application/json',
                        ],
                        \Unirest\Request\Body::json($request_body)
                    );

                    if($response->code >= 400) {
                        Response::json($response->body->error->message, 'error');
                    }

                } catch (\Exception $exception) {
                    Response::json($exception->getMessage(), 'error');
                }

                /* Get info after the request */
                $info = \Unirest\Request::getInfo();

                /* Some needed variables */
                $api_response_time = $info['total_time'] * 1000;

                if(!isset($response->body->data) || !is_array($response->body->data) || !count($response->body->data)) {
                    Response::json(l('images.error_message.invalid_response'), 'error');
                }

                /* Decode generated results */
                $generated_images = [];
                foreach($response->body->data as $key => $result) {
                    if(empty($result->b64_json)) {
                        Response::json(l('images.error_message.invalid_response'), 'error');
                    }

                    /* Decode the image */
                    $image_content = base64_decode($result->b64_json, true);
                    if($image_content === false) {
                        Response::json(l('images.error_message.invalid_response'), 'error');
                    }

                    $generated_image = [
                        'key' => $key,
                        'content' => $image_content,
                    ];

                    if(isset($result->revised_prompt)) {
                        $generated_image['revised_prompt'] = $result->revised_prompt;
                    }

                    $generated_images[] = $generated_image;
                }

                /* Generated results */
                $generated_variants_count = count($generated_images);

                /* Go through each result */
                foreach($generated_images as $generated_image) {
                    /* Save the image temporarily */
                    $temp_image_name = md5(uniqid('', true) . random_bytes(16)) . '.' . $_POST['output_format'];
                    $temp_image_path = sys_get_temp_dir() . '/' . $temp_image_name;
                    if(file_put_contents($temp_image_path, $generated_image['content']) === false) {
                        Response::json(l('global.error_message.basic'), 'error');
                    }

                    /* Fake uploaded image */
                    $_FILES['image'] = [
                        'name' => 'altum.' . $_POST['output_format'],
                        'tmp_name' => $temp_image_path,
                        'error' => null,
                        'size' => filesize($temp_image_path),
                    ];

                    $image = \Altum\Uploads::process_upload_fake('images', 'image', 'json_error', null);

                    /* Image settings */
                    $settings = [
                        'variants' => $generated_variants_count,
                        'prompt' => $input,
                        'quality' => $_POST['quality'],
                        'output_format' => $_POST['output_format'],
                        'background' => $_POST['background'],
                    ];

                    if(isset($response->body->usage)) {
                        $settings['usage'] = $response->body->usage;
                    }

                    if(isset($generated_image['revised_prompt'])) {
                        $settings['revised_prompt'] = $generated_image['revised_prompt'];
                    }

                    $settings = json_encode($settings);

                    /* Prepare a custom name if needed */
                    $name = $_POST['name'];

                    if($generated_variants_count > 1) {
                        $name .= ' - ' . ($generated_image['key'] + 1) . '/' . $generated_variants_count;
                    }

                    /* Database query */
                    $image_id = db()->insert('images', [
                        'user_id' => $this->user->user_id,
                        'project_id' => $_POST['project_id'],
                        'name' => $name,
                        'input' => $_POST['input'],
                        'image' => $image,
                        'size' => $_POST['size'],
                        'settings' => $settings,
                        'api' => $this->user->plan_settings->images_api,
                        'api_response_time' => $api_response_time,
                        'datetime' => get_date(),
                    ]);

                    if(!$image_id) {
                        \Altum\Uploads::delete_uploaded_file($image, 'images');
                        Response::json(l('global.error_message.basic'), 'error');
                    }

                    /* Add variant to the array */
                    $variants_ids[] = $image_id;
                }

                break;
        }

        if(!count($variants_ids)) {
            Response::json(l('images.error_message.invalid_response'), 'error');
        }

        /* Go through each generated image to link them up */
        $variants_ids_jsoned = json_encode($variants_ids);
        foreach($variants_ids as $image_id) {
            db()->where('image_id', $image_id)->update('images', [
                'variants_ids' => $variants_ids_jsoned,
            ]);
        }

        /* Database query */
        db()->where('user_id', $this->user->user_id)->update('users', [
            'aix_images_current_month' => db()->inc(count($variants_ids))
        ]);

        /* Redirect to the first generated variant */
        $redirect_image_id = reset($variants_ids);

        /* Set a nice success message */
        Response::json(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'), 'success', ['url' => url('image-update/' . $redirect_image_id)]);

    }

}
