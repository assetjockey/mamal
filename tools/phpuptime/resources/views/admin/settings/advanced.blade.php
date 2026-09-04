@extends('layouts.app')

@section('site_title', formatTitle([__('Advanced'), __('Settings'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Settings')],
            ]])

            <h1 class="h2 mb-3 d-inline-block">{{ __('Advanced') }}</h1>

            <div class="card border-0 shadow-sm">
                <div class="card-header"><div class="font-weight-medium py-1">{{ __('Advanced') }}</div></div>
                <div class="card-body">
                    @include('shared.message')

                    <form action="{{ route('admin.settings', 'advanced') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="i-demo-url">{{ __('Demo URL') }}</label>
                            <input type="text" dir="ltr" name="demo_url" id="i-demo-url" class="form-control{{ $errors->has('demo_url') ? ' is-invalid' : '' }}" value="{{ old('settings.demo_url') ?? config('settings.demo_url') }}">
                            @if ($errors->has('demo_url'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('demo_url') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The URL opened when users click the demo button on the homepage.') }}</small>
                        </div>

                        <div class="row mx-n2 mb-3">
                            <div class="col-auto font-weight-bold px-2">
                                <span class="badge badge-secondary text-uppercase">
                                    {{ __('Status page') }}
                                </span>
                            </div>
                            <div class="col d-flex align-items-center px-2">
                                <hr class="my-0 w-100">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="i-domain-protocol" class="d-flex align-items-center"><span>{{ __('Domain protocol') }}</span></label>
                            <select name="domain_protocol" id="i-domain-protocol" class="custom-select{{ $errors->has('domain_protocol') ? ' is-invalid' : '' }}">
                                @foreach(['http' => 'HTTP', 'https' => 'HTTPS'] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('domain_protocol') !== null && old('domain_protocol') == $key) || (config('settings.domain_protocol') == $key && old('domain_protocol') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('domain_protocol'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('domain_protocol') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The protocol used for custom domains.') }} {{ __('Enable only if you can generate SSL certificates for the custom domains.') }}</small>
                        </div>

                        <div class="row mx-n2 mb-3">
                            <div class="col-auto font-weight-bold px-2">
                                <span class="badge badge-secondary text-uppercase">
                                    {{ __('Monitor') }}
                                </span>
                            </div>
                            <div class="col d-flex align-items-center px-2">
                                <hr class="my-0 w-100">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="i-monitors-double-check">{{ __('Double check') }}</label>
                            <select name="monitors_double_check" id="i-monitors-double-check" class="custom-select{{ $errors->has('monitors_double_check') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('monitors_double_check') !== null && old('monitors_double_check') == $key) || (config('settings.monitors_double_check') == $key && old('monitors_double_check') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('monitors_double_check'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('monitors_double_check') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">
                                {{ __('Enable or disable double-check monitoring.') }} {{ __('When enabled, the system performs an additional check to confirm the monitor is down before triggering an alert.') }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="i-monitors-double-check-delay-seconds" class="d-inline-flex align-items-center"><span class="{{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">{{ __('Double check delay') }}</span></label>
                            <select name="monitors_double_check_delay_seconds" id="i-monitors-double-check-delay-seconds" class="custom-select{{ $errors->has('monitors_double_check_delay_seconds') ? ' is-invalid' : '' }}">
                                @foreach([1, 2, 3, 4, 5] as $value)
                                    <option value="{{ $value }}" @if ((old('monitors_double_check_delay_seconds') !== null && old('monitors_double_check_delay_seconds') == $value) || (config('settings.monitors_double_check_delay_seconds') == $value && old('monitors_double_check_delay_seconds') == null)) selected @endif>{{ Carbon\CarbonInterval::seconds($value)->forHumans() }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('monitors_double_check_delay_seconds'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('monitors_double_check_delay_seconds') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The delay that the system waits before performing a second check on monitors that appeared offline.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-request-simultaneous-requests">{{ __('Simultaneous requests') }}</label>
                            <input type="number" min="1" name="request_simultaneous_requests" id="i-request-simultaneous-requests" class="form-control{{ $errors->has('request_simultaneous_requests') ? ' is-invalid' : '' }}" value="{{ old('request_simultaneous_requests') ?? config('settings.request_simultaneous_requests') }}">
                            @if ($errors->has('request_simultaneous_requests'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('request_simultaneous_requests') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The number of simultaneous requests when checking the status of monitors.') }} {{ __('Increasing this value requires more server resources.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-custom-server-addr">{{ __('Custom server IP') }}</label>
                            <input type="text" name="custom_server_addr" id="i-custom-server-addr" class="form-control{{ $errors->has('custom_server_addr') ? ' is-invalid' : '' }}" value="{{ old('settings.custom_server_addr') ?? config('settings.custom_server_addr') }}">
                            @if ($errors->has('custom_server_addr'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('custom_server_addr') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{!! __('The custom IP address to use as the server\'s :name value.', ['name' => '<code>$_SERVER[\'SERVER_ADDR\']</code>']) !!} {!! __('Useful when the server runs behind a reverse proxy or load balancer that prevents the default :name from returning the correct IP.', ['name' => '<code>$_SERVER[\'SERVER_ADDR\']</code>']) !!} {{ __('Leave empty if not required.') }}</small>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseCrawler" aria-expanded="{{ ($errors->has('request_timeout') || $errors->has('request_http_version') || $errors->has('request_user_agent') || $errors->has('request_proxy') ? 'true' : 'false') }}" aria-controls="collapseCrawler">
                                @include('icons.account-tree', ['class' => 'fill-current width-4 height-4 ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) {{ __('Crawler') }}
                            </button>

                            <div class="collapse {{ ($errors->has('request_timeout') || $errors->has('request_http_version') || $errors->has('request_user_agent') || $errors->has('request_proxy') ? 'show' : '') }}" id="collapseCrawler">
                                <div class="form-group mt-3">
                                    <label for="i-request-timeout">{{ __('Timeout') }}</label>
                                    <input type="number" name="request_timeout" id="i-request-timeout" class="form-control{{ $errors->has('request_timeout') ? ' is-invalid' : '' }}" value="{{ old('request_timeout') ?? config('settings.request_timeout') }}">
                                    @if ($errors->has('request_timeout'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('request_timeout') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{{ __('The number of seconds to wait before the request is terminated if no response is received.') }}</small>
                                </div>

                                <div class="form-group">
                                    <label for="i-request-http-version">{{ __('HTTP version') }}</label>
                                    <select name="request_http_version" id="i-request-http-version" class="custom-select{{ $errors->has('request_http_version') ? ' is-invalid' : '' }}">
                                        @foreach(['1.1' => __('1.1'), '2' => __('2')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('request_http_version') !== null && old('request_http_version') == $key) || (config('settings.request_http_version') == $key && old('request_http_version') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('request_http_version'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('request_http_version') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-request-user-agent">{{ __('User-Agent') }}</label>
                                    <input type="text" name="request_user_agent" id="i-request-user-agent" class="form-control{{ $errors->has('request_user_agent') ? ' is-invalid' : '' }}" value="{{ old('request_user_agent') ?? config('settings.request_user_agent') }}">
                                    @if ($errors->has('request_user_agent'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('request_user_agent') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-request-proxy">{{ __('Proxies') }}</label>
                                    <textarea name="request_proxy" id="i-request-proxy" class="form-control{{ $errors->has('request_proxy') ? ' is-invalid' : '' }}" rows="3" placeholder="http://username:password@ip:port
            ">{{ config('settings.request_proxy') }}</textarea>
                                    @if ($errors->has('request_proxy'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('request_proxy') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{{ __('One per line.') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseTwilio" aria-expanded="{{ ($errors->has('request_timeout') || $errors->has('request_http_version') || $errors->has('request_user_agent') || $errors->has('request_proxy') ? 'true' : 'false') }}" aria-controls="collapseTwilio">
                                @include('icons.twilio', ['class' => 'fill-current width-4 height-4 ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) Twilio
                            </button>

                            <div class="collapse {{ ($errors->has('request_timeout') || $errors->has('request_http_version') || $errors->has('request_user_agent') || $errors->has('request_proxy') ? 'show' : '') }}" id="collapseTwilio">
                                <div class="alert alert-info mt-3">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => 'Twilio']) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#twilio" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="i-twilio">Twilio</label>
                                        <select name="twilio" id="i-twilio" class="custom-select{{ $errors->has('twilio') ? ' is-invalid' : '' }}">
                                            @foreach([0 => __('No'), 1 => __('Yes')] as $key => $value)
                                                <option value="{{ $key }}" @if ((old('twilio') !== null && old('twilio') == $key) || (config('settings.twilio') == $key && old('twilio') == null)) selected @endif>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('twilio'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('twilio') }}</strong>
                                            </span>
                                        @endif
                                        <small class="form-text text-muted">{{ __('Enable or disable the :service integration.', ['service' => 'Twilio']) }} {{ __('When enabled, the SMS alerts functionality will become available.') }}</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="i-twilio-sid">{{ __('Account SID') }}</label>
                                        <input type="text" name="twilio_sid" id="i-twilio-sid" class="form-control{{ $errors->has('twilio_sid') ? ' is-invalid' : '' }}" value="{{ old('twilio_sid') ?? config('settings.twilio_sid') }}">
                                        @if ($errors->has('twilio_sid'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('twilio_sid') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label for="i-twilio-token">{{ __('Auth token') }}</label>
                                        <input type="password" name="twilio_token" id="i-twilio-token" class="form-control{{ $errors->has('twilio_token') ? ' is-invalid' : '' }}" value="{{ old('twilio_token') ?? config('settings.twilio_token') }}">
                                        @if ($errors->has('twilio_token'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('twilio_token') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group mb-0">
                                        <label for="i-twilio-phone-number">{{ __('Phone number') }}</label>
                                        <input type="text" name="twilio_phone_number" id="i-twilio-phone-number" class="form-control{{ $errors->has('twilio_phone_number') ? ' is-invalid' : '' }}" value="{{ old('twilio_phone_number') ?? config('settings.twilio_phone_number') }}">
                                        @if ($errors->has('twilio_phone_number'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('twilio_phone_number') }}</strong>
                                            </span>
                                        @endif
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
