@extends('layouts.app')

@section('site_title', formatTitle([__('Authentication'), __('Settings'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Settings')],
            ]])

            <h1 class="h2 mb-3 d-inline-block">{{ __('Authentication') }}</h1>

            <div class="card border-0 shadow-sm">
                <div class="card-header"><div class="font-weight-medium py-1">{{ __('Authentication') }}</div></div>
                <div class="card-body">
                    @include('shared.message')

                    <form action="{{ route('admin.settings', 'authentication') }}" method="post" enctype="multipart/form-data">

                        @csrf

                        <div class="form-group">
                            <label for="i-registration">{{ __('Registration') }}</label>
                            <select name="registration" id="i-registration" class="custom-select{{ $errors->has('registration') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('registration') !== null && old('registration') == $key) || (config('settings.registration') == $key && old('registration') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('registration'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('registration') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">
                                {{ __('Enable or disable user registration on the website.') }} {{ __('When disabled, new users cannot create accounts.') }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="i-registration-require-email-verification">{{ __('Email verification') }}</label>
                            <select name="registration_require_email_verification" id="i-registration-require-email-verification" class="custom-select{{ $errors->has('registration_require_email_verification') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('registration_require_email_verification') !== null && old('registration_require_email_verification') == $key) || (config('settings.registration_require_email_verification') == $key && old('registration_require_email_verification') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('registration_require_email_verification'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('registration_require_email_verification') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">
                                {{ __('Require users to verify their email address before activating their account.') }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="i-login-tfa">{{ __('Two-factor authentication') }}</label>
                            <select name="login_tfa" id="i-login-tfa" class="custom-select{{ $errors->has('login_tfa') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('login_tfa') !== null && old('login_tfa') == $key) || (config('settings.login_tfa') == $key && old('login_tfa') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('login_tfa'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('login_tfa') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">
                                {{ __('Enable or disable the two-factor authentication (2FA) system on the website.') }} {{ __('When disabled, users will not be prompted for 2FA during login, regardless of their personal settings.') }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="i-registration-tfa" class="d-inline-flex align-items-center"><span class="{{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">{{ __('Two-factor authentication') }}</span><span class="badge badge-secondary">{{ __('Default') }}</span></label>
                            <select name="registration_tfa" id="i-registration-tfa" class="custom-select{{ $errors->has('registration_tfa') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('registration_tfa') !== null && old('registration_tfa') == $key) || (config('settings.registration_tfa') == $key && old('registration_tfa') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('registration_tfa'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('registration_tfa') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">
                                {{ __('The default two-factor authentication (2FA) status for new users upon registration.') }} {{ __('Users can later change this in their account settings.') }}
                            </small>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseGoogle" aria-expanded="{{ ($errors->has('auth_google') || $errors->has('auth_google_client_id') || $errors->has('auth_google_client_secret') ? 'true' : 'false') }}" aria-controls="collapseGoogle">
                                @include('icons.google', ['class' => 'width-4 height-4 fill-current ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) Google
                            </button>

                            <div class="collapse {{ ($errors->has('auth_google') || $errors->has('auth_google_client_id') || $errors->has('auth_google_client_secret') ? 'show' : '') }}" id="collapseGoogle">
                                <div class="alert alert-info mt-3">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => 'Google']) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#google" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="i-auth-google">Google</label>
                                    <select name="auth_google" id="i-auth-google" class="custom-select{{ $errors->has('auth_google') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('auth_google') !== null && old('auth_google') == $key) || (config('settings.auth_google') == $key && old('auth_google') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('auth_google'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('auth_google') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable authentication through :service.', ['service' => 'Google']) }} {{ __('When enabled, users can register or log in using their :service account.', ['service' => 'Google']) }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-auth-google-client-id">{{ __('Client ID') }}</label>
                                    <input type="text" name="auth_google_client_id" id="i-auth-google-client-id" class="form-control{{ $errors->has('auth_google_client_id') ? ' is-invalid' : '' }}" value="{{ old('auth_google_client_id') ?? config('settings.auth_google_client_id') }}">
                                    @if ($errors->has('auth_google_client_id'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('auth_google_client_id') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-auth-google-client-secret">{{ __('Client secret') }}</label>
                                    <input type="password" name="auth_google_client_secret" id="i-auth-google-client-secret" class="form-control{{ $errors->has('auth_google_client_secret') ? ' is-invalid' : '' }}" value="{{ old('auth_google_client_secret') ?? config('settings.auth_google_client_secret') }}">
                                    @if ($errors->has('auth_google_client_secret'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('auth_google_client_secret') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-google-callback-url">{{ __('Callback URL') }}</label>
                                    <div class="input-group">
                                        <input type="text" dir="ltr" name="google_callback_url" id="i-google-callback-url" class="form-control" value="{{ route('login.google') }}" readonly>
                                        <div class="input-group-append">
                                            <div class="btn btn-primary" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-google-callback-url">{{ __('Copy') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseMicrosoft" aria-expanded="{{ ($errors->has('auth_microsoft') || $errors->has('auth_microsoft_client_id') || $errors->has('auth_microsoft_client_secret') ? 'true' : 'false') }}" aria-controls="collapseMicrosoft">
                                @include('icons.microsoft', ['class' => 'width-4 height-4 fill-current ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) Microsoft
                            </button>

                            <div class="collapse {{ ($errors->has('auth_microsoft') || $errors->has('auth_microsoft_client_id') || $errors->has('auth_microsoft_client_secret') ? 'show' : '') }}" id="collapseMicrosoft">
                                <div class="alert alert-info mt-3">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => 'Microsoft']) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#microsoft" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="i-auth-microsoft">Microsoft</label>
                                    <select name="auth_microsoft" id="i-auth-microsoft" class="custom-select{{ $errors->has('auth_microsoft') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('auth_microsoft') !== null && old('auth_microsoft') == $key) || (config('settings.auth_microsoft') == $key && old('auth_microsoft') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('auth_microsoft'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('auth_microsoft') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable authentication through :service.', ['service' => 'Microsoft']) }} {{ __('When enabled, users can register or log in using their :service account.', ['service' => 'Microsoft']) }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-auth-microsoft-client-id">{{ __('Application (client) ID') }}</label>
                                    <input type="text" name="auth_microsoft_client_id" id="i-auth-microsoft-client-id" class="form-control{{ $errors->has('auth_microsoft_client_id') ? ' is-invalid' : '' }}" value="{{ old('auth_microsoft_client_id') ?? config('settings.auth_microsoft_client_id') }}">
                                    @if ($errors->has('auth_microsoft_client_id'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('auth_microsoft_client_id') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-auth-microsoft-client-secret">{{ __('Client secret value') }}</label>
                                    <input type="password" name="auth_microsoft_client_secret" id="i-auth-microsoft-client-secret" class="form-control{{ $errors->has('auth_microsoft_client_secret') ? ' is-invalid' : '' }}" value="{{ old('auth_microsoft_client_secret') ?? config('settings.auth_microsoft_client_secret') }}">
                                    @if ($errors->has('auth_microsoft_client_secret'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('auth_microsoft_client_secret') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-microsoft-callback-url">{{ __('Callback URL') }}</label>
                                    <div class="input-group">
                                        <input type="text" dir="ltr" name="microsoft_callback_url" id="i-microsoft-callback-url" class="form-control" value="{{ route('login.microsoft') }}" readonly>
                                        <div class="input-group-append">
                                            <div class="btn btn-primary" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-microsoft-callback-url">{{ __('Copy') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseApple" aria-expanded="{{ ($errors->has('auth_apple') || $errors->has('auth_apple_client_id') || $errors->has('auth_apple_team_id') || $errors->has('auth_apple_key_id') || $errors->has('auth_apple_private_key') ? 'true' : 'false') }}" aria-controls="collapseApple">
                                @include('icons.apple', ['class' => 'width-4 height-4 fill-current ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) Apple
                            </button>

                            <div class="collapse {{ ($errors->has('auth_apple') || $errors->has('auth_apple_client_id') || $errors->has('auth_apple_team_id') || $errors->has('auth_apple_key_id') || $errors->has('auth_apple_private_key') ? 'show' : '') }}" id="collapseApple">
                                <div class="alert alert-info mt-3">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => 'Apple']) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#apple" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="i-auth-apple">Apple</label>
                                    <select name="auth_apple" id="i-auth-apple" class="custom-select{{ $errors->has('auth_apple') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('auth_apple') !== null && old('auth_apple') == $key) || (config('settings.auth_apple') == $key && old('auth_apple') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('auth_apple'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('auth_apple') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable authentication through :service.', ['service' => 'Apple']) }} {{ __('When enabled, users can register or log in using their :service account.', ['service' => 'Apple']) }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-auth-apple-client-id">{{ __('Service ID identifier') }}</label>
                                    <input type="text" name="auth_apple_client_id" id="i-auth-apple-client-id" class="form-control{{ $errors->has('auth_apple_client_id') ? ' is-invalid' : '' }}" value="{{ old('auth_apple_client_id') ?? config('settings.auth_apple_client_id') }}">
                                    @if ($errors->has('auth_apple_client_id'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('auth_apple_client_id') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-auth-apple-team-id">{{ __('Team ID') }}</label>
                                    <input type="text" name="auth_apple_team_id" id="i-auth-apple-team-id" class="form-control{{ $errors->has('auth_apple_team_id') ? ' is-invalid' : '' }}" value="{{ old('auth_apple_team_id') ?? config('settings.auth_apple_team_id') }}">
                                    @if ($errors->has('auth_apple_team_id'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('auth_apple_team_id') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-auth-apple-key-id">{{ __('Key ID') }}</label>
                                    <input type="text" name="auth_apple_key_id" id="i-auth-apple-key-id" class="form-control{{ $errors->has('auth_apple_key_id') ? ' is-invalid' : '' }}" value="{{ old('auth_apple_key_id') ?? config('settings.auth_apple_key_id') }}">
                                    @if ($errors->has('auth_apple_key_id'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('auth_apple_key_id') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-auth-apple-private-key">{{ __('Private key') }}</label>
                                    <textarea name="auth_apple_private_key" id="i-auth-apple-private-key" class="form-control{{ $errors->has('auth_apple_private_key') ? ' is-invalid' : '' }}">{{ old('auth_apple_private_key') ?? config('settings.auth_apple_private_key') }}</textarea>
                                    @if ($errors->has('auth_apple_private_key'))
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $errors->first('auth_apple_private_key') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-apple-callback-url">{{ __('Return URL') }}</label>
                                    <div class="input-group">
                                        <input type="text" dir="ltr" name="apple_callback_url" id="i-apple-callback-url" class="form-control" value="{{ route('login.apple') }}" readonly>
                                        <div class="input-group-append">
                                            <div class="btn btn-primary" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-apple-callback-url">{{ __('Copy') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.admin')
