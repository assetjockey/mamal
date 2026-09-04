@extends('layouts.app')

@section('site_title', formatTitle([__('Advanced'), __('Settings'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Settings')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Advanced') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Advanced') }}</div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    <form action="{{ route('admin.settings', 'advanced') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="row mx-n2 mb-4">
                            <div class="col-auto fw-bold px-2">
                                <span class="badge badge-secondary text-uppercase">
                                    {{ __('Shortener') }}
                                </span>
                            </div>
                            <div class="col d-flex align-items-center px-2">
                                <hr class="my-0 w-full">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="i-short-link-metadata-fetch">{{ __('Link metadata fetching') }}</label>
                            <select name="short_link_metadata_fetch" id="i-short-link-metadata-fetch" class="custom-select{{ $errors->has('short_link_metadata_fetch') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('short_link_metadata_fetch') !== null && old('short_link_metadata_fetch') == $key) || (config('settings.short_link_metadata_fetch') == $key && old('short_link_metadata_fetch') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('short_link_metadata_fetch'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('short_link_metadata_fetch') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('Enable or disable link metadata fetching.') }} {{ __('When enabled, metadata such as page title and description will be automatically retrieved for shortened links.') }}
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="i-short-splash-redirect">{{ __('Splash redirect') }}</label>
                            <select name="short_splash_redirect" id="i-short-splash-redirect" class="custom-select{{ $errors->has('short_splash_redirect') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('short_splash_redirect') !== null && old('short_splash_redirect') == $key) || (config('settings.short_splash_redirect') == $key && old('short_splash_redirect') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('short_splash_redirect'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('short_splash_redirect') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('Enable or disable the splash screen redirect.') }} {!! __('When enabled, guest users and users without the :feature plan feature will see a splash screen before being redirected.', ['feature' => '<span class="fw-medium">' . __('No ads') . '</span>']) !!} </small>
                        </div>

                        <div class="form-group">
                            <label for="i-short-splash-redirect-delay-seconds">{{ __('Splash redirect delay') }}</label>
                            <div class="input-group">
                                <input type="number" name="short_splash_redirect_delay_seconds" id="i-short-splash-redirect-delay-seconds" class="form-control{{ $errors->has('short_splash_redirect_delay_seconds') ? ' is-invalid' : '' }}" value="{{ old('short_splash_redirect_delay_seconds') ?? config('settings.short_splash_redirect_delay_seconds') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">{{ mb_strtolower(__('Seconds')) }}</span>
                                </div>
                            </div>
                            @if ($errors->has('short_splash_redirect_delay_seconds'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('short_splash_redirect_delay_seconds') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The number of seconds before the splash page automatically redirects.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-short-splash-redirect-skipping">{{ __('Splash redirect skipping') }}</label>
                            <select name="short_splash_redirect_skipping" id="i-short-splash-redirect-skipping" class="custom-select{{ $errors->has('short_splash_redirect_skipping') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('short_splash_redirect_skipping') !== null && old('short_splash_redirect_skipping') == $key) || (config('settings.short_splash_redirect_skipping') == $key && old('short_splash_redirect_skipping') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('short_splash_redirect_skipping'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('short_splash_redirect_skipping') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('Enable or disable skipping the redirect timer.') }} {{ __('When enabled, users can skip the redirect timer and continue immediately.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-short-guest">{{ __('Guest shortening') }}</label>
                            <select name="short_guest" id="i-short-guest" class="custom-select{{ $errors->has('short_guest') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('short_guest') !== null && old('short_guest') == $key) || (config('settings.short_guest') == $key && old('short_guest') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('short_guest'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('short_guest') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('Enable or disable link shortening functionality for visitors who are not logged in.') }} {{ __('When enabled, shortened links will have limited stats.') }}</small>
                        </div>

                        <div class="row mx-n2 mb-4">
                            <div class="col-auto fw-bold px-2">
                                <span class="badge badge-secondary text-uppercase">
                                    {{ __('Domain') }}
                                </span>
                            </div>
                            <div class="col d-flex align-items-center px-2">
                                <hr class="my-0 w-full">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="i-short-domain-id" class="d-inline-flex align-items-center"><span class="me-2">{{ __('Domain') }}</span><span class="badge badge-secondary">{{ __('Default') }}</span></label>
                            <select name="short_domain_id" id="i-short-domain-id" class="custom-select">
                                <option value="">{{ __('None') }}</option>
                                @foreach($domains as $domain)
                                    <option value="{{ $domain->id }}" @if (config('settings.short_domain_id') == $domain->id) selected @endif>{{ str_replace(['http://', 'https://'], '', $domain->name) }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">{{ __('The default domain used when generating shortened links.') }}</small>
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
                            <small class="form-text text-muted">{{ __('The protocol used for redirects on custom domains.') }} {{ __('Enable only if you can generate SSL certificates for the custom domains.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-short-max-multi-links">{{ __('Maximum multiple links') }}</label>
                            <input type="number" name="short_max_multi_links" id="i-short-max-multi-links" class="form-control{{ $errors->has('short_max_multi_links') ? ' is-invalid' : '' }}" value="{{ old('short_max_multi_links') ?? config('settings.short_max_multi_links') }}">
                            @if ($errors->has('short_max_multi_links'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('short_max_multi_links') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The maximum number of links that can be shortened at once.') }}</small>
                        </div>

                        <div class="pb-4">
                            <button class="btn btn-soft-inverse d-block w-full d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseCrawler" aria-expanded="{{ ($errors->has('request_timeout') || $errors->has('request_http_version') || $errors->has('request_user_agent') || $errors->has('request_proxy') ? 'true' : 'false') }}" aria-controls="collapseCrawler">
                                @include('icons.account-tree', ['class' => 'fill-current w-4 h-4 me-2']) {{ __('Crawler') }}
                            </button>

                            <div class="collapse {{ ($errors->has('request_timeout') || $errors->has('request_http_version') || $errors->has('request_user_agent') || $errors->has('request_proxy') ? 'show' : '') }}" id="collapseCrawler">
                                <div class="form-group mt-4">
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

                        <div class="pb-4">
                            <button class="btn btn-soft-inverse d-block w-full d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseGoogleSafeBrowsing" aria-expanded="{{ ($errors->has('gsb') || $errors->has('gsb_key') ? 'true' : 'false') }}" aria-controls="collapseGoogleSafeBrowsing">
                                @include('icons.google', ['class' => 'fill-current w-4 h-4 me-2']) Google Safe Browsing
                            </button>

                            <div class="collapse {{ ($errors->has('gsb') || $errors->has('gsb_key') ? 'show' : '') }}" id="collapseGoogleSafeBrowsing">
                                <div class="alert alert-info mt-4">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => 'Google Safe Browsing']) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#google-safe-browsing" class="alert-link fw-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="i-gsb">Google Safe Browsing</label>
                                    <select name="gsb" id="i-gsb" class="custom-select{{ $errors->has('gsb') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('gsb') !== null && old('gsb') == $key) || (config('settings.gsb') == $key && old('gsb') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('gsb'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('gsb') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{{ __('Enable or disable the :service integration.', ['service' => 'Google Safe Browsing']) }}</small>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-gsb-key">{{ __('API key') }}</label>
                                    <input type="password" name="gsb_key" id="i-gsb-key" class="form-control{{ $errors->has('gsb_key') ? ' is-invalid' : '' }}" value="{{ old('gsb_key') ?? config('settings.gsb_key') }}">
                                    @if ($errors->has('gsb_key'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('gsb_key') }}</strong>
                                        </span>
                                    @endif
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
