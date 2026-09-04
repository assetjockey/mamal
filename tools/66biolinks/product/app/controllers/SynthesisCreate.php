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

class SynthesisCreate extends Controller {

    public function index() {
        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('aix') || !settings()->aix->syntheses_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.syntheses')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('syntheses');
        }

        /* Check for the plan limit */
        $syntheses_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`aix_syntheses_current_month`');
        if($this->user->plan_settings->syntheses_per_month_limit != -1 && $syntheses_current_month >= $this->user->plan_settings->syntheses_per_month_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('syntheses');
        }

        /* Check for exclusive personal API usage limitation */
        if($this->user->plan_settings->exclusive_personal_api_keys && empty($this->user->preferences->openai_api_key)) {
            Alerts::add_error(sprintf(l('account_preferences.error_message.aix.openai_api_key'), '<a href="' . url('account-preferences') . '"><strong>' . l('account_preferences.menu') . '</strong></a>'));
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Ai synthesis models */
        $ai_syntheses_models = require \Altum\Plugin::get('aix')->path . 'includes/ai_syntheses_models.php';

        /* Selected AI model */
        if(!isset($this->user->plan_settings->syntheses_model)) {
            $this->user->plan_settings->syntheses_model = 'gpt-4o-mini-tts';
        }

        /* Free text model metadata */
        if(isset($ai_syntheses_models[$this->user->plan_settings->syntheses_model])) {
            $ai_model = $ai_syntheses_models[$this->user->plan_settings->syntheses_model];
        } else {
            $ai_model = [
                'api' => 'openai',
                'name' => $this->user->plan_settings->syntheses_model,
                'max_length' => 4096,
                'supports_instructions' => true,
            ];
        }

        /* Voices */
        $ai_voices = require \Altum\Plugin::get('aix')->path . 'includes/ai_syntheses_openai_audio_voices.php';

        /* Formats */
        $ai_formats = require \Altum\Plugin::get('aix')->path . 'includes/ai_syntheses_openai_audio_formats.php';

        /* Clear $_GET */
        foreach($_GET as $key => $value) {
            $_GET[$key] = input_clean($value);
        }

        $values = [
            'name' => isset($_POST['name']) ? $_POST['name'] : (isset($_GET['name']) ? $_GET['name'] : sprintf(l('synthesis_create.name_x'), \Altum\Date::get())),
            'input' => isset($_GET['input']) ? $_GET['input'] : (isset($_POST['input']) ? $_POST['input'] : ''),
            'instructions' => isset($_GET['instructions']) ? $_GET['instructions'] : (isset($_POST['instructions']) ? $_POST['instructions'] : ''),
            'voice_id' => isset($_GET['voice_id']) ? $_GET['voice_id'] : (isset($_POST['voice_id']) ? $_POST['voice_id'] : 'alloy'),
            'format' => isset($_GET['format']) ? $_GET['format'] : (isset($_POST['format']) ? $_POST['format'] : array_key_first($ai_formats)),
            'speed' => isset($_GET['speed']) ? $_GET['speed'] : (isset($_POST['speed']) ? $_POST['speed'] : 1),
            'project_id' => isset($_GET['project_id']) ? $_GET['project_id'] : (isset($_POST['project_id']) ? $_POST['project_id'] : null),
        ];

        /* Prepare the view */
        $data = [
            'values' => $values,
            'ai_voices' => $ai_voices,
            'ai_formats' => $ai_formats,
            'projects' => $projects ?? [],
            'ai_model' => $ai_model,
        ];

        $view = new \Altum\View(\Altum\Plugin::get('aix')->path . 'views/synthesis-create/index', (array) $this, true);

        $this->add_view_content('content', $view->run($data));

    }

    public function create_ajax() {
        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Response::json('Please create an account on the demo to test out this function.', 'error');

        if(empty($_POST)) {
            throw_404();
        }

        set_time_limit(0);

        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('aix') || !settings()->aix->syntheses_is_enabled) {
            throw_404();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.syntheses')) {
            Response::json(l('global.info_message.team_no_access'), 'error');
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Ai synthesis models */
        $ai_syntheses_models = require \Altum\Plugin::get('aix')->path . 'includes/ai_syntheses_models.php';

        /* Selected AI model */
        if(!isset($this->user->plan_settings->syntheses_model)) {
            $this->user->plan_settings->syntheses_model = 'gpt-4o-mini-tts';
        }

        /* Free text model metadata */
        if(isset($ai_syntheses_models[$this->user->plan_settings->syntheses_model])) {
            $ai_model = $ai_syntheses_models[$this->user->plan_settings->syntheses_model];
        } else {
            $ai_model = [
                'api' => 'openai',
                'name' => $this->user->plan_settings->syntheses_model,
                'max_length' => 4096,
                'supports_instructions' => true,
            ];
        }

        /* Voices */
        $ai_voices = require \Altum\Plugin::get('aix')->path . 'includes/ai_syntheses_openai_audio_voices.php';

        /* Formats */
        $ai_formats = require \Altum\Plugin::get('aix')->path . 'includes/ai_syntheses_openai_audio_formats.php';

        /* Filter some of the variables */
        $_POST['name'] = isset($_POST['name']) ? input_clean($_POST['name'], 64) : '';
        $_POST['input'] = isset($_POST['input']) ? trim(strip_tags(mb_substr($_POST['input'], 0, $ai_model['max_length']))) : '';
        $_POST['instructions'] = isset($_POST['instructions']) ? trim(strip_tags(mb_substr($_POST['instructions'], 0, 512))) : '';
        $_POST['voice_id'] = !empty($_POST['voice_id']) && array_key_exists($_POST['voice_id'], $ai_voices) ? $_POST['voice_id'] : 'alloy';
        $_POST['format'] = !empty($_POST['format']) && array_key_exists($_POST['format'], $ai_formats) ? $_POST['format'] : array_key_first($ai_formats);
        $_POST['speed'] = isset($_POST['speed']) ? (float) $_POST['speed'] : 1;
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
        $characters = mb_strlen($_POST['input']);

        /* Check for any errors */
        $required_fields = ['name', 'input'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                Response::json(l('global.error_message.empty_fields'), 'error');
            }
        }

        if($_POST['speed'] < 0.25 || $_POST['speed'] > 4) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        if(!\Altum\Csrf::check('global_token')) {
            Response::json(l('global.error_message.invalid_csrf_token'), 'error');
        }

        /* Check for the plan limit */
        $syntheses_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`aix_syntheses_current_month`');
        if($this->user->plan_settings->syntheses_per_month_limit != -1 && $syntheses_current_month >= $this->user->plan_settings->syntheses_per_month_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* Check for the plan limit */
        $synthesized_characters_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`aix_synthesized_characters_current_month`');
        if($this->user->plan_settings->synthesized_characters_per_month_limit != -1 && $synthesized_characters_current_month + $characters > $this->user->plan_settings->synthesized_characters_per_month_limit) {
            Response::json(l('global.info_message.plan_feature_limit'), 'error');
        }

        /* API key check */
        $openai_api_key = get_random_line_from_text($this->user->plan_settings->exclusive_personal_api_keys ? $this->user->preferences->openai_api_key : settings()->aix->openai_api_key);
        if(!$openai_api_key) {
            Response::json(l('syntheses.error_message.openai_api_key'), 'error');
        }

        if(!is_writable(UPLOADS_PATH . Uploads::get_path('syntheses'))) {
            Response::json(sprintf(l('global.error_message.directory_not_writable'), UPLOADS_PATH . Uploads::get_path('syntheses')), 'error');
        }

        /* Try to increase the database timeout as well */
        database()->query("set session wait_timeout=600;");

        /* Do not use sessions anymore to not lockout the user from doing anything else on the site */
        session_write_close();

        /* Request body */
        $request_body = [
            'model' => $this->user->plan_settings->syntheses_model,
            'input' => $_POST['input'],
            'voice' => $_POST['voice_id'],
            'response_format' => $_POST['format'],
            'speed' => $_POST['speed'],
        ];

        if($_POST['instructions'] && $ai_model['supports_instructions']) {
            $request_body['instructions'] = $_POST['instructions'];
        }

        try {
            $response = \Unirest\Request::post(
                'https://api.openai.com/v1/audio/speech',
                [
                    'Authorization' => 'Bearer '  . $openai_api_key,
                    'Content-Type' => 'application/json',
                ],
                \Unirest\Request\Body::json($request_body)
            );

            if($response->code >= 400) {
                $error_message = isset($response->body->error->message) ? $response->body->error->message : l('global.error_message.basic');
                Response::json($error_message, 'error');
            }

        } catch (\Exception $exception) {
            Response::json($exception->getMessage(), 'error');
        }

        /* Get info after the request */
        $info = \Unirest\Request::getInfo();

        /* Some needed variables */
        $api_response_time = $info['total_time'] * 1000;

        if(!$response->raw_body) {
            Response::json(l('syntheses.error_message.invalid_response'), 'error');
        }

        /* Save the synthesis temporarily */
        $temp_synthesis_name = md5(uniqid('', true) . random_bytes(16)) . '.' . $_POST['format'];
        if(!file_put_contents(Uploads::get_full_path('syntheses') . $temp_synthesis_name, $response->raw_body)) {
            Response::json(l('global.error_message.basic'), 'error');
        }

        /* Fake uploaded synthesis */
        $_FILES['synthesis'] = [
            'name' => 'altum.' . $ai_formats[$_POST['format']],
            'tmp_name' => Uploads::get_full_path('syntheses') . $temp_synthesis_name,
            'error' => null,
            'size' => 0,
        ];

        $file = \Altum\Uploads::process_upload_fake('syntheses', 'synthesis', 'json_error', null);
        sleep(1);

        if(!$file) {
            /* Delete temp */
            if(file_exists(Uploads::get_full_path('syntheses') . $temp_synthesis_name)) {
                unlink(Uploads::get_full_path('syntheses') . $temp_synthesis_name);
            }

            Response::json(l('global.error_message.basic'), 'error');
        }

        /* Synthesis settings */
        $settings = [
            'instructions' => $_POST['instructions'],
            'speed' => $_POST['speed'],
        ];

        $settings = json_encode($settings);

        /* Prepare a custom name if needed */
        $name = $_POST['name'];

        /* Database query */
        $synthesis_id = db()->insert('syntheses', [
            'user_id' => $this->user->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $name,
            'input' => $_POST['input'],
            'file' => $file,
            'language' => null,
            'model' => $this->user->plan_settings->syntheses_model,
            'format' => $_POST['format'],
            'voice_id' => $_POST['voice_id'],
            'settings' => $settings,
            'characters' => $characters,
            'api_response_time' => $api_response_time,
            'datetime' => get_date(),
        ]);

        if(!$synthesis_id) {
            /* Delete uploaded synthesis */
            \Altum\Uploads::delete_uploaded_file($file, 'syntheses');

            Response::json(l('global.error_message.basic'), 'error');
        }

        /* Database query */
        db()->where('user_id', $this->user->user_id)->update('users', [
            'aix_syntheses_current_month' => db()->inc(),
            'aix_synthesized_characters_current_month' => db()->inc($characters)
        ]);

        /* Set a nice success message */
        Response::json(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'), 'success', ['url' => url('synthesis-update/' . $synthesis_id)]);

    }

}
