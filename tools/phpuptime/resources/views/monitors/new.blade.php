@extends('layouts.app')

@section('site_title', formatTitle([__('New'), __('Monitor'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            <div class="row">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                        ['url' => route('dashboard'), 'title' => __('Home')],
                        ['url' => route('monitors'), 'title' => __('Monitors')],
                        ['title' => __('New')],
                    ]])

                    <h1 class="h2 mb-3 d-inline-block">{{ __('New') }}</h1>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header">
                            <div class="row">
                                <div class="col">
                                    <div class="font-weight-medium py-1">{{ __('Monitor') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            <form action="{{ route('monitors.new') }}" method="post" enctype="multipart/form-data" autocomplete="off" id="form-monitor">
                                @csrf

                                <div class="form-group">
                                    <label for="i-name">{{ __('Name') }}</label>
                                    <input type="text" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" id="i-name" value="{{ old('name') }}">
                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-url">{{ __('URL') }}</label>
                                    <input type="text" dir="ltr" name="url" class="form-control{{ $errors->has('url') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" id="i-url" value="{{ old('url') }}" placeholder="https://example.com">
                                    @if ($errors->has('url'))
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $errors->first('url') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-interval">{{ __('Interval') }}</label>
                                    <select name="interval" id="i-interval" class="custom-select{{ $errors->has('interval') ? ' is-invalid' : '' }}">
                                        @foreach(config('intervals.http') as $key)
                                            <option value="{{ $key }}" @if(old('interval') == $key && old('interval') !== null) selected @endif @if(!in_array($key, Auth::user()->active_plan->features->monitor_intervals)) disabled @endif>{{ Carbon\CarbonInterval::seconds($key)->cascade()->forHumans() }} @if (!in_array($key, Auth::user()->active_plan->features->monitor_intervals)) ({{ __('Feature locked') }}) @endif</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('interval'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('interval') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <div class="d-flex align-items-center mb-2">
                                        <label for="i-ssl-alert-days" class="d-flex align-items-center mb-0">
                                            {{ __('SSL certificate monitoring') }}
                                        </label>
                                        @cannot('sslMonitoring', [App\Models\User::class])
                                            @if(enabledPaymentProcessors())
                                                <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}" class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.lock-open', ['class' => 'fill-current text-primary width-4 height-4'])</a>
                                            @endif
                                        @endcannot
                                    </div>
                                    <select name="ssl_alert_days" id="i-ssl-alert-days" class="custom-select{{ $errors->has('ssl_alert_days') ? ' is-invalid' : '' }}" @cannot('sslMonitoring', [App\Models\User::class]) disabled @endcannot>
                                        @foreach(config('intervals.ssl') as $key)
                                            <option value="{{ $key }}" @if(old('ssl_alert_days') == $key && old('ssl_alert_days') !== null) selected @endif>{{ ($key > 1 ? __('Alert :days days before', ['days' => $key]) : ($key == 1 ? __('Alert :day day before', ['day' => $key]) : __('Off'))) }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('ssl_alert_days'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('ssl_alert_days') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{{ __('The number of days before SSL certificate expiration to receive an alert.') }}</small>
                                </div>

                                <div class="form-group">
                                    <div class="d-flex align-items-center mb-2">
                                        <label for="i-domain-alert-days" class="d-flex align-items-center mb-0">
                                            {{ __('Domain name monitoring') }}
                                        </label>
                                        @cannot('domainMonitoring', [App\Models\User::class])
                                            @if(enabledPaymentProcessors())
                                                <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}" class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.lock-open', ['class' => 'fill-current text-primary width-4 height-4'])</a>
                                            @endif
                                        @endcannot
                                    </div>
                                    <select name="domain_alert_days" id="i-domain-alert-days" class="custom-select{{ $errors->has('domain_alert_days') ? ' is-invalid' : '' }}" @cannot('domainMonitoring', [App\Models\User::class]) disabled @endcannot>
                                        @foreach(config('intervals.domain') as $key)
                                            <option value="{{ $key }}" @if(old('domain_alert_days') == $key && old('domain_alert_days') !== null) selected @endif>{{ ($key > 1 ? __('Alert :days days before', ['days' => $key]) : ($key == 1 ? __('Alert :day day before', ['day' => $key]) : __('Off'))) }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('domain_alert_days'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('domain_alert_days') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{{ __('The number of days before domain name expiration to receive an alert.') }}</small>
                                </div>

                                <div class="form-row">
                                    <div class="col-12 col-lg">
                                        <div class="form-group">
                                            <label for="i-alert-condition">{{ __('Alert condition') }}</label>
                                            <select name="alert_condition" id="i-alert-condition" class="custom-select{{ $errors->has('alert_condition') ? ' is-invalid' : '' }}">
                                                @foreach(config('alert.conditions') as $key => $value)
                                                    <option value="{{ $key }}" @if(old('alert_condition') == $key && old('alert_condition') !== null) selected @endif>{{ __($value) }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('alert_condition'))
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $errors->first('alert_condition') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-12 col-lg {{ (old('alert_condition') === null || old('alert_condition') == 'url_unavailable' ? 'd-none' : '') }}" id="alert-text-lookup">
                                        <div class="form-group">
                                            <label for="i-alert-text-lookup">{{ __('Text lookup') }}</label>
                                            <input type="text" name="alert_text_lookup" class="form-control{{ $errors->has('alert_text_lookup') ? ' is-invalid' : '' }}" id="i-alert-text-lookup" value="{{ old('alert_text_lookup') }}">
                                            @if ($errors->has('alert_text_lookup'))
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $errors->first('alert_text_lookup') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="i-alerts">{{ __('Alert channels') }}</label>

                                    @if ($errors->has('alerts'))
                                        <span class="invalid-feedback d-block mt-0 mb-2" role="alert">
                                            <strong>{{ $errors->first('alerts') }}</strong>
                                        </span>
                                    @endif

                                    <div data-inputs-container="alerts">
                                        <input name="alerts[empty][key]" type="hidden" disabled>
                                        <input name="alerts[empty][value]" type="hidden" disabled>

                                        <div class="d-none">
                                            <div data-alert="" data-placeholder=""></div>
                                            <div data-alert="email" data-placeholder="example@example.com"></div>
                                            <div data-alert="slack" data-placeholder="{{ __('Webhook URL') }}">{!! __('Learn more at :url.', ['url' => '<a href="https://api.slack.com/messaging/webhooks" target="_blank" rel="nofollow noreferrer noopener">Sending messages using incoming webhooks - Slack</a>']) !!}</div>
                                            <div data-alert="teams" data-placeholder="{{ __('Webhook URL') }}">{!! __('Learn more at :url.', ['url' => '<a href="https://learn.microsoft.com/en-us/microsoftteams/platform/webhooks-and-connectors/how-to/add-incoming-webhook" target="_blank" rel="nofollow noreferrer noopener">Create Incoming Webhooks - Microsoft Teams</a>']) !!}</div>
                                            <div data-alert="discord" data-placeholder="{{ __('Webhook URL') }}">{!! __('Learn more at :url.', ['url' => '<a href="https://support.discord.com/hc/en-us/articles/228383668-Intro-to-Webhooks" target="_blank" rel="nofollow noreferrer noopener">Intro to Webhooks - Discord</a>']) !!}</div>
                                            <div data-alert="flock" data-placeholder="{{ __('Webhook URL') }}">{!! __('Learn more at :url.', ['url' => '<a href="https://support.flock.com/hc/en-us/articles/360006943354-Incoming-webhooks" target="_blank" rel="nofollow noreferrer noopener">Incoming webhooks - Flock</a>']) !!}</div>
                                            <div data-alert="webhook" data-placeholder="{{ __('Webhook URL') }}"></div>
                                            <div data-alert="telegram" data-placeholder="{{ __('api:token chat_id') }}">{!! __(':api_token and :chat_id must be separated by a space.', ['api_token' => '<code>api:token</code>', 'chat_id' => '<code>chat_id</code>']) !!} {!! __('Learn more at :url.', ['url' => '<a href="https://core.telegram.org/bots/api" target="_blank" rel="nofollow noreferrer noopener">Telegram Bot API</a>']) !!}</div>
                                            <div data-alert="sms" data-placeholder="{{ __('+10000000000') }}"></div>
                                        </div>

                                        <div class="form-row form-group d-none" data-inputs-template data-inputs-group>
                                            <div class="col">
                                                <select data-input="key" class="custom-select" disabled>
                                                    <option value="" selected>{{ __('Channel') }}</option>
                                                    @foreach(config('alert.channels') as $key => $value)
                                                        @if ($key != 'sms' || $key == 'sms' && config('settings.twilio'))
                                                            <option value="{{ $key }}" @if ((Auth::user()->cannot('smsAlerts', [App\Models\User::class]) && $key == 'sms') || (Auth::user()->cannot('emailAlerts', [App\Models\User::class]) && $key == 'email') || (Auth::user()->cannot('webhookAlerts', [App\Models\User::class]) && $key == in_array($key, ['slack', 'teams', 'discord', 'flock', 'telegram', 'webhook']))) disabled @endif>{{ __($value) }} @if ((Auth::user()->cannot('smsAlerts', [App\Models\User::class]) && $key == 'sms') || (Auth::user()->cannot('emailAlerts', [App\Models\User::class]) && $key == 'email') || (Auth::user()->cannot('webhookAlerts', [App\Models\User::class]) && $key == in_array($key, ['slack', 'teams', 'discord', 'flock', 'telegram', 'webhook']))) ({{ __('Feature locked') }}) @endif</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col">
                                                <input type="text" dir="ltr" data-input="value" class="form-control" autocapitalize="none" spellcheck="false" value="" disabled>
                                                <small class="form-text text-muted d-none" data-help-text></small>
                                            </div>

                                            <div class="col-auto d-flex align-items-start">
                                                <button type="button" class="btn btn-outline-danger d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'width-4 height-4 fill-current'])&#8203;</button>
                                            </div>
                                        </div>

                                        <div data-inputs>
                                            @if(old('alerts'))
                                                @foreach(old('alerts') as $id => $alerts)
                                                    <div class="form-row form-group" data-inputs-group>
                                                        <div class="col">
                                                            <select name="alerts[{{ $id }}][key]" data-input="key" class="custom-select{{ $errors->has('alerts.'.$id.'.key') ? ' is-invalid' : '' }}">
                                                                <option value="">{{ __('Channel') }}</option>
                                                                @foreach(config('alert.channels') as $key => $value)
                                                                    <option value="{{ $key }}" @if($alerts['key'] == $key) selected @endif>{{ $value }}</option>
                                                                @endforeach
                                                            </select>
                                                            @if ($errors->has('alerts.'.$id.'.key'))
                                                                <span class="invalid-feedback d-block" role="alert">
                                                                    <strong>{{ $errors->first('alerts.'.$id.'.key') }}</strong>
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <div class="col">
                                                            <input type="text" dir="ltr" name="alerts[{{ $id }}][value]" data-input="value" class="form-control{{ $errors->has('alerts.'.$id.'.value') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" value="{{ $alerts['value'] }}" placeholder="{{ ($alerts['key'] ? ($alerts['key'] == 'email' ? 'example@example.com' : ($alerts['key'] == 'sms' ? '+10000000000' : __('Webhook URL'))) : '') }}">
                                                            @if ($errors->has('alerts.'.$id.'.value'))
                                                                <span class="invalid-feedback d-block" role="alert">
                                                                    <strong>{{ $errors->first('alerts.'.$id.'.value') }}</strong>
                                                                </span>
                                                            @endif

                                                            <small class="form-text text-muted d-none" data-help-text></small>
                                                        </div>

                                                        <div class="col-auto d-flex align-items-start">
                                                            <button type="button" class="btn btn-outline-danger d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'width-4 height-4 fill-current'])&#8203;</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>

                                        <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center" data-inputs-add>@include('icons.add', ['class' => 'width-4 height-4 fill-current'])&#8203;</button>
                                    </div>
                                </div>

                                <div class="pb-3">
                                    <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseRequest" aria-expanded="{{ ($errors->has('request_method') || $errors->has('request_auth_username') || $errors->has('request_auth_password') || $errors->has('request_headers') || $errors->has('request_headers.*.key') || $errors->has('request_headers.*.value') || $errors->has('cache_buster') ? 'true' : 'false') }}" aria-controls="collapseRequest">
                                        @include('icons.adjust', ['class' => 'width-4 height-4 fill-current ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) {{ __('Request') }}
                                    </button>

                                    <div class="collapse {{ ($errors->has('request_method') || $errors->has('request_auth_username') || $errors->has('request_auth_password') || $errors->has('request_headers') || $errors->has('request_headers.*.key') || $errors->has('request_headers.*.value') || $errors->has('cache_buster') ? 'show' : '') }}" id="collapseRequest">
                                        <div class="form-group mt-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <label for="i-request-method" class="d-flex align-items-center mb-0">
                                                    {{ __('Method') }}
                                                </label>
                                            </div>
                                            <select name="request_method" id="i-request-method" class="custom-select{{ $errors->has('request_method') ? ' is-invalid' : '' }}">
                                                @foreach(config('request.methods') as $key)
                                                    <option value="{{ $key }}" @if(old('request_method') == $key && old('request_method') !== null) selected @endif>{{ $key }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('request_method'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('request_method') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mb-3">
                                            <label for="i-request-headers">{{ __('Headers') }}</label>

                                            @if ($errors->has('request_headers'))
                                                <span class="invalid-feedback d-block mt-0 mb-2" role="alert">
                                                    <strong>{{ $errors->first('request_headers') }}</strong>
                                                </span>
                                            @endif

                                            <div data-inputs-container="request_headers">
                                                <input name="request_headers[empty][key]" type="hidden" disabled>
                                                <input name="request_headers[empty][value]" type="hidden" disabled>

                                                <div class="form-row form-group d-none" data-inputs-template data-inputs-group>
                                                    <div class="col">
                                                        <input type="text" data-input="key" class="form-control" autocapitalize="none" spellcheck="false" value="" placeholder="{{ __('Name') }}" disabled>
                                                    </div>

                                                    <div class="col">
                                                        <input type="text" data-input="value" class="form-control" autocapitalize="none" spellcheck="false" value="" placeholder="{{ __('Value') }}" disabled>
                                                    </div>

                                                    <div class="col-auto d-flex align-items-start">
                                                        <button type="button" class="btn btn-outline-danger d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'width-4 height-4 fill-current'])&#8203;</button>
                                                    </div>
                                                </div>

                                                <div data-inputs>
                                                    @if(old('request_headers'))
                                                        @foreach(old('request_headers') as $id => $requestHeaders)
                                                            <div class="form-row form-group" data-inputs-group>
                                                                <div class="col">
                                                                    <input type="text" name="request_headers[{{ $id }}][key]" data-input="key" class="form-control{{ $errors->has('request_headers.'.$id.'.key') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" value="{{ $requestHeaders['key'] }}">
                                                                    @if ($errors->has('request_headers.'.$id.'.key'))
                                                                        <span class="invalid-feedback d-block" role="alert">
                                                                            <strong>{{ $errors->first('request_headers.'.$id.'.key') }}</strong>
                                                                        </span>
                                                                    @endif
                                                                </div>

                                                                <div class="col">
                                                                    <input type="text" name="request_headers[{{ $id }}][value]" data-input="value" class="form-control{{ $errors->has('request_headers.'.$id.'.value') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" value="{{ $requestHeaders['value'] }}">
                                                                    @if ($errors->has('request_headers.'.$id.'.value'))
                                                                        <span class="invalid-feedback d-block" role="alert">
                                                                            <strong>{{ $errors->first('request_headers.'.$id.'.value') }}</strong>
                                                                        </span>
                                                                    @endif
                                                                </div>

                                                                <div class="col-auto d-flex align-items-start">
                                                                    <button type="button" class="btn btn-outline-danger d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'width-4 height-4 fill-current'])&#8203;</button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>

                                                <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center" data-inputs-add>@include('icons.add', ['class' => 'width-4 height-4 fill-current'])&#8203;</button>
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div class="col-12"><label for="i-request-auth-username">{{ __('HTTP authentication') }}</label></div>
                                            <div class="col-12 col-lg">
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <label for="i-request-auth-username" class="input-group-text">{{ __('Username') }}</label>
                                                        </div>
                                                        <input type="text" name="request_auth_username" class="form-control{{ $errors->has('request_auth_username') ? ' is-invalid' : '' }}" id="i-request-auth-username" autocapitalize="none" spellcheck="false" value="{{ old('request_auth_username') }}">
                                                    </div>
                                                    @if ($errors->has('request_auth_username'))
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $errors->first('request_auth_username') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg">
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <label for="i-request-auth-password" class="input-group-text">{{ __('Password') }}</label>
                                                        </div>
                                                        <input type="password" name="request_auth_password" class="form-control{{ $errors->has('request_auth_password') ? ' is-invalid' : '' }}" id="i-request-auth-password" autocapitalize="none" spellcheck="false" autocomplete="new-password" value="{{ old('request_auth_password') }}">
                                                        <div class="input-group-append">
                                                            <div class="input-group-text cursor-pointer" data-tooltip="true" data-title="{{ __('Show password') }}" data-password="i-request-auth-password" data-password-show="{{ __('Show password') }}" data-password-hide="{{ __('Hide password') }}">@include('icons.visibility_off', ['class' => 'width-4 height-4 fill-current text-muted'])@include('icons.visibility', ['class' => 'width-4 height-4 fill-current text-muted d-none'])</div>
                                                        </div>
                                                    </div>
                                                    @if ($errors->has('request_auth_password'))
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $errors->first('request_auth_password') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="i-cache-buster">{{ __('Cache buster') }}</label>
                                            <select name="cache_buster" id="i-cache-buster" class="custom-select{{ $errors->has('cache_buster') ? ' is-invalid' : '' }}">
                                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                                    <option value="{{ $key }}" @if(old('cache_buster') == $key && old('cache_buster') !== null) selected @endif>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('cache_buster'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('cache_buster') }}</strong>
                                                </span>
                                            @endif
                                            <small class="form-text text-muted">{{ __('Appends a unique string at the end of the URL so that every request is unique.') }}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="pb-3">
                                    <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseMaintenance" aria-expanded="{{ ($errors->has('maintenance_start_at') || $errors->has('maintenance_end_at') || $errors->has('meta_description') ? 'true' : 'false') }}" aria-controls="collapseMaintenance">
                                        @include('icons.date-range', ['class' => 'width-4 height-4 fill-current ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) {{ __('Maintenance') }}
                                    </button>

                                    <div class="collapse {{ ($errors->has('maintenance_start_at') || $errors->has('maintenance_end_at') ? 'show' : '') }}" id="collapseMaintenance">
                                        <div class="form-row mt-3">
                                            <div class="col-12"><label for="i-maintenance-start-at">{{ __('Period') }}</label></div>
                                            <div class="col-12 col-lg-6">
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <label for="i-maintenance-start-at" class="input-group-text">{{ __('Start') }}</label>
                                                        </div>
                                                        <input type="datetime-local" step="1" dir="ltr" name="maintenance_start_at" class="form-control{{ $errors->has('maintenance_start_at') ? ' is-invalid' : '' }}" id="i-maintenance-start-at" value="{{ old('maintenance_start_at') }}" placeholder="{{ Carbon\Carbon::now()->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d\TH:i:s') }}">
                                                    </div>
                                                    @if ($errors->has('maintenance_start_at'))
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $errors->first('maintenance_start_at') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-12 col-lg-6">
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <label for="i-maintenance-end-at" class="input-group-text">{{ __('End') }}</label>
                                                        </div>
                                                        <input type="datetime-local" step="1" dir="ltr" name="maintenance_end_at" class="form-control{{ $errors->has('maintenance_end_at') ? ' is-invalid' : '' }}" id="i-maintenance-end-at" value="{{ old('maintenance_end_at') }}" placeholder="{{ Carbon\Carbon::now()->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d\TH:i:s') }}">
                                                    </div>
                                                    @if ($errors->has('maintenance_end_at'))
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $errors->first('maintenance_end_at') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-12 mt-n3">
                                                <small class="form-text text-muted">{{ __('The maintenance period during which no monitor checks or alerts will be performed.') }}</small>
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
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
