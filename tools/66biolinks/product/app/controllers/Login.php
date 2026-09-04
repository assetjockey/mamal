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
use Altum\Captcha;
use Altum\Logger;
use Altum\Models\User;
use Google\Client;
use Google\Service\Oauth2;

defined('ALTUMCODE') || die();

class Login extends Controller {

    public function index() {

        $method	= (isset($this->params[0])) ? $this->params[0] : null;
        $redirect = process_and_get_redirect_params() ?? 'dashboard';
        $redirect_append = $redirect ? '?redirect=' . $redirect : null;

        if($method !== 'one-time-login-code') {
            \Altum\Authentication::guard('guest');
        }

        /* Default values */
        $values = [
'email' => isset($_GET['email']) && is_string($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL) ? query_clean($_GET['email']) : '',
            'password' => '',
            'rememberme' => isset($_POST['rememberme']) || settings()->users->login_rememberme_checkbox_is_checked,
        ];

        //ALTUMCODE:DEMO if(DEMO) {$values['email'] = 'admin'; $values['password'] = 'admin';$user=(object)['twofa_secret' => null];}

        /* Initiate captcha */
        $captcha = new Captcha();

        /* Clear out any pending social login Two-factor Authentication when leaving its verification route */
        if($method != 'social-twofa' && session_has('social_twofa_user_id')) {
            session_unset_key('social_twofa_user_id');
            session_unset_key('social_twofa_method');
            session_unset_key('social_twofa_expiration');
        }

        /* Social login Two-factor Authentication */
        if($method == 'social-twofa') {
            $social_twofa_user_id = session_get('social_twofa_user_id');
            $social_twofa_method = session_get('social_twofa_method');
            $social_twofa_expiration = session_get('social_twofa_expiration');

            if(!$social_twofa_user_id || !in_array($social_twofa_method, ['facebook', 'google', 'twitter', 'discord', 'linkedin', 'microsoft', 'apple', 'github']) || !$social_twofa_expiration || $social_twofa_expiration < time()) {
                session_unset_key('social_twofa_user_id');
                session_unset_key('social_twofa_method');
                session_unset_key('social_twofa_expiration');
                redirect('login' . $redirect_append);
            }

            $user = db()->where('user_id', $social_twofa_user_id)->getOne('users', ['user_id', 'password', 'name', 'status', 'twofa_secret', 'language']);

            if(!$user || $user->status != 1 || !$user->twofa_secret) {
                session_unset_key('social_twofa_user_id');
                session_unset_key('social_twofa_method');
                session_unset_key('social_twofa_expiration');
                Alerts::add_error(l('login.error_message.user_not_active'));
                redirect('login' . $redirect_append);
            }
        }

        /* One time login */
        if($method == 'one-time-login-code') {
            $one_time_login_code = isset($this->params[1]) ? query_clean($this->params[1]) : null;

            if(empty($one_time_login_code)) {
                redirect('login' . $redirect_append);
            }

            /* Try to get the user from the database */
            $user = db()->where('one_time_login_code', $one_time_login_code)->getOne('users', ['user_id', 'password', 'name', 'status', 'language']);

            if(!$user) {
                redirect('login' . $redirect_append);
            }

            if($user->status != 1) {
                Alerts::add_error(l('login.error_message.user_not_active'));
                redirect('login' . $redirect_append);
            }

            /* Login the user */
            session_regenerate_id(true);
                session_set('user_id', $user->user_id);
            session_set('user_password_hash', md5($user->password));

            (new User())->login_aftermath_update($user->user_id);

            /* Remove one time login */
            db()->where('user_id', $user->user_id)->update('users', ['one_time_login_code' => null]);

            /* Set a welcome message */
            Alerts::add_info(sprintf(l('login.info_message.logged_in'), $user->name));

            session_unset_key('redirect');

            /* Check to see if the user has a custom language set */
            if(\Altum\Language::$name == $user->language) {
                redirect($redirect);
            } else {
                redirect((\Altum\Language::$active_languages[$user->language] ? \Altum\Language::$active_languages[$user->language] . '/' : null) . $redirect, true);
            }
        }

        /* Facebook Login / Register */
        if(settings()->facebook->is_enabled && in_array($method, ['facebook-initiate', 'facebook'])) {
            $facebook_config = [
                'callback' => SITE_URL . 'login/facebook',
                'keys' => [
                    'key' => settings()->facebook->app_id,
                    'secret' => settings()->facebook->app_secret,
                ],
                'scope' => 'email public_profile',
            ];

            if($method == 'facebook-initiate') {
                session_set('register_language', \Altum\Language::$name);

                try {
                    $facebook = new \Hybridauth\Provider\Facebook($facebook_config);
                    $facebook->disconnect();
                    $facebook->authenticate();
                } catch (\Exception $exception) {
                    if(isset($facebook)) $facebook->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][Facebook] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            if($method == 'facebook' && isset($_GET['code'])) {
                try {
                    $facebook = new \Hybridauth\Provider\Facebook($facebook_config);
                    $facebook->authenticate();
                    $facebook_account_info = $facebook->getUserProfile();
                    $name = $facebook_account_info->displayName;
                    $email = $facebook_account_info->email;

                    /* Clear out the provider access token from the session */
                    $facebook->disconnect();

                    if(is_null($email)) {
                        session_unset_key('register_language');
                        error_log('[Social login][Facebook] The provider did not return an email address.');
                        Alerts::add_error(l('login.error_message.social_authentication'));
                        redirect('login' . $redirect_append);
                    }

                    $this->process_social_login($email, $name, $redirect, $method);
                } catch (\Exception $exception) {
                    if(isset($facebook)) $facebook->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][Facebook] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            elseif($method == 'facebook') {
                try {
                    (new \Hybridauth\Provider\Facebook($facebook_config))->disconnect();
                } catch (\Exception $exception) {
                    error_log('[Social login][Facebook] ' . (string) $exception);
                }
                session_unset_key('register_language');
                error_log('[Social login][Facebook] Callback error: ' . json_encode(['error' => $_GET['error'] ?? null, 'error_description' => $_GET['error_description'] ?? null]));
                Alerts::add_error(l('login.error_message.social_authentication'));
                redirect('login' . $redirect_append);
            }
        }

        /* Google Login / Register */
        if(settings()->google->is_enabled && in_array($method, ['google-initiate', 'google'])) {
            $google_config = [
                'callback' => SITE_URL . 'login/google',
                'keys' => [
                    'key' => settings()->google->client_id,
                    'secret' => settings()->google->client_secret,
                ],
                'scope' => 'email profile',
            ];

            if($method == 'google-initiate') {
                session_set('register_language', \Altum\Language::$name);

                try {
                    $google = new \Hybridauth\Provider\Google($google_config);
                    $google->disconnect();
                    $google->authenticate();
                } catch (\Exception $exception) {
                    if(isset($google)) $google->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][Google] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            if($method == 'google' && isset($_GET['code'])) {
                try {
                    $google = new \Hybridauth\Provider\Google($google_config);
                    $google->authenticate();
                    $google_account_info = $google->getUserProfile();
                    $name = $google_account_info->displayName;
                    $email = $google_account_info->email;

                    /* Clear out the provider access token from the session */
                    $google->disconnect();

                    if(is_null($email)) {
                        session_unset_key('register_language');
                        error_log('[Social login][Google] The provider did not return an email address.');
                        Alerts::add_error(l('login.error_message.social_authentication'));
                        redirect('login' . $redirect_append);
                    }

                    $this->process_social_login($email, $name, $redirect, $method);
                } catch (\Exception $exception) {
                    if(isset($google)) $google->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][Google] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            elseif($method == 'google') {
                try {
                    (new \Hybridauth\Provider\Google($google_config))->disconnect();
                } catch (\Exception $exception) {
                    error_log('[Social login][Google] ' . (string) $exception);
                }
                session_unset_key('register_language');
                error_log('[Social login][Google] Callback error: ' . json_encode(['error' => $_GET['error'] ?? null, 'error_description' => $_GET['error_description'] ?? null]));
                Alerts::add_error(l('login.error_message.social_authentication'));
                redirect('login' . $redirect_append);
            }
        }

        /* X Login / Register */
        if(settings()->twitter->is_enabled && in_array($method, ['twitter-initiate', 'twitter'])) {
            $twitter_config = [
                'callback' => SITE_URL . 'login/twitter',
                'keys' => [
                    'key' => settings()->twitter->consumer_api_key,
                    'secret' => settings()->twitter->consumer_api_secret,
                ],
                'include_email' => true,
            ];

            if($method == 'twitter-initiate') {
                session_set('register_language', \Altum\Language::$name);

                try {
                    $twitter = new \Hybridauth\Provider\Twitter($twitter_config);
                    $twitter->disconnect();
                    $twitter->authenticate();
                } catch (\Exception $exception) {
                    if(isset($twitter)) $twitter->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][X] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            if($method == 'twitter' && isset($_GET['oauth_token'], $_GET['oauth_verifier'])) {
                try {
                    $twitter = new \Hybridauth\Provider\Twitter($twitter_config);
                    $twitter->authenticate();
                    $twitter_account_info = $twitter->getUserProfile();
                    $name = $twitter_account_info->displayName;
                    $email = $twitter_account_info->email;

                    /* Clear out the provider access token from the session */
                    $twitter->disconnect();

                    if(is_null($email)) {
                        session_unset_key('register_language');
                        error_log('[Social login][X] The provider did not return an email address.');
                        Alerts::add_error(l('login.error_message.social_authentication'));
                        redirect('login' . $redirect_append);
                    }

                    $this->process_social_login($email, $name, $redirect, $method);
                } catch (\Exception $exception) {
                    if(isset($twitter)) $twitter->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][X] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            elseif($method == 'twitter') {
                try {
                    (new \Hybridauth\Provider\Twitter($twitter_config))->disconnect();
                } catch (\Exception $exception) {
                    error_log('[Social login][X] ' . (string) $exception);
                }
                session_unset_key('register_language');
                error_log('[Social login][X] Callback error: ' . json_encode(['denied' => $_GET['denied'] ?? null]));
                Alerts::add_error(l('login.error_message.social_authentication'));
                redirect('login' . $redirect_append);
            }
        }

        /* Discord Login / Register */
        if(settings()->discord->is_enabled && in_array($method, ['discord-initiate', 'discord'])) {
            $discord_config = [
                'callback' => SITE_URL . 'login/discord',
                'keys' => [
                    'key' => settings()->discord->client_id,
                    'secret' => settings()->discord->client_secret,
                ],
                'scope' => 'email identify',
            ];

            if($method == 'discord-initiate') {
                session_set('register_language', \Altum\Language::$name);

                try {
                    $discord = new \Hybridauth\Provider\Discord($discord_config);
                    $discord->disconnect();
                    $discord->authenticate();
                } catch (\Exception $exception) {
                    if(isset($discord)) $discord->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][Discord] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            if($method == 'discord' && isset($_GET['code'])) {
                try {
                    $discord = new \Hybridauth\Provider\Discord($discord_config);
                    $discord->authenticate();
                    $discord_account_info = $discord->getUserProfile();
                    $name = $discord_account_info->displayName;
                    $email = $discord_account_info->email;

                    /* Clear out the provider access token from the session */
                    $discord->disconnect();

                    if(is_null($email)) {
                        session_unset_key('register_language');
                        error_log('[Social login][Discord] The provider did not return an email address.');
                        Alerts::add_error(l('login.error_message.social_authentication'));
                        redirect('login' . $redirect_append);
                    }

                    $this->process_social_login($email, $name, $redirect, $method);
                } catch (\Exception $exception) {
                    if(isset($discord)) $discord->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][Discord] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            elseif($method == 'discord') {
                try {
                    (new \Hybridauth\Provider\Discord($discord_config))->disconnect();
                } catch (\Exception $exception) {
                    error_log('[Social login][Discord] ' . (string) $exception);
                }
                session_unset_key('register_language');
                error_log('[Social login][Discord] Callback error: ' . json_encode(['error' => $_GET['error'] ?? null, 'error_description' => $_GET['error_description'] ?? null]));
                Alerts::add_error(l('login.error_message.social_authentication'));
                redirect('login' . $redirect_append);
            }
        }

        /* LinkedIn Login / Register */
        if(settings()->linkedin->is_enabled && in_array($method, ['linkedin-initiate', 'linkedin'])) {
            $linkedin_config = [
                'callback' => SITE_URL . 'login/linkedin',
                'keys' => [
                    'key' => settings()->linkedin->client_id,
                    'secret' => settings()->linkedin->client_secret,
                ],
                'scope' => 'openid profile email',
            ];

            if($method == 'linkedin-initiate') {
                session_set('register_language', \Altum\Language::$name);

                try {
                    $linkedin = new \Hybridauth\Provider\LinkedInOpenID($linkedin_config);
                    $linkedin->disconnect();
                    $linkedin->authenticate();
                } catch (\Exception $exception) {
                    if(isset($linkedin)) $linkedin->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][LinkedIn] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            if($method == 'linkedin' && isset($_GET['code'])) {
                try {
                    $linkedin = new \Hybridauth\Provider\LinkedInOpenID($linkedin_config);
                    $linkedin->authenticate();
                    $linkedin_account_info = $linkedin->getUserProfile();
                    $name = $linkedin_account_info->displayName;
                    $email = $linkedin_account_info->email;

                    /* Clear out the provider access token from the session */
                    $linkedin->disconnect();

                    if(is_null($email)) {
                        session_unset_key('register_language');
                        error_log('[Social login][LinkedIn] The provider did not return an email address.');
                        Alerts::add_error(l('login.error_message.social_authentication'));
                        redirect('login' . $redirect_append);
                    }

                    $this->process_social_login($email, $name, $redirect, $method);
                } catch (\Exception $exception) {
                    if(isset($linkedin)) $linkedin->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][LinkedIn] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            elseif($method == 'linkedin') {
                try {
                    (new \Hybridauth\Provider\LinkedInOpenID($linkedin_config))->disconnect();
                } catch (\Exception $exception) {
                    error_log('[Social login][LinkedIn] ' . (string) $exception);
                }
                session_unset_key('register_language');
                error_log('[Social login][LinkedIn] Callback error: ' . json_encode(['error' => $_GET['error'] ?? null, 'error_description' => $_GET['error_description'] ?? null]));
                Alerts::add_error(l('login.error_message.social_authentication'));
                redirect('login' . $redirect_append);
            }
        }

        /* Microsoft Login / Register */
        if(settings()->microsoft->is_enabled && in_array($method, ['microsoft-initiate', 'microsoft'])) {
            $microsoft_config = [
                'callback' => SITE_URL . 'login/microsoft',
                'keys' => [
                    'id' => settings()->microsoft->client_id,
                    'secret' => settings()->microsoft->client_secret,
                ],
                'scope' => 'user.read',
                'tenant' => 'common',
            ];

            if($method == 'microsoft-initiate') {
                session_set('register_language', \Altum\Language::$name);

                try {
                    $microsoft = new \Hybridauth\Provider\MicrosoftGraph($microsoft_config);
                    $microsoft->disconnect();
                    $microsoft->authenticate();
                } catch (\Exception $exception) {
                    if(isset($microsoft)) $microsoft->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][Microsoft] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            if($method == 'microsoft' && isset($_GET['code'])) {
                try {
                    $microsoft = new \Hybridauth\Provider\MicrosoftGraph($microsoft_config);
                    $microsoft->authenticate();
                    $microsoft_account_info = $microsoft->getUserProfile();
                    $name = $microsoft_account_info->displayName;
                    $email = $microsoft_account_info->email;
                    $id = $microsoft_account_info->identifier;

                    /* Clear out the provider access token from the session */
                    $microsoft->disconnect();

                    if(is_null($email)) {
                        session_unset_key('register_language');
                        error_log('[Social login][Microsoft] The provider did not return an email address.');
                        Alerts::add_error(l('login.error_message.social_authentication'));
                        redirect('login' . $redirect_append);
                    }

                    $this->process_social_login($email, $name, $redirect, $method, $id);
                } catch (\Exception $exception) {
                    if(isset($microsoft)) $microsoft->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][Microsoft] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            elseif($method == 'microsoft') {
                try {
                    (new \Hybridauth\Provider\MicrosoftGraph($microsoft_config))->disconnect();
                } catch (\Exception $exception) {
                    error_log('[Social login][Microsoft] ' . (string) $exception);
                }
                session_unset_key('register_language');
                error_log('[Social login][Microsoft] Callback error: ' . json_encode(['error' => $_GET['error'] ?? null, 'error_description' => $_GET['error_description'] ?? null]));
                Alerts::add_error(l('login.error_message.social_authentication'));
                redirect('login' . $redirect_append);
            }
        }

        /* GitHub Login / Register */
        if(settings()->github->is_enabled && in_array($method, ['github-initiate', 'github'])) {
            $github_config = [
                'callback' => SITE_URL . 'login/github',
                'keys' => [
                    'key' => settings()->github->client_id,
                    'secret' => settings()->github->client_secret,
                ],
                'scope' => 'user:email',
            ];

            if($method == 'github-initiate') {
                session_set('register_language', \Altum\Language::$name);

                try {
                    $github = new \Hybridauth\Provider\GitHub($github_config);
                    $github->disconnect();
                    $github->authenticate();
                } catch (\Exception $exception) {
                    if(isset($github)) $github->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][GitHub] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            if($method == 'github' && isset($_GET['code'])) {
                try {
                    $github = new \Hybridauth\Provider\GitHub($github_config);
                    $github->authenticate();
                    $github_account_info = $github->getUserProfile();
                    $name = $github_account_info->displayName;
                    $email = $github_account_info->emailVerified;
                    $id = $github_account_info->identifier;

                    /* Public profile emails are not marked as verified, so verify them through the emails endpoint */
                    if(is_null($email)) {
                        $github_emails = $github->apiRequest('user/emails');
                        foreach($github_emails as $github_email) {
                            if($github_email->primary && $github_email->verified) {
                                $email = $github_email->email;
                                break;
                            }
                        }
                    }

                    /* Clear out the provider access token from the session */
                    $github->disconnect();

                    if(is_null($email)) {
                        session_unset_key('register_language');
                        error_log('[Social login][GitHub] The provider did not return a verified email address.');
                        Alerts::add_error(l('login.error_message.social_authentication'));
                        redirect('login' . $redirect_append);
                    }

                    $this->process_social_login($email, $name, $redirect, $method, $id);
                } catch (\Exception $exception) {
                    if(isset($github)) $github->disconnect();
                    session_unset_key('register_language');
                    error_log('[Social login][GitHub] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            elseif($method == 'github') {
                try {
                    (new \Hybridauth\Provider\GitHub($github_config))->disconnect();
                } catch (\Exception $exception) {
                    error_log('[Social login][GitHub] ' . (string) $exception);
                }
                session_unset_key('register_language');
                error_log('[Social login][GitHub] Callback error: ' . json_encode(['error' => $_GET['error'] ?? null, 'error_description' => $_GET['error_description'] ?? null]));
                Alerts::add_error(l('login.error_message.social_authentication'));
                redirect('login' . $redirect_append);
            }
        }

        /* Apple Login / Register */
        if(settings()->apple->is_enabled && in_array($method, ['apple-initiate', 'apple'])) {
            if($method == 'apple-initiate') {
                session_set('register_language', \Altum\Language::$name);
                session_set('apple_nonce', bin2hex(random_bytes(32)));

                /* Apple returns with a cross-site POST, so this session cookie must be sent with it */
                $session_cookie_params = session_get_cookie_params();
                setcookie(session_name(), session_id(), [
                    'expires' => $session_cookie_params['lifetime'] ? time() + $session_cookie_params['lifetime'] : 0,
                    'path' => $session_cookie_params['path'],
                    'domain' => $session_cookie_params['domain'],
                    'secure' => true,
                    'httponly' => $session_cookie_params['httponly'],
                    'samesite' => 'None',
                ]);
            }

            /* Restore the default session cookie policy after the Apple callback */
            if($method == 'apple') {
                $session_cookie_params = session_get_cookie_params();
                setcookie(session_name(), session_id(), [
                    'expires' => $session_cookie_params['lifetime'] ? time() + $session_cookie_params['lifetime'] : 0,
                    'path' => $session_cookie_params['path'],
                    'domain' => $session_cookie_params['domain'],
                    'secure' => $session_cookie_params['secure'],
                    'httponly' => $session_cookie_params['httponly'],
                    'samesite' => 'Lax',
                ]);
            }

            $apple_config = [
                'callback' => SITE_URL . 'login/apple',
                'keys' => [
                    'id' => settings()->apple->client_id,
                    'team_id' => settings()->apple->team_id,
                    'key_id' => settings()->apple->key_id,
                    'key_content' => settings()->apple->key_content,
                ],
                'scope' => 'name email',
                'authorize_url_parameters' => [
                    'nonce' => session_get('apple_nonce'),
                ],
            ];

            if($method == 'apple-initiate') {
                try {
                    $apple = new \Hybridauth\Provider\Apple($apple_config);
                    $apple->disconnect();
                    $apple->authenticate();
                } catch (\Exception $exception) {
                    if(isset($apple)) $apple->disconnect();
                    session_unset_key('apple_nonce');
                    session_unset_key('register_language');
                    error_log('[Social login][Apple] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            if($method == 'apple' && isset($_POST['error'])) {
                try {
                    (new \Hybridauth\Provider\Apple($apple_config))->disconnect();
                } catch (\Exception $exception) {
                    error_log('[Social login][Apple] ' . (string) $exception);
                }
                session_unset_key('apple_nonce');
                session_unset_key('register_language');
                error_log('[Social login][Apple] Callback error: ' . json_encode(['error' => $_POST['error'] ?? null, 'error_description' => $_POST['error_description'] ?? null]));
                Alerts::add_error(l('login.error_message.social_authentication'));
                redirect('login' . $redirect_append);
            }

            if($method == 'apple' && isset($_POST['code'])) {
                try {
                    $apple = new \Hybridauth\Provider\Apple($apple_config);
                    $apple->authenticate();
                    $apple_account_info = $apple->getUserProfile();
                    $email = $apple_account_info->email;
                    $id = $apple_account_info->identifier;

                    /* Validate the identity token claims after Hybridauth validates its signature */
                    $apple_tokens = $apple->getAccessToken();

                    /* Clear out the provider access token from the session */
                    $apple->disconnect();

                    if(is_null($email)) {
                        session_unset_key('apple_nonce');
                        session_unset_key('register_language');
                        error_log('[Social login][Apple] The provider did not return an email address.');
                        Alerts::add_error(l('login.error_message.social_authentication'));
                        redirect('login' . $redirect_append);
                    }

                    $apple_id_token_parts = explode('.', $apple_tokens['id_token'] ?? '');

                    if(count($apple_id_token_parts) != 3) {
                        throw new \Exception(l('login.error_message.social_authentication'));
                    }

                    $apple_id_token_payload = $apple_id_token_parts[1];
                    $apple_id_token_payload .= str_repeat('=', (4 - strlen($apple_id_token_payload) % 4) % 4);
                    $apple_id_token_payload = json_decode(base64_decode(strtr($apple_id_token_payload, '-_', '+/'), true));
                    $apple_audience_is_valid = isset($apple_id_token_payload->aud) && (
                        $apple_id_token_payload->aud === settings()->apple->client_id
                        || (is_array($apple_id_token_payload->aud) && in_array(settings()->apple->client_id, $apple_id_token_payload->aud, true))
                    );

                    if(
                        !$apple_id_token_payload
                        || ($apple_id_token_payload->iss ?? null) !== 'https://appleid.apple.com'
                        || !$apple_audience_is_valid
                        || ($apple_id_token_payload->sub ?? null) !== $id
                        || ($apple_id_token_payload->email ?? null) !== $email
                        || !filter_var($apple_id_token_payload->email_verified ?? false, FILTER_VALIDATE_BOOLEAN)
                        || !hash_equals(session_get('apple_nonce') ?? '', $apple_id_token_payload->nonce ?? '')
                    ) {
                        throw new \Exception(l('login.error_message.social_authentication'));
                    }

                    $name = $apple_account_info->displayName ?: strtok($email, '@');
                    session_unset_key('apple_nonce');

                    $this->process_social_login($email, $name, $redirect, $method, $id);
                } catch (\Exception $exception) {
                    if(isset($apple)) $apple->disconnect();
                    session_unset_key('apple_nonce');
                    session_unset_key('register_language');
                    error_log('[Social login][Apple] ' . (string) $exception);
                    Alerts::add_error(l('login.error_message.social_authentication'));
                    redirect('login' . $redirect_append);
                }
            }

            if($method == 'apple' && !isset($_POST['code'], $_POST['error'])) {
                try {
                    (new \Hybridauth\Provider\Apple($apple_config))->disconnect();
                } catch (\Exception $exception) {
                    error_log('[Social login][Apple] ' . (string) $exception);
                }
                session_unset_key('apple_nonce');
                session_unset_key('register_language');
                error_log('[Social login][Apple] The callback did not return an authorization code.');
                Alerts::add_error(l('login.error_message.social_authentication'));
                redirect('login' . $redirect_append);
            }
        }

        if(!empty($_POST) && $method == 'social-twofa') {
            $_POST['twofa_token'] = isset($_POST['twofa_token']) ? input_clean(str_replace(' ', '', $_POST['twofa_token'] ?? ''), 64) : null;

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!$_POST['twofa_token']) {
                Alerts::add_field_error('twofa_token', l('global.error_message.empty_field'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $twofa = new \RobThree\Auth\TwoFactorAuth(new \RobThree\Auth\Providers\Qr\BaconQrCodeProvider(format: 'svg'), settings()->main->title, 6, 30);
                $twofa_check = $twofa->verifyCode($user->twofa_secret, $_POST['twofa_token']);

                if(!$twofa_check) {
                    Alerts::add_field_error('twofa_token', l('login.error_message.twofa_token'));
                }
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                session_regenerate_id(true);
                session_set('user_id', $user->user_id);
                session_set('user_password_hash', md5($user->password));

                session_unset_key('social_twofa_user_id');
                session_unset_key('social_twofa_method');
                session_unset_key('social_twofa_expiration');

                (new User())->login_aftermath_update($user->user_id, $social_twofa_method);

                Alerts::add_info(sprintf(l('login.info_message.logged_in'), $user->name));

                session_unset_key('redirect');

                /* Check to see if the user has a custom language set */
                if(\Altum\Language::$name == $user->language) {
                    redirect($redirect);
                } else {
                    redirect((\Altum\Language::$active_languages[$user->language] ? \Altum\Language::$active_languages[$user->language] . '/' : null) . $redirect, true);
                }
            }
        }

        elseif(!empty($_POST)) {
            /* Clean email and encrypt the password */
            $_POST['email'] = input_clean_email($_POST['email'] ?? '');
            $_POST['twofa_token'] = isset($_POST['twofa_token']) ? input_clean(str_replace(' ', '', $_POST['twofa_token'] ?? ''), 64) : null;
            $values['email'] = $_POST['email'];
            $values['password'] = $_POST['password'] = mb_substr($_POST['password'], 0, 64);

            /* Check for any errors */
            $required_fields = ['email', 'password'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(settings()->captcha->login_is_enabled && !session_has('twofa_required') && !$captcha->is_valid()) {
                Alerts::add_field_error('captcha', l('global.error_message.invalid_captcha'));
            }

            /* Make sure to check against the limiter */
            if(settings()->users->login_lockout_is_enabled) {
                $minutes_ago_datetime = (new \DateTime())->modify('-' . settings()->users->login_lockout_time . ' minutes')->format('Y-m-d H:i:s');

                $recent_fails = db()->where('ip', get_ip())->where('type', 'login.wrong_password')->where('datetime', $minutes_ago_datetime, '>=')->getValue('users_logs', 'COUNT(*)');

                if($recent_fails >= settings()->users->login_lockout_max_retries) {
                    Alerts::add_error(sprintf(l('global.error_message.limit_try_again'), settings()->users->login_lockout_time, l('global.date.minutes')));
                    setcookie('login_lockout', 'true', time()+60*settings()->users->login_lockout_time, COOKIE_PATH);
                    $_COOKIE['login_lockout'] = 'true';
                }
            }

            /* Try to get the user from the database */
            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $user = db()->where('email', $_POST['email'])->getOne('users', ['user_id', 'email', 'name', 'status', 'password', 'token_code', 'twofa_secret', 'language']);

                if(!$user) {
                    Alerts::add_error(l('login.error_message.wrong_login_credentials'));
                } else {

                    if($user->status != 1) {
                        Alerts::add_error(l('login.error_message.user_not_active'));
                    } else

                        if(!password_verify($_POST['password'], $user->password)) {
                            Logger::users($user->user_id, 'login.wrong_password');

                            Alerts::add_error(l('login.error_message.wrong_login_credentials'));
                        }

                }
            }

            /* Check if the user has Two-factor Authentication enabled */
            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                if($user && $user->twofa_secret) {
                    session_set('twofa_required', 1);

                    if($_POST['twofa_token']) {
                        $twofa = new \RobThree\Auth\TwoFactorAuth(new \RobThree\Auth\Providers\Qr\BaconQrCodeProvider(format: 'svg'), settings()->main->title, 6, 30);
                        $twofa_check = $twofa->verifyCode($user->twofa_secret, $_POST['twofa_token']);

                        if(!$twofa_check) {
                            Alerts::add_field_error('twofa_token', l('login.error_message.twofa_token'));
                        }
                    } else {
                        Alerts::add_info(l('login.info_message.twofa_token'));
                    }
                }
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors() && !Alerts::has_infos()) {

                /* If remember me is checked, log the user with cookies for X days else, remember just with a session */
                if(isset($_POST['rememberme'])) {
                    $token_code = $user->token_code;

                    /* Generate a new token */
                    if(empty($user->token_code)) {
                        $token_code = md5(uniqid('', true) . random_bytes(16));

                        db()->where('user_id', $user->user_id)->update('users', ['token_code' => $token_code]);
                    }

                    setcookie('user_id', $user->user_id, time()+60*60*24* (settings()->users->login_rememberme_cookie_days ?? 30), COOKIE_PATH);
                    setcookie('token_code', $token_code, time()+60*60*24* (settings()->users->login_rememberme_cookie_days ?? 30), COOKIE_PATH);
                    setcookie('user_password_hash', md5($user->password), time()+60*60*24* (settings()->users->login_rememberme_cookie_days ?? 30), COOKIE_PATH);

                } else {
                    session_regenerate_id(true);
                session_set('user_id', $user->user_id);
                    session_set('user_password_hash', md5($user->password));
                }

                session_unset_key('twofa_required');

                (new User())->login_aftermath_update($user->user_id);

                Alerts::add_info(sprintf(l('login.info_message.logged_in'), $user->name));

                session_unset_key('redirect');

                /* Check to see if the user has a custom language set */
                if(\Altum\Language::$name == $user->language) {
                    redirect($redirect);
                } else {
                    redirect((\Altum\Language::$active_languages[$user->language] ? \Altum\Language::$active_languages[$user->language] . '/' : null) . $redirect, true);
                }
            }
        }

        if(empty($_POST)) {
            session_unset_key('twofa_required');
        }

        /* Prepare the view */
        $data = [
            'captcha' => $captcha,
            'values' => $values,
            'redirect_append' => $redirect_append,
            'user' => $user ?? null
        ];

        $view = new \Altum\View('login/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    /* After a successful social login auth, register or login the user */
    private function process_social_login($email, $name, $redirect, $method, $id = null) {

        /* Clear session variable */
        if(session_has('register_language')) {
            \Altum\Language::set_by_name(session_get('register_language'));
            session_unset_key('register_language');
        }

        /* Clean the values returned by the social provider */
        $email = input_clean_email($email ?? '');
        $name = input_clean_name($name ?? '', 64);

        /* Make sure the domain is not blacklisted */
        $email_domain = get_domain_from_email($email);
        if(settings()->users->blacklisted_domains && in_array($email_domain, settings()->users->blacklisted_domains)) {
            Alerts::add_field_error('email', l('register.error_message.blacklisted_domain'));
        }

        /* Email shield plugin */
        if(
            \Altum\Plugin::is_active('email-shield')
            && settings()->email_shield->is_enabled
            && !in_array($email_domain, settings()->email_shield->whitelisted_domains ?? [])
            && !\Altum\Plugin\EmailShield::validate($email_domain)
        ) {
            Alerts::add_field_error('email', l('register.error_message.blacklisted_domain'));
        }

        /* Detect the location */
        try {
            $maxmind = (get_maxmind_reader_country())->get(get_ip());
        } catch(\Exception $exception) { /* :) */ }
        $country = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;

        /* Make sure the country is not blacklisted */
        if($country && in_array($country, settings()->users->blacklisted_countries ?? [])) {
            Alerts::add_error(l('register.error_message.blacklisted_country'));
        }

        /* Make sure the IP is not blacklisted */
        if(in_array(get_ip(), settings()->users->blacklisted_ips ?? [])) {
            Alerts::add_error(l('register.error_message.blacklisted_country'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
            /* If the user is already in the system, log him in */
            if($user = db()->where('email', $email)->getOne('users', ['user_id', 'email', 'password', 'lost_password_code', 'language', 'name', 'status', 'twofa_secret', 'extra'])) {

                /* Make sure the account is not disabled */
                if($user->status == 2) {
                    Alerts::add_error(l('login.error_message.user_not_active'));
                    redirect('login');
                }

                /* Make sure the account has the id matching in case its required */
                if($id && $method == 'microsoft') {
                    $user->extra = json_decode($user->extra ?? '');
                    if($id != $user->extra->initial_social_id) {
                        throw new \Exception(l('login.error_message.email_is_null'));
                    }
                }

                /* Make sure the account is activated / confirmed */
                if($user->status == 0) {
                    db()->where('user_id', $user->user_id)->update('users', ['status' => 1]);
                    $user->status = 1;
                }

                /* Make sure the user has a password set before letting the user login */
                (new User())->verify_null_password($user->user_id, $user->email, $user->password);

                /* Verify Two-factor Authentication before logging the user in */
                if($user->twofa_secret) {
                    session_set('social_twofa_user_id', $user->user_id);
                    session_set('social_twofa_method', $method);
                    session_set('social_twofa_expiration', time() + 60 * 10);

                    Alerts::add_info(l('login.info_message.twofa_token'));
                    redirect('login/social-twofa');
                }

                session_regenerate_id(true);
                session_set('user_id', $user->user_id);
                session_set('user_password_hash', md5($user->password));

                (new User())->login_aftermath_update($user->user_id, $method);

                Alerts::add_info(sprintf(l('login.info_message.logged_in'), $user->name));

                session_unset_key('redirect');

                /* Check to see if the user has a custom language set */
                if(\Altum\Language::$name == $user->language) {
                    redirect($redirect);
                } else {
                    redirect((\Altum\Language::$active_languages[$user->language] ? \Altum\Language::$active_languages[$user->language] . '/' : null) . $redirect, true);
                }
            }

            /* Create a new account */
            else {
                /* Make sure registrations are not closed */
                if(!settings()->users->register_is_enabled) {
                    Alerts::add_error(l('register.error_message.closed'));
                    redirect('login');
                }

                \Altum\CustomHooks::user_initiate_registration();

                /* Validate the values returned by the social provider */
                if(mb_strlen(trim($name)) < 1 || mb_strlen($name) > 64) {
                    Alerts::add_error(l('register.error_message.name_length'));
                }
                if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Alerts::add_field_error('email', l('global.error_message.invalid_email'));
                }
                if(!settings()->users->email_aliases_is_enabled && str_contains($email, '+')) {
                    Alerts::add_field_error('email', l('register.error_message.email_aliases_not_allowed'));
                }

                /* Make sure to check against the limiter */
                if(settings()->users->register_lockout_is_enabled) {
                    $days_ago_datetime = (new \DateTime())->modify('-' . settings()->users->register_lockout_time . ' days')->format('Y-m-d H:i:s');

                    $recent_registrations = db()->where('ip', get_ip())->where('type', 'register%.success', 'LIKE')->where('datetime', $days_ago_datetime, '>=')->getValue('users_logs', 'COUNT(*)');

                    if($recent_registrations >= settings()->users->register_lockout_max_registrations) {
                        Alerts::add_error(sprintf(l('global.error_message.limit_try_again'), settings()->users->register_lockout_time, l('global.date.days')));
                        setcookie('register_lockout', 'true', time()+60*60*24*settings()->users->register_lockout_time, COOKIE_PATH);
                        $_COOKIE['register_lockout'] = 'true';
                    }
                }

                if(Alerts::has_field_errors() || Alerts::has_errors()) {
                    redirect('login');
                }

                /* Determine what plan is set by default */
                $plan_id = 'free';
                $plan_settings = json_encode(settings()->plan_free->settings ?? '');
                $plan_expiration_date = get_date();
                $lost_password_code = md5(uniqid('', true) . random_bytes(16));
                $password = settings()->users->register_social_login_require_password ? null : md5(uniqid('', true) . random_bytes(16));

                $registered_user = (new User())->create(
                    $email,
                    $password,
                    $name,
                    1,
                    $method,
                    null,
                    $lost_password_code,
                    false,
                    $plan_id,
                    $plan_settings,
                    $plan_expiration_date,
                    settings()->main->default_timezone,
                    [
                        'initial_social_method' => $method,
                        'initial_social_id' => $id,
                    ]
                );

                /* Log the action */
                Logger::users($registered_user['user_id'], 'register.' . $method . '.success');

                /* Send a welcome email if needed */
                if(settings()->users->welcome_email_is_enabled) {

                    $email_template = get_email_template(
                        [],
                        l('global.emails.user_welcome.subject'),
                        [
                            '{{NAME}}' => $name,
                            '{{URL}}' => url(),
                            '{{DASHBOARD_LINK}}' => url('dashboard'),
                        ],
                        l('global.emails.user_welcome.body')
                    );

                    send_mail($email, $email_template->subject, $email_template->body);

                }

                /* Send notification to admin if needed */
                if(settings()->email_notifications->new_user && !empty(settings()->email_notifications->emails)) {

                    $email_template = get_email_template(
                        [],
                        l('global.emails.admin_new_user_notification.subject'),
                        [
                            '{{NAME}}' => $name,
                            '{{EMAIL}}' => $email,
                            '{{SOURCE}}' => $registered_user['source'],
                            '{{IP}}' => $registered_user['ip'],
                            '{{COUNTRY_NAME}}' => $registered_user['country'] ? get_country_from_country_code($registered_user['country']) : l('global.unknown'),
                            '{{CITY_NAME}}' => $registered_user['city_name'] ?? l('global.unknown'),
                            '{{DEVICE_TYPE}}' => l('global.device.' . $registered_user['device_type']),
                            '{{OS_NAME}}' => $registered_user['os_name'],
                            '{{BROWSER_NAME}}' => $registered_user['browser_name'],
                            '{{USER_LINK}}' => url('admin/user-view/' . $registered_user['user_id']),
                        ],
                        l('global.emails.admin_new_user_notification.body')
                    );

                    send_mail(explode(',', settings()->email_notifications->emails), $email_template->subject, $email_template->body);

                }

                /* Send webhook notification if needed */
                if(settings()->webhooks->user_new) {
                    fire_and_forget('post', settings()->webhooks->user_new, [
                        'user_id' => $registered_user['user_id'],
                        'email' => $email,
                        'name' => $name,
                        'source' => $method,
                        'is_newsletter_subscribed' => false,
                        'datetime' => get_date()
                    ], signature: true);
                }

                /* Send internal notification if needed */
                if(settings()->internal_notifications->admins_is_enabled && settings()->internal_notifications->new_user) {
                    db()->insert('internal_notifications', [
                        'for_who' => 'admin',
                        'from_who' => 'system',
                        'icon' => 'fas fa-user',
                        'title' => l('global.notifications.new_user.title'),
                        'description' => sprintf(l('global.notifications.new_user.description'), $name, $email),
                        'url' => 'admin/user-view/' . $registered_user['user_id'],
                        'datetime' => get_date(),
                    ]);
                }

                if($password) {
                    /* Login the user */
                    session_regenerate_id(true);
                session_set('user_id', $registered_user['user_id']);
                    session_set('user_password_hash', md5($registered_user['password']));

                    (new User())->login_aftermath_update($registered_user['user_id'], $method);

                    /* Set a nice success message */
                    Alerts::add_success(l('register.success_message.login'));

                    $redirect = append_query_param($redirect, 'welcome=' . $registered_user['user_id']);
                    session_unset_key('redirect');

                    redirect($redirect);
                } else {
                    /* Redirect the newly created user to set a new password */
                    redirect('reset-password/' . md5($email) . '/' . $lost_password_code . '?redirect=' . $redirect . '&welcome=' . $registered_user['user_id']);
                }
            }
        }
    }

}
