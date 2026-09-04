@section('site_title', formatTitle([config('settings.title'), __(config('settings.tagline'))]))

@extends('layouts.app')

@section('head_content')

@endsection

@section('content')
    <div class="flex-fill">
        <div class="bg-base-0 position-relative pt-5 pt-sm-6">
            <div class="container position-relative py-sm-5 z-1">
                <h1 class="display-4 text-center text-break font-weight-bold mb-0">
                    {{ __('Reliable uptime monitoring') }}
                </h1>

                <p class="text-muted text-center text-break font-size-xl font-weight-normal mt-4 mb-5">
                    {{ __('Monitor your websites with detailed analytics, customizable status pages, and incident reports.') }}
                </p>

                <div class="row justify-content-md-center m-n2">
                    <div class="col-12 col-md-auto p-2">
                        <a href="{{ config('settings.registration') ? route('register') : route('login') }}"
                           class="btn btn-primary btn-lg btn-block font-size-lg d-inline-flex align-items-center justify-content-center">{{ __('Get started') }}</a>
                    </div>
                    <div class="col-12 col-md-auto p-2">
                        @if(config('settings.demo_url'))
                            <a href="{{ config('settings.demo_url') }}" target="_blank"
                               class="btn btn-outline-primary btn-lg btn-block font-size-lg d-inline-flex align-items-center justify-content-center">{{ __('Demo') }} @include('icons.external', ['class' => 'fill-current width-3 height-3 ' . (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                        @else
                            <a href="#features"
                               class="btn btn-outline-primary btn-lg btn-block font-size-lg d-inline-flex align-items-center justify-content-center">{{ __('Learn more') }}</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="position-relative z-0 mt-5 mt-sm-0">
                <div class="container">
                    <div class="position-absolute top-0 right-0 bottom-0 left-0 z-1 d-flex flex-column">
                        <div class="flex-grow-1"></div>
                        <div class="flex-grow-1"></div>
                        <div class="flex-grow-1"></div>
                        <div class="flex-grow-1 bg-fade-0"></div>
                    </div>

                    <div class="position-relative">
                        <div class="position-absolute top-n4 left-4 bottom-4 right-4 bg-primary opacity-10 border-radius-xl"></div>
                        <img src="{{ (config('settings.dark_mode') == 1 ? asset('img/hero-dark.png') : asset('img/hero.png')) }}"
                             class="position-relative img-fluid shadow-lg border-top-left-radius-xl border-top-right-radius-xl image-rendering-optimize-contrast"
                             data-theme-dark="{{ asset('img/hero-dark.png') }}"
                             data-theme-light="{{ asset('img/hero.png') }}" width="1512" height="700"
                             data-theme-target="src" alt="{{ config('settings.title') }}">
                    </div>
                </div>
            </div>

            <div class="bg-base-0 container position-relative pt-5 pb-5 pb-md-7 z-1 overflow-hidden">
                <div class="row m-n4 m-lg-n5">
                    <div class="col-12 col-lg-4 p-4 p-lg-5 d-flex">
                        <div class="d-flex position-relative text-primary width-4 height-4 align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }} mt-1">
                            @include('icons.monitor-heart', ['class' => 'fill-current width-4 height-4'])
                        </div>
                        <div>
                            <div class="d-block w-100 d-inline-block">
                            <span class="font-weight-bold">
                                {{ __('Monitors') }}.
                            </span>
                                <span class="text-muted">
                                {{ __('Uptime tracking for websites and SSLs.') }}
                            </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 p-4 p-lg-5 d-flex">
                        <div class="d-flex position-relative text-primary width-4 height-4 align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }} mt-1">
                            @include('icons.podcasts', ['class' => 'fill-current width-4 height-4'])
                        </div>
                        <div>
                            <div class="d-block w-100 d-inline-block">
                            <span class="font-weight-bold">
                                {{ __('Status pages') }}.
                            </span>
                                <span class="text-muted">
                                {{ __('Service status and incident history.') }}
                            </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 p-4 p-lg-5 d-flex">
                        <div class="d-flex position-relative text-primary width-4 height-4 align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }} mt-1">
                            @include('icons.error', ['class' => 'fill-current width-4 height-4'])
                        </div>
                        <div>
                            <div class="d-block w-100 d-inline-block">
                            <span class="font-weight-bold">
                                {{ __('Incidents') }}.
                            </span>
                                <span class="text-muted">
                                {{ __('Reports, notifications, and data export.') }}
                            </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="scroll-margin-top-18" id="features"></div>

        <div class="bg-base-1">
            <div class="container py-5 py-md-7">
                <div class="text-center">
                    <h2 class="h2 mb-3 font-weight-bold text-center">{{ __('Monitoring') }}</h2>
                    <div class="mx-auto mb-5">
                        <p class="text-muted font-weight-normal font-size-lg mb-0">{{ __('Everything you need to keep your websites monitored.') }}</p>
                    </div>
                </div>

                <div class="row m-n2 m-sm-n3">
                    <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-3">
                        <div class="card border-0 shadow-sm h-100 border-radius-xl overflow-hidden">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex width-10 height-10 position-relative align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                    <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                    @include('icons.stats', ['class' => 'fill-current width-5 height-5 text-primary'])
                                </div>
                                <div class="d-block w-100">
                                    <div class="font-size-lg mt-0 mb-1 mt-3 d-inline-block font-weight-bold">{{ __('Statistics') }}</div>
                                </div>
                                <div class="d-block w-100 text-muted">{{ __('Website performance insights and SSL status at a glance.') }}</div>
                            </div>
                            <div class="card-footer bg-base-0 p-0 border-0">
                                <div class="position-relative z-1 pt-0 pr-4 pb-4 pl-4">
                                    <div class="z-0">
                                        <div class="card bg-base-1 border-0 h-100 cursor-default border-radius-lg">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center text-truncate">
                                                    <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                        <div class="d-flex align-items-center">
                                                            @include('icons.status', ['class' => 'width-4 height-4 fill-current text-success'])
                                                            &#8203;
                                                        </div>
                                                    </div>

                                                    <div class="text-body text-truncate">example.com</div>

                                                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
                                                        @include('icons.lock', ['class' => 'fill-current width-4 height-4 text-success'])
                                                        &#8203;
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-3">
                        <div class="card border-0 shadow-sm h-100 border-radius-xl overflow-hidden">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex width-10 height-10 position-relative align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                    <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                    @include('icons.adjust', ['class' => 'fill-current width-5 height-5 text-primary'])
                                </div>
                                <div class="d-block w-100">
                                    <div class="font-size-lg mt-0 mb-1 mt-3 d-inline-block font-weight-bold">{{ __('Checks') }}</div>
                                </div>
                                <div class="d-block w-100 text-muted">{{ __('Status codes, response times, and location information.') }}</div>
                            </div>
                            <div class="card-footer bg-base-0 p-0 border-0">
                                <div class="position-relative z-1 pt-0 pr-4 pb-4 pl-4">
                                    <div class="z-0">
                                        <div class="card bg-base-1 border-0 h-100 cursor-default border-radius-lg">
                                            <div class="card-body p-3">
                                                <div class="row m-n2">
                                                    <div class="col p-2 text-truncate">
                                                        <div class="d-flex align-items-center text-truncate">
                                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                                <div class="d-flex align-items-center">
                                                                    <img src="{{ asset('img/icons/countries/' . mb_strtolower($location?->country?->isoCode ?? 'unknown') .'.svg') }}"
                                                                         class="width-4 height-4"
                                                                         alt="{{ __(config('countries')[$location?->country?->isoCode] ?? 'Unknown') }}">
                                                                </div>
                                                            </div>

                                                            <div class="text-body text-truncate">{{ __(config('countries')[$location?->country?->isoCode] ?? 'Unknown') }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto p-2">
                                                        {{ number_format(428, 0, __('.'), __(',')) }} ms
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-3">
                        <div class="card border-0 shadow-sm h-100 border-radius-xl overflow-hidden">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex width-10 height-10 position-relative align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                    <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                    @include('icons.error', ['class' => 'fill-current width-5 height-5 text-primary'])
                                </div>
                                <div class="d-block w-100">
                                    <div class="font-size-lg mt-0 mb-1 mt-3 d-inline-block font-weight-bold">{{ __('Incidents') }}</div>
                                </div>
                                <div class="d-block w-100 text-muted">{{ (config('settings.twilio') ? __('Outage reports with email, webhook, and SMS notifications.') : __('Outage reports with email and webhook notifications.')) }}</div>
                            </div>
                            <div class="card-footer bg-base-0 p-0 border-0">
                                <div class="position-relative z-1 pt-0 pr-4 pb-4 pl-4">
                                    <div class="z-0">
                                        <div class="card bg-base-1 border-0 h-100 cursor-default border-radius-lg">
                                            <div class="card-body p-3">
                                                <div class="row m-n2">
                                                    <div class="col p-2 text-truncate">
                                                        <div class="d-flex align-items-center text-truncate">
                                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                                <div class="d-flex align-items-center">
                                                                    @include('icons.error-filled', ['class' => 'width-4 height-4 fill-current text-danger'])
                                                                    &#8203;
                                                                </div>
                                                            </div>

                                                            <div class="text-body text-truncate">example.org</div>
                                                        </div>
                                                    </div>

                                                    <div class="col-auto d-flex align-items-center">
                                                        @php
                                                            Carbon\Carbon::disableHumanDiffOption(Carbon\Carbon::NO_ZERO_DIFF);
                                                        @endphp
                                                        {{ addSpacingToTimeUnits(Carbon\Carbon::now()->subHours(1)->subMinutes(3)->diffForHumans(Carbon\Carbon::now(), ['syntax' => true, 'parts' => 2, 'join' => true, 'short' => true])) }}
                                                        @php
                                                            Carbon\Carbon::enableHumanDiffOption(Carbon\Carbon::NO_ZERO_DIFF);
                                                        @endphp
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-base-0 overflow-hidden">
            <div class="container py-5 py-md-7 position-relative z-1">
                <div class="row mx-n5">
                    <div class="col-12 col-lg-6 px-5">
                        <div class="row">
                            <div class="col-12 text-center {{ (__('lang_dir') == 'rtl' ? 'text-lg-right' : 'text-lg-left') }}">
                                <h2 class="h2 mb-3 font-weight-bold">{{ __('Status pages') }}</h2>
                                <div class="m-auto">
                                    <p class="text-muted font-weight-normal font-size-lg mb-0">{{ __('Customizable status pages that show your website and SSL status at a glance.') }}</p>
                                </div>
                            </div>

                            <div class="col-12 pt-4 mt-4">
                                <div class="d-flex flex-row">
                                    <div class="d-flex width-12 height-12 position-relative align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                        <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                        @include('icons.website', ['class' => 'fill-current width-6 height-6 text-primary'])
                                    </div>
                                    <div class="{{ (__('lang_dir') == 'rtl' ? 'mr-1' : 'ml-1') }}">
                                        <div class="d-block w-100">
                                            <div class="h5 mt-0 mb-1 d-inline-block font-weight-bold">{{ __('Domain') }}</div>
                                        </div>
                                        <div class="d-block w-100 text-muted">{{ __('Create a branded experience by connecting a domain to your status page.') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 pt-4 mt-4">
                                <div class="d-flex flex-row">
                                    <div class="d-flex width-12 height-12 position-relative align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                        <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                        @include('icons.design-services', ['class' => 'fill-current width-6 height-6 text-primary'])
                                    </div>
                                    <div class="{{ (__('lang_dir') == 'rtl' ? 'mr-1' : 'ml-1') }}">
                                        <div class="d-block w-100">
                                            <div class="h5 mt-0 mb-1 d-inline-block font-weight-bold">{{ __('Appearance') }}</div>
                                        </div>
                                        <div class="d-block w-100 text-muted">{{ __('Personalize the appearance of your status page by using CSS code.') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 pt-4 mt-4">
                                <div class="d-flex flex-row">
                                    <div class="d-flex width-12 height-12 position-relative align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                        <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                        @include('icons.terminal', ['class' => 'fill-current width-6 height-6 text-primary'])
                                    </div>
                                    <div class="{{ (__('lang_dir') == 'rtl' ? 'mr-1' : 'ml-1') }}">
                                        <div class="d-block w-100">
                                            <div class="h5 mt-0 mb-1 d-inline-block font-weight-bold">{{ __('Functionality') }}</div>
                                        </div>
                                        <div class="d-block w-100 text-muted">{{ __('Expand the functionality of your status page by adding JavaScript code.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 px-5 position-relative mt-5 mt-lg-0">
                        <div class="position-relative">
                            <div class="position-absolute top-4 right-n4 bottom-n4 left-4 bg-primary opacity-10 border-radius-xl"></div>
                            <div class="position-relative">
                                <div class="position-absolute top-0 right-0 bottom-0 left-0 shadow-lg border-radius-xl z-0"></div>
                                <div class="card position-relative border-0 border-radius-xl overflow-hidden cursor-default z-1">
                                    <div class="card-body">
                                        <div class="list-group list-group-flush my-n3">
                                            <div class="list-group-item px-0">
                                                <div class="row">
                                                    <div class="col d-flex align-items-center text-truncate">
                                                        <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                            <div class="d-flex align-items-center">
                                                                @include('icons.status', ['class' => 'width-4 height-4 fill-current text-success'])
                                                                &#8203;
                                                            </div>
                                                        </div>

                                                        <div class="text-body text-truncate">example.com</div>

                                                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
                                                            @include('icons.lock', ['class' => 'fill-current width-4 height-4 text-success'])
                                                            &#8203;
                                                        </div>
                                                    </div>

                                                    <div class="col-auto text-muted">
                                                        {{ __(':value uptime', ['value' => number_format(99.8, 2, __('.'), __(',')) . '%']) }}
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="d-flex justify-content-between mt-2 graph-container overflow-hidden rounded-lg">
                                                            @for($i = 1; $i <= 30; $i++)
                                                                <div class="position-relative min-height-8 w-100">
                                                                    <div class="position-absolute top-0 right-0 bottom-0 left-0 {{ (in_array($i, [26]) ? 'bg-danger' : 'bg-success') }}"></div>
                                                                </div>

                                                                @if ($i !== 30)
                                                                    <div class="width-px flex-shrink-0"></div>
                                                                    <div class="width-px flex-shrink-0"></div>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="row text-muted small mt-2">
                                                            <div class="col">
                                                                {{ __('Last :days days', ['days' => 30]) }}
                                                            </div>

                                                            <div class="col-auto">
                                                                {{ __('Today') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="position-relative mt-3">
                                <div class="position-absolute top-0 right-0 bottom-0 left-0 shadow-lg border-radius-xl z-0"></div>
                                <div class="card position-relative border-0 border-radius-xl overflow-hidden cursor-default z-1">
                                    <div class="card-body">
                                        <div class="list-group list-group-flush my-n3">
                                            <div class="list-group-item px-0">
                                                <div class="row">
                                                    <div class="col d-flex align-items-center text-truncate">
                                                        <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                            <div class="d-flex align-items-center">
                                                                @include('icons.status', ['class' => 'width-4 height-4 fill-current text-blue'])
                                                                &#8203;
                                                            </div>
                                                        </div>

                                                        <div class="text-body text-truncate">example.net</div>

                                                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
                                                            @include('icons.lock', ['class' => 'fill-current width-4 height-4 text-blue'])
                                                            &#8203;
                                                        </div>
                                                    </div>

                                                    <div class="col-auto text-muted">
                                                        {{ __(':value uptime', ['value' => number_format(98.63, 2, __('.'), __(',')) . '%']) }}
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="d-flex justify-content-between mt-2 graph-container overflow-hidden">
                                                            @for($i = 1; $i <= 30; $i++)
                                                                <div class="position-relative min-height-8 w-100">
                                                                    <div class="position-absolute top-0 right-0 bottom-0 left-0 border-radius-xl {{ (in_array($i, [17, 38]) ? 'bg-danger' : 'bg-blue') }}"></div>
                                                                </div>

                                                                @if ($i !== 30)
                                                                    <div class="width-px flex-shrink-0"></div>
                                                                    <div class="width-px flex-shrink-0"></div>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="row text-muted small mt-2">
                                                            <div class="col">
                                                            <span class="d-none d-md-block">
                                                                {{ __('Last :days days', ['days' => 30]) }}
                                                            </span>
                                                            </div>

                                                            <div class="col-auto">
                                                                {{ __('Today') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="position-relative mt-3">
                                <div class="position-absolute top-0 right-0 bottom-0 left-0 shadow-lg border-radius-xl z-0"></div>
                                <div class="card position-relative border-0 border-radius-xl overflow-hidden cursor-default z-1">
                                    <div class="card-body">
                                        <div class="list-group list-group-flush my-n3">
                                            <div class="list-group-item px-0">
                                                <div class="row">
                                                    <div class="col d-flex align-items-center text-truncate">
                                                        <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                            <div class="d-flex align-items-center">
                                                                @include('icons.status', ['class' => 'width-4 height-4 fill-current text-violet'])
                                                                &#8203;
                                                            </div>
                                                        </div>

                                                        <div class="text-body text-truncate">example.org</div>

                                                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
                                                            @include('icons.lock', ['class' => 'fill-current width-4 height-4 text-violet'])
                                                            &#8203;
                                                        </div>
                                                    </div>

                                                    <div class="col-auto text-muted">
                                                        {{ __(':value uptime', ['value' => number_format(99.1, 2, __('.'), __(',')) . '%']) }}
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="d-flex justify-content-between mt-2 graph-container overflow-hidden">
                                                            @for($i = 1; $i <= 30; $i++)
                                                                <div class="position-relative w-100 aspect-ratio-1/1">
                                                                    <div class="position-absolute top-0 right-0 bottom-0 left-0 rounded-sm {{ (in_array($i, [28]) ? 'bg-danger' : 'bg-violet') }}"></div>
                                                                </div>

                                                                @if ($i !== 30)
                                                                    <div class="width-px flex-shrink-0"></div>
                                                                    <div class="width-px flex-shrink-0"></div>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="row text-muted small mt-2">
                                                            <div class="col">
                                                            <span class="d-none d-md-block">
                                                                {{ __('Last :days days', ['days' => 30]) }}
                                                            </span>
                                                            </div>

                                                            <div class="col-auto">
                                                                {{ __('Today') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="position-relative mt-3">
                                <div class="position-absolute top-0 right-0 bottom-0 left-0 shadow-lg border-radius-xl z-0"></div>
                                <div class="card position-relative border-0 border-radius-xl overflow-hidden cursor-default z-1">
                                    <div class="card-body">
                                        <div class="list-group list-group-flush my-n3">
                                            <div class="list-group-item px-0">
                                                <div class="row">
                                                    <div class="col d-flex align-items-center text-truncate">
                                                        <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                            <div class="d-flex align-items-center">
                                                                @include('icons.status', ['class' => 'width-4 height-4 fill-current text-dark'])
                                                                &#8203;
                                                            </div>
                                                        </div>

                                                        <div class="text-body text-truncate">example.edu</div>

                                                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
                                                            @include('icons.lock', ['class' => 'fill-current width-4 height-4 text-dark'])
                                                            &#8203;
                                                        </div>
                                                    </div>

                                                    <div class="col-auto text-muted">
                                                        {{ __(':value uptime', ['value' => number_format(99.43, 2, __('.'), __(',')) . '%']) }}
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="d-flex justify-content-between mt-2 graph-container overflow-hidden">
                                                            @for($i = 1; $i <= 30; $i++)
                                                                <div class="position-relative w-100 aspect-ratio-1/1">
                                                                    <div class="position-absolute top-0 right-0 bottom-0 left-0 rounded-circle {{ (in_array($i, [22]) ? 'bg-danger' : 'bg-dark') }}"></div>
                                                                </div>

                                                                @if ($i !== 30)
                                                                    <div class="width-px flex-shrink-0"></div>
                                                                    <div class="width-px flex-shrink-0"></div>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="row text-muted small mt-2">
                                                            <div class="col">
                                                            <span class="d-none d-md-block">
                                                                {{ __('Last :days days', ['days' => 30]) }}
                                                            </span>
                                                            </div>

                                                            <div class="col-auto">
                                                                {{ __('Today') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-base-1 overflow-hidden">
            <div class="container py-5 py-md-7 position-relative z-1">
                <div class="row mx-n5">
                    <div class="col-12 col-lg-6 px-5 order-1 order-lg-2">
                        <div class="row">
                            <div class="col-12 text-center {{ (__('lang_dir') == 'rtl' ? 'text-lg-right' : 'text-lg-left') }}">
                                <h2 class="h2 mb-3 font-weight-bold">{{ __('Incident management') }}</h2>
                                <div class="m-auto">
                                    <p class="text-muted font-weight-normal font-size-lg mb-0">{{ __('Incident reports and notifications to keep you better informed and in control.') }}</p>
                                </div>
                            </div>

                            <div class="col-12 pt-4 mt-4">
                                <div class="d-flex flex-row">
                                    <div class="d-flex width-12 height-12 position-relative align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                        <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                        @include('icons.list-alt', ['class' => 'fill-current width-6 height-6 text-primary'])
                                    </div>
                                    <div class="{{ (__('lang_dir') == 'rtl' ? 'mr-1' : 'ml-1') }}">
                                        <div class="d-block w-100">
                                            <div class="h5 mt-0 mb-1 d-inline-block font-weight-bold">{{ __('Reports') }}</div>
                                        </div>
                                        <div class="d-block w-100 text-muted">{{ __('Receive detailed incident reports for a complete overview of events.') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 pt-4 mt-4">
                                <div class="d-flex flex-row">
                                    <div class="d-flex width-12 height-12 position-relative align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                        <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                        @include('icons.notifications', ['class' => 'fill-current width-6 height-6 text-primary'])
                                    </div>
                                    <div class="{{ (__('lang_dir') == 'rtl' ? 'mr-1' : 'ml-1') }}">
                                        <div class="d-block w-100">
                                            <div class="h5 mt-0 mb-1 d-inline-block font-weight-bold">{{ __('Notifications') }}</div>
                                        </div>
                                        <div class="d-block w-100 text-muted">{{ (config('settings.twilio') ? __('Never miss a critical incident with alerts sent via email, webhook, and SMS.') : __('Never miss a critical incident with alerts sent via email and webhook.')) }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 pt-4 mt-4">
                                <div class="d-flex flex-row">
                                    <div class="d-flex width-12 height-12 position-relative align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                        <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                        @include('icons.file-download', ['class' => 'fill-current width-6 height-6 text-primary'])
                                    </div>
                                    <div class="{{ (__('lang_dir') == 'rtl' ? 'mr-1' : 'ml-1') }}">
                                        <div class="d-block w-100">
                                            <div class="h5 mt-0 mb-1 d-inline-block font-weight-bold">{{ __('Export') }}</div>
                                        </div>
                                        <div class="d-block w-100 text-muted">{{ __('Export all your incident events and their associated details in CSV format.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 px-5 position-relative mt-5 mt-lg-0 order-2 order-lg-1">
                        <div class="position-relative">
                            <div class="position-absolute top-4 right-4 bottom-n4 left-n4 bg-primary opacity-10 border-radius-xl"></div>
                            <div class="position-relative">
                                <div class="position-absolute top-0 right-0 bottom-0 left-0 shadow-lg border-radius-xl z-0"></div>
                                <div class="card position-relative border-0 border-radius-xl overflow-hidden cursor-default z-1">
                                    <div class="card-body">
                                        <div class="list-group list-group-flush my-n3">
                                            <div class="list-group-item px-0">
                                                <div class="row m-n1">
                                                    <div class="col-auto p-1">
                                                        <div class="width-8 height-8 d-flex align-items-center justify-content-center position-relative flex-shrink-0">
                                                            <div class="width-12 height-12 bg-base-0 rounded-circle position-absolute"></div>
                                                            <div class="width-6 height-6 bg-success rounded-circle opacity-10"></div>
                                                            @include('icons.check-circle-filled', ['class' => 'width-4 height-4 fill-current text-success position-absolute'])
                                                        </div>
                                                    </div>
                                                    <div class="col p-1 text-truncate">
                                                        <div class="text-truncate mt-1">{{ __('Incident resolved.') }}</div>
                                                        <div class="text-muted small">
                                                            {{ Carbon\Carbon::now()->subMonthNoOverflow()->setDay(12)->setHour(17)->setMinute(06)->setSecond(34)->format(__('Y-m-d') . ' H:i:s') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="position-relative mt-3">
                                <div class="position-absolute top-0 right-0 bottom-0 left-0 shadow-lg border-radius-xl z-0"></div>
                                <div class="card position-relative border-0 border-radius-xl overflow-hidden cursor-default z-1">
                                    <div class="card-body">
                                        <div class="list-group list-group-flush my-n3">
                                            <div class="list-group-item px-0">
                                                <div class="row m-n1">
                                                    <div class="col-auto p-1">
                                                        <div class="width-8 height-8 d-flex align-items-center justify-content-center position-relative flex-shrink-0">
                                                            <div class="width-12 height-12 bg-base-0 rounded-circle position-absolute"></div>
                                                            <div class="width-6 height-6 bg-warning rounded-circle opacity-10"></div>
                                                            @include('icons.offline-bolt-filled', ['class' => 'width-4 height-4 fill-current text-warning position-absolute'])
                                                        </div>
                                                    </div>
                                                    <div class="col p-1 text-truncate">
                                                        <div class="text-truncate mt-1">{{ __('Incident acknowledged.') }}</div>
                                                        <div class="text-muted small">
                                                            {{ Carbon\Carbon::now()->subMonthNoOverflow()->setDay(12)->setHour(16)->setMinute(38)->setSecond(52)->format(__('Y-m-d') . ' H:i:s') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if (config('settings.twilio'))
                                <div class="position-relative mt-3">
                                    <div class="position-absolute top-0 right-0 bottom-0 left-0 shadow-lg border-radius-xl z-0"></div>
                                    <div class="card position-relative border-0 border-radius-xl overflow-hidden cursor-default z-1">
                                        <div class="card-body">
                                            <div class="list-group list-group-flush my-n3">
                                                <div class="list-group-item px-0">
                                                    <div class="row m-n1">
                                                        <div class="col-auto p-1">
                                                            <div class="width-8 height-8 d-flex align-items-center justify-content-center position-relative flex-shrink-0">
                                                                <div class="width-12 height-12 bg-base-0 rounded-circle position-absolute"></div>
                                                                <div class="width-6 height-6 bg-secondary rounded-circle opacity-10"></div>
                                                                @include('icons.notifications-circle-filled', ['class' => 'width-4 height-4 fill-current text-secondary position-absolute'])
                                                            </div>
                                                        </div>
                                                        <div class="col p-1 text-truncate">
                                                            <div class="text-truncate mt-1">{!! __('Sent out :service alert.', ['service' => view('icons.sms-filled', ['class' => 'width-4 height-4 vertical-align-n0.5 mx-1 fill-current text-success']). '<span class="font-weight-medium">' . __('SMS') . '</span>']) !!}</div>
                                                            <div class="text-muted small">
                                                                {{ Carbon\Carbon::now()->subMonthNoOverflow()->setDay(12)->setHour(16)->setMinute(24)->setSecond(03)->format(__('Y-m-d') . ' H:i:s') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="position-relative mt-3">
                                    <div class="position-absolute top-0 right-0 bottom-0 left-0 shadow-lg border-radius-xl z-0"></div>
                                    <div class="card position-relative border-0 border-radius-xl overflow-hidden cursor-default z-1">
                                        <div class="card-body">
                                            <div class="list-group list-group-flush my-n3">
                                                <div class="list-group-item px-0">
                                                    <div class="row m-n1">
                                                        <div class="col-auto p-1">
                                                            <div class="width-8 height-8 d-flex align-items-center justify-content-center position-relative flex-shrink-0">
                                                                <div class="width-12 height-12 bg-base-0 rounded-circle position-absolute"></div>
                                                                <div class="width-6 height-6 bg-secondary rounded-circle opacity-10"></div>
                                                                @include('icons.notifications-circle-filled', ['class' => 'width-4 height-4 fill-current text-secondary position-absolute'])
                                                            </div>
                                                        </div>
                                                        <div class="col p-1 text-truncate">
                                                            <div class="text-truncate mt-1">{!! __('Sent out :service alert.', ['service' => view('icons.email-filled', ['class' => 'width-4 height-4 vertical-align-n0.5 mx-1 fill-current text-info']). '<span class="font-weight-medium">' . __('Email') . '</span>']) !!}</div>
                                                            <div class="text-muted small">
                                                                {{ Carbon\Carbon::now()->subMonthNoOverflow()->setDay(12)->setHour(16)->setMinute(24)->setSecond(03)->format(__('Y-m-d') . ' H:i:s') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="position-relative mt-3">
                                <div class="position-absolute top-0 right-0 bottom-0 left-0 shadow-lg border-radius-xl z-0"></div>
                                <div class="card position-relative border-0 border-radius-xl overflow-hidden cursor-default z-1">
                                    <div class="card-body">
                                        <div class="list-group list-group-flush my-n3">
                                            <div class="list-group-item px-0">
                                                <div class="row m-n1">
                                                    <div class="col-auto p-1">
                                                        <div class="width-8 height-8 d-flex align-items-center justify-content-center position-relative flex-shrink-0">
                                                            <div class="width-12 height-12 bg-base-0 rounded-circle position-absolute"></div>
                                                            <div class="width-6 height-6 bg-secondary rounded-circle opacity-10"></div>
                                                            @include('icons.notifications-circle-filled', ['class' => 'width-4 height-4 fill-current text-secondary position-absolute'])
                                                        </div>
                                                    </div>
                                                    <div class="col p-1 text-truncate">
                                                        <div class="text-truncate mt-1">{!! __('Sent out :service alert.', ['service' => '<img src="' . asset('img/icons/brands/' . md5('teams') . '.svg') . '" class="width-4 height-4 vertical-align-n0.5 mx-1"><span class="font-weight-medium">Microsoft Teams</span>']) !!}</div>
                                                        <div class="text-muted small">
                                                            {{ Carbon\Carbon::now()->subMonthNoOverflow()->setDay(12)->setHour(16)->setMinute(24)->setSecond(03)->format(__('Y-m-d') . ' H:i:s') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="position-relative mt-3">
                                <div class="position-absolute top-0 right-0 bottom-0 left-0 shadow-lg border-radius-xl z-0"></div>
                                <div class="card position-relative border-0 border-radius-xl overflow-hidden cursor-default mt-3">
                                    <div class="card-body">
                                        <div class="list-group list-group-flush my-n3">
                                            <div class="list-group-item px-0">
                                                <div class="row m-n1">
                                                    <div class="col-auto p-1">
                                                        <div class="width-8 height-8 d-flex align-items-center justify-content-center position-relative flex-shrink-0">
                                                            <div class="width-12 height-12 bg-base-0 rounded-circle position-absolute"></div>
                                                            <div class="width-6 height-6 bg-danger rounded-circle opacity-10"></div>
                                                            @include('icons.error-filled', ['class' => 'width-4 height-4 fill-current text-danger position-absolute'])
                                                        </div>
                                                    </div>
                                                    <div class="col p-1 text-truncate">
                                                        <div class="text-truncate mt-1">{{ __('Incident started.') }}</div>
                                                        <div class="text-muted small">
                                                            {{ Carbon\Carbon::now()->subMonthNoOverflow()->setDay(12)->setHour(16)->setMinute(24)->setSecond(03)->format(__('Y-m-d') . ' H:i:s') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-base-0">
            <div class="container position-relative text-center py-5 py-md-7 d-flex flex-column z-1">
                <h2 class="h2 mb-3 font-weight-bold text-center">{{ __('Integrations') }}</h2>
                <div class="m-auto text-center">
                    <p class="text-muted font-weight-normal font-size-lg mb-0">{{ __('Notifications delivered to your favorite platforms.') }}</p>
                </div>

                <div class="d-flex flex-wrap justify-content-center justify-content-lg-between mt-4 mx-n3">
                    <div class="bg-base-1 d-flex width-20 height-20 position-relative align-items-center justify-content-center flex-shrink-0 border-radius-3xl mx-3 mt-3"
                         data-tooltip="true" title="Microsoft Teams">
                        <img src="{{ asset('img/icons/brands/' . md5('teams')) }}.svg" class="width-8 height-8"
                             alt="Teams">
                    </div>

                    <div class="bg-base-1 d-flex width-20 height-20 position-relative align-items-center justify-content-center flex-shrink-0 border-radius-3xl mx-3 mt-3"
                         data-tooltip="true" title="Slack">
                        <img src="{{ asset('img/icons/brands/' . md5('slack')) }}.svg" class="width-8 height-8"
                             alt="Slack">
                    </div>

                    <div class="bg-base-1 d-flex width-20 height-20 position-relative align-items-center justify-content-center flex-shrink-0 border-radius-3xl mx-3 mt-3"
                         data-tooltip="true" title="Discord">
                        <img src="{{ asset('img/icons/brands/' . md5('discord')) }}.svg" class="width-8 height-8"
                             alt="Discord">
                    </div>

                    <div class="bg-base-1 d-flex width-20 height-20 position-relative align-items-center justify-content-center flex-shrink-0 border-radius-3xl mx-3 mt-3"
                         data-tooltip="true" title="Telegram">
                        <img src="{{ asset('img/icons/brands/' . md5('telegram')) }}.svg" class="width-8 height-8"
                             alt="Telegram">
                    </div>

                    <div class="bg-base-1 d-flex width-20 height-20 position-relative align-items-center justify-content-center flex-shrink-0 border-radius-3xl mx-3 mt-3"
                         data-tooltip="true" title="Flock">
                        <img src="{{ asset('img/icons/brands/' . md5('flock')) }}.svg" class="width-8 height-8"
                             alt="Flock">
                    </div>

                    <div class="bg-base-1 d-flex width-20 height-20 position-relative align-items-center justify-content-center flex-shrink-0 border-radius-3xl mx-3 mt-3"
                         data-tooltip="true" title="Webhook">
                        <img src="{{ asset('img/icons/brands/' . md5('webhook')) }}.svg" class="width-8 height-8"
                             alt="Webhook">
                    </div>

                    <div class="bg-base-1 d-flex width-20 height-20 position-relative align-items-center justify-content-center flex-shrink-0 border-radius-3xl mx-3 mt-3"
                         data-tooltip="true" title="{{ __('Email') }}">
                        @include('icons.email-filled', ['class' => 'width-8 height-8 fill-current text-info'])
                    </div>

                    @if (config('settings.twilio'))
                        <div class="bg-base-1 d-flex width-20 height-20 position-relative align-items-center justify-content-center flex-shrink-0 border-radius-3xl mx-3 mt-3"
                             data-tooltip="true" title="{{ __('SMS') }}">
                            @include('icons.sms-filled', ['class' => 'width-8 height-8 fill-current text-success'])
                        </div>
                    @endif
                </div>

                {{--<div class="d-flex flex-wrap justify-content-center justify-content-lg-between mt-4 mx-n3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-dark height-8 mx-3 mt-4" viewBox="0 0 1131 193.9"><path d="M360.92,141.98h-14.11v-55.11c0-4.55.26-10.05.87-16.57h-.26c-.86,3.69-1.64,6.36-2.34,7.98l-25.61,63.62h-9.77l-25.7-63.19c-.69-1.89-1.47-4.72-2.24-8.49h-.26c.34,3.43.51,8.93.51,16.66v54.94h-13.15V56.66h20.08l22.58,56.75c1.73,4.38,2.85,7.64,3.37,9.71h.26c1.47-4.46,2.68-7.81,3.55-9.96l23.01-56.5h19.3v85.33h-.09l.02-.02ZM384.98,68.4c-2.24,0-4.24-.78-5.8-2.23-1.64-1.47-2.42-3.35-2.42-5.58s.78-4.11,2.42-5.67c1.64-1.54,3.55-2.23,5.8-2.23s4.33.76,5.97,2.23c1.64,1.54,2.42,3.35,2.42,5.67,0,2.15-.78,3.94-2.42,5.5-1.64,1.54-3.63,2.32-5.97,2.32v-.02ZM391.81,141.98h-13.84v-60.79h13.84v60.79ZM451.26,139.24c-4.93,2.84-10.72,4.21-17.39,4.21-9.09,0-16.44-2.84-22.05-8.49-5.63-5.67-8.39-12.97-8.39-21.98,0-10.05,3.02-18.03,8.99-24.13,5.97-6.09,14.01-9.1,24.05-9.1,5.63,0,10.47.95,14.79,2.91v12.8c-4.24-3.18-8.82-4.72-13.67-4.72-5.88,0-10.65,1.98-14.45,5.92-3.73,3.94-5.63,9.1-5.63,15.46s1.73,11.25,5.28,14.93c3.55,3.6,8.31,5.49,14.18,5.49,5.02,0,9.69-1.81,14.11-5.31v12.02s.17,0,.17,0ZM497.97,94.33c-1.64-1.29-4.07-1.98-7.26-1.98-4.07,0-7.53,1.81-10.29,5.49-2.77,3.69-4.16,8.67-4.16,15.03v29.1h-13.84v-60.79h13.84v12.53h.26c1.39-4.29,3.46-7.64,6.23-10.05,2.85-2.4,5.97-3.6,9.43-3.6,2.51,0,4.41.34,5.7,1.12v13.14h.08v.02ZM531.27,143.45c-9.43,0-17.04-2.84-22.66-8.49-5.63-5.67-8.48-13.22-8.48-22.58,0-10.22,2.95-18.2,8.82-23.96,5.88-5.75,13.84-8.59,23.78-8.59s17.04,2.84,22.41,8.42c5.36,5.58,8.04,13.31,8.04,23.18s-2.85,17.43-8.65,23.27c-5.7,5.84-13.5,8.76-23.27,8.76h0ZM531.98,90.72c-5.46,0-9.69,1.89-12.89,5.67-3.12,3.77-4.75,8.93-4.75,15.54s1.56,11.42,4.75,15.03c3.2,3.69,7.43,5.49,12.81,5.49s9.69-1.81,12.62-5.41c2.95-3.6,4.41-8.76,4.41-15.37s-1.47-11.85-4.41-15.54c-2.85-3.6-7.09-5.41-12.55-5.41h0ZM570.91,140.09v-12.8c5.19,3.94,10.89,5.84,17.13,5.84,8.4,0,12.55-2.49,12.55-7.39,0-1.37-.34-2.57-1.04-3.52s-1.64-1.81-2.95-2.57c-1.22-.78-2.68-1.47-4.33-2.06-1.64-.61-3.46-1.29-5.53-2.06-2.51-1.03-4.75-2.06-6.75-3.18s-3.73-2.4-5.02-3.77-2.34-3.01-3.02-4.8c-.69-1.81-1.04-3.87-1.04-6.27,0-2.91.69-5.5,2.07-7.73,1.39-2.23,3.29-4.11,5.63-5.67,2.34-1.54,5.02-2.67,8.04-3.43,3.02-.78,6.06-1.12,9.26-1.12,5.63,0,10.72.86,15.23,2.57v12.02c-4.32-2.91-9.26-4.38-14.88-4.38-1.73,0-3.38.17-4.75.51-1.39.34-2.6.86-3.63,1.47-1.04.61-1.82,1.37-2.34,2.32-.51.86-.87,1.89-.87,2.91,0,1.29.26,2.4.87,3.35.51.95,1.39,1.71,2.51,2.4,1.12.69,2.43,1.29,3.97,1.89,1.56.59,3.29,1.2,5.28,1.89,2.6,1.03,5.02,2.15,7.09,3.26,2.07,1.12,3.9,2.4,5.36,3.77s2.6,3.01,3.38,4.89c.78,1.81,1.22,4.04,1.22,6.53,0,3.09-.69,5.75-2.17,8.07-1.45,2.29-3.4,4.22-5.7,5.66-2.42,1.54-5.19,2.67-8.31,3.35-3.12.76-6.41,1.12-9.87,1.12-6.66.25-12.45-.86-17.39-3.09h.02ZM652.22,143.45c-9.43,0-17.04-2.84-22.66-8.49-5.63-5.67-8.48-13.22-8.48-22.58,0-10.22,2.95-18.2,8.82-23.96,5.88-5.75,13.84-8.59,23.8-8.59s17.04,2.84,22.41,8.42c5.36,5.58,8.04,13.31,8.04,23.18s-2.85,17.43-8.65,23.27c-5.8,5.84-13.58,8.76-23.27,8.76h-.02ZM652.83,90.72c-5.46,0-9.69,1.89-12.89,5.67-3.12,3.77-4.75,8.93-4.75,15.54s1.56,11.42,4.75,15.03c3.2,3.69,7.43,5.49,12.8,5.49s9.69-1.81,12.64-5.41c2.95-3.6,4.41-8.76,4.41-15.37s-1.47-11.85-4.41-15.54c-2.85-3.6-7.09-5.41-12.55-5.41ZM728.28,63.16c-1.9-1.03-3.97-1.64-6.41-1.64-6.75,0-10.12,3.77-10.12,11.33v8.25h14.28v10.82h-14.18v49.97h-13.84v-49.97h-10.47v-10.82h10.47v-9.87c0-6.44,2.07-11.5,6.31-15.2s9.52-5.58,15.84-5.58c3.46,0,6.14.34,8.14,1.12v11.6l-.02-.02ZM767.83,141.29c-2.68,1.37-6.31,2.06-10.72,2.06-11.86,0-17.82-5.67-17.82-17v-34.34h-10.21v-10.82h10.21v-14.07l13.84-3.94v18.03h14.62v10.82h-14.62v30.39c0,3.6.69,6.19,2,7.73,1.29,1.54,3.55,2.32,6.58,2.32,2.34,0,4.41-.69,6.14-2.06v10.91l-.02-.03ZM864.97,68.83h-24.66v73.15h-14.28v-73.15h-24.58v-12.02h63.5v12.02h.02ZM913.34,115.28h-41.71c.17,5.58,1.9,9.96,5.28,12.97,3.29,3.09,7.87,4.55,13.76,4.55,6.58,0,12.55-1.98,18-5.83v11.08c-5.63,3.52-12.98,5.24-22.15,5.24s-16.1-2.74-21.2-8.25c-5.1-5.5-7.7-13.31-7.7-23.27,0-9.45,2.85-17.18,8.48-23.1,5.63-5.92,12.62-8.93,21.03-8.93s14.88,2.67,19.47,7.98c4.58,5.33,6.92,12.8,6.92,22.24v5.33h-.17l-.02-.02ZM900.01,105.57c0-4.99-1.22-8.76-3.55-11.6-2.34-2.74-5.53-4.11-9.6-4.11s-7.36,1.47-10.12,4.29c-2.77,2.91-4.5,6.7-5.11,11.33h28.38v.09ZM971.05,141.98h-13.5v-9.52h-.26c-4.24,7.29-10.47,10.91-18.59,10.91-6.06,0-10.72-1.64-14.18-4.89-3.37-3.26-5.11-7.56-5.11-12.87,0-11.5,6.66-18.2,19.98-20.09l18.17-2.57c0-8.67-4.16-12.97-12.45-12.97-7.26,0-13.84,2.49-19.73,7.47v-12.02c6.48-3.77,13.93-5.67,22.41-5.67,15.49,0,23.19,7.56,23.19,22.66v39.57h.08,0ZM957.63,112.1l-12.89,1.81c-3.97.51-7.01,1.47-8.99,2.91-2,1.37-3.02,3.87-3.02,7.39,0,2.57.95,4.72,2.77,6.36,1.82,1.64,4.33,2.49,7.43,2.49,4.24,0,7.7-1.47,10.47-4.47,2.77-2.91,4.16-6.7,4.16-11.16v-5.33h.08-.02ZM1077.03,141.98h-13.84v-33.14c0-6.36-.86-10.99-2.68-13.82s-4.85-4.29-9.16-4.29c-3.63,0-6.75,1.81-9.26,5.41s-3.8,7.9-3.8,12.97v32.89h-13.93v-34.26c0-11.33-4.07-17-12.11-17-3.73,0-6.84,1.71-9.26,5.07-2.42,3.43-3.63,7.81-3.63,13.22v32.89h-13.84v-60.79h13.84v9.62h.26c4.41-7.39,10.89-11.08,19.3-11.08,4.24,0,7.96,1.12,11.08,3.43,3.2,2.32,5.36,5.33,6.48,9.1,4.58-8.42,11.33-12.62,20.34-12.62,13.5,0,20.25,8.25,20.25,24.72v37.69l-.03.02ZM1087.59,140.09v-12.8c5.19,3.94,10.89,5.84,17.13,5.84,8.39,0,12.55-2.49,12.55-7.39,0-1.37-.34-2.57-1.04-3.52-.69-.95-1.64-1.81-2.95-2.57-1.22-.78-2.68-1.47-4.33-2.06-1.64-.61-3.46-1.29-5.53-2.06-2.51-1.03-4.75-2.06-6.75-3.18s-3.73-2.4-5.02-3.77c-1.29-1.37-2.34-3.01-3.02-4.8-.69-1.81-1.04-3.87-1.04-6.27,0-2.91.69-5.5,2.07-7.73s3.29-4.11,5.63-5.67c2.34-1.54,5.02-2.67,8.04-3.43,3.02-.78,6.06-1.12,9.26-1.12,5.63,0,10.72.86,15.23,2.57v12.02c-4.33-2.91-9.26-4.38-14.88-4.38-1.73,0-3.37.17-4.75.51s-2.59.86-3.63,1.47c-1.04.61-1.82,1.37-2.34,2.32-.51.86-.86,1.89-.86,2.91,0,1.29.26,2.4.86,3.35.51.95,1.39,1.71,2.51,2.4s2.42,1.29,3.97,1.89,3.29,1.2,5.28,1.89c2.6,1.03,5.02,2.15,7.09,3.26,2.07,1.12,3.9,2.4,5.36,3.77s2.59,3.01,3.37,4.89c.78,1.81,1.22,4.04,1.22,6.53,0,3.09-.69,5.75-2.17,8.07-1.45,2.29-3.4,4.22-5.7,5.66-2.42,1.54-5.19,2.67-8.31,3.35-3.12.76-6.41,1.12-9.87,1.12-6.67.25-12.55-.86-17.39-3.09h.03Z"/><path d="M119.66,62.52c17.12-3.17,28.42-19.62,25.25-36.74-3.17-17.12-19.62-28.42-36.74-25.25-14.61,2.71-25.35,15.25-25.77,30.1h15.35c12.1,0,21.91,9.81,21.91,21.91v9.98h0ZM63.71,163.31h34.04c12.1,0,21.91-9.81,21.91-21.91v-68.69h36.25c5.03.13,9,4.3,8.88,9.33v55.94c.7,30.16-23.16,55.18-53.32,55.92-21.08-.52-39.08-12.9-47.77-30.61v.03h0ZM203.58,41.23c0,12.05-9.77,21.81-21.81,21.81s-21.81-9.77-21.81-21.81,9.77-21.81,21.81-21.81,21.81,9.77,21.81,21.81M174.42,164.82l-1.56-.03c3.48-8.56,5.16-17.73,4.96-26.96v-55.66c.05-3.25-.62-6.48-1.95-9.44h23.34c5.08,0,9.21,4.13,9.21,9.21v49.04c0,18.68-15.16,33.83-33.84,33.84h-.16Z"/><path d="M8.88,43.65h88.87c4.91,0,8.88,3.97,8.88,8.88v88.87c.01,4.89-3.94,8.87-8.83,8.88H8.88c-4.89.02-8.87-3.93-8.88-8.82V52.52c0-4.91,3.97-8.88,8.88-8.88h0ZM76.7,77.47v-9.38H29.93v9.38h17.68v48.39h11.33v-48.39h17.76Z"/></svg>

                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-dark height-8 mx-3 mt-4" viewBox="0 0 496.4 125.6"><path d="M158.8,98.9l6.2-14.4c6.7,5,15.6,7.6,24.4,7.6,6.5,0,10.6-2.5,10.6-6.3-.1-10.6-38.9-2.3-39.2-28.9-.1-13.5,11.9-23.9,28.9-23.9,10.1,0,20.2,2.5,27.4,8.2l-5.8,14.7c-6.6-4.2-14.8-7.2-22.6-7.2-5.3,0-8.8,2.5-8.8,5.7.1,10.4,39.2,4.7,39.6,30.1,0,13.8-11.7,23.5-28.5,23.5-12.3,0-23.6-2.9-32.2-9.1M396.7,79.3c-3.1,5.4-8.9,9.1-15.6,9.1-9.9,0-17.9-8-17.9-17.9s8-17.9,17.9-17.9c6.7,0,12.5,3.7,15.6,9.1l17.1-9.5c-6.4-11.4-18.7-19.2-32.7-19.2-20.7,0-37.5,16.8-37.5,37.5s16.8,37.5,37.5,37.5c14.1,0,26.3-7.7,32.7-19.2,0,0-17.1-9.5-17.1-9.5ZM228.1,1.9h21.4v104.7h-21.4V1.9ZM422.2,1.9v104.7h21.4v-31.4l25.4,31.4h27.4l-32.3-37.3,29.9-34.8h-26.2l-24.2,28.9V1.9h-21.4ZM313.1,79.5c-3.1,5.1-9.5,8.9-16.7,8.9-9.9,0-17.9-8-17.9-17.9s8-17.9,17.9-17.9c7.2,0,13.6,4,16.7,9.2v17.7ZM313.1,34.5v8.5c-3.5-5.9-12.2-10-21.3-10-18.8,0-33.6,16.6-33.6,37.4s14.8,37.6,33.6,37.6c9.1,0,17.8-4.1,21.3-10v8.5h21.4V34.5h-21.4Z"/><path d="M26.5,79.4c0,7.3-5.9,13.2-13.2,13.2S.1,86.7.1,79.4s5.9-13.2,13.2-13.2h13.2v13.2ZM33.1,79.4c0-7.3,5.9-13.2,13.2-13.2s13.2,5.9,13.2,13.2v33c0,7.3-5.9,13.2-13.2,13.2s-13.2-5.9-13.2-13.2v-33Z"/><path d="M46.3,26.4c-7.3,0-13.2-5.9-13.2-13.2S39,0,46.3,0s13.2,5.9,13.2,13.2v13.2h-13.2ZM46.3,33.1c7.3,0,13.2,5.9,13.2,13.2s-5.9,13.2-13.2,13.2H13.2c-7.3,0-13.2-5.9-13.2-13.2s5.9-13.2,13.2-13.2h33.1Z"/><path d="M99.2,46.3c0-7.3,5.9-13.2,13.2-13.2s13.2,5.9,13.2,13.2-5.9,13.2-13.2,13.2h-13.2v-13.2ZM92.6,46.3c0,7.3-5.9,13.2-13.2,13.2s-13.2-5.9-13.2-13.2V13.2c0-7.3,5.9-13.2,13.2-13.2s13.2,5.9,13.2,13.2c0,0,0,33.1,0,33.1Z"/><path d="M79.4,99.2c7.3,0,13.2,5.9,13.2,13.2s-5.9,13.2-13.2,13.2-13.2-5.9-13.2-13.2v-13.2h13.2ZM79.4,92.6c-7.3,0-13.2-5.9-13.2-13.2s5.9-13.2,13.2-13.2h33.1c7.3,0,13.2,5.9,13.2,13.2s-5.9,13.2-13.2,13.2h-33.1Z"/></svg>

                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-dark height-8 mx-3 mt-4" viewBox="0 0 635.3 96"><path id="d" d="M81.15,0c-1.24,2.2-2.35,4.47-3.36,6.79-9.6-1.44-19.37-1.44-28.99,0-.99-2.32-2.12-4.6-3.36-6.79-9.02,1.54-17.81,4.24-26.14,8.06C2.78,32.53-1.69,56.37.53,79.89c9.67,7.15,20.51,12.6,32.05,16.09,2.6-3.49,4.9-7.2,6.87-11.06-3.74-1.39-7.35-3.13-10.81-5.15.91-.66,1.79-1.34,2.65-2,20.28,9.55,43.77,9.55,64.08,0,.86.71,1.74,1.39,2.65,2-3.46,2.05-7.07,3.76-10.83,5.18,1.97,3.86,4.27,7.58,6.87,11.06,11.54-3.49,22.38-8.92,32.05-16.06,2.63-27.28-4.5-50.92-18.82-71.85C98.98,4.27,90.19,1.57,81.18.05l-.03-.05ZM42.28,65.41c-6.24,0-11.42-5.66-11.42-12.65s4.98-12.68,11.39-12.68,11.52,5.71,11.42,12.68c-.1,6.97-5.03,12.65-11.39,12.65ZM84.36,65.41c-6.26,0-11.39-5.66-11.39-12.65s4.98-12.68,11.39-12.68,11.49,5.71,11.39,12.68c-.1,6.97-5.03,12.65-11.39,12.65ZM168.84,13.31h37.7c9.09,0,16.77,1.42,23.06,4.25,5.79,2.43,10.7,6.56,14.1,11.85,3.18,5.22,4.81,11.23,4.69,17.34.07,6.14-1.62,12.17-4.87,17.37-3.63,5.5-8.82,9.79-14.9,12.32-6.66,3.04-14.93,4.55-24.79,4.54h-34.99V13.31h0ZM203.44,63.77c6.12,0,10.83-1.53,14.11-4.58,3.37-3.27,4.95-7.72,4.93-12.52.02-4.45-1.38-8.59-4.39-11.74-2.95-2.93-7.38-4.4-13.3-4.4h-11.79v33.25h10.44,0ZM304.84,80.88c-4.97-1.25-9.73-3.23-14.11-5.9v-16.04c3.83,2.78,8.13,4.83,12.7,6.05,5,1.53,10.2,2.32,15.42,2.36,1.8.1,3.61-.22,5.27-.91,1.19-.61,1.78-1.38,1.78-2.18.03-.9-.32-1.78-.97-2.42-1.1-.85-2.4-1.41-3.77-1.64l-11.6-2.61c-6.65-1.55-11.36-3.69-14.15-6.42-2.83-2.86-4.33-6.77-4.14-10.78-.05-3.6,1.26-7.09,3.67-9.76,2.86-3.02,6.46-5.23,10.44-6.42,5.13-1.61,10.48-2.38,15.85-2.28,5.02-.05,10.02.54,14.89,1.74,3.93.93,7.71,2.43,11.21,4.45v15.19c-3.28-1.91-6.82-3.35-10.49-4.29-3.99-1.07-8.09-1.61-12.22-1.6-6.06,0-9.09,1.03-9.09,3.09-.02.94.52,1.8,1.38,2.18,1.65.7,3.39,1.2,5.16,1.49l9.67,1.74c6.28,1.1,10.96,3.04,14.04,5.8s4.63,6.8,4.64,12.12c.1,5.69-2.83,11.01-7.69,13.97-5.09,3.42-12.36,5.12-21.8,5.11-5.43,0-10.84-.67-16.1-2.03h0ZM373.27,78.85c-5.17-2.37-9.52-6.22-12.52-11.05-2.8-4.74-4.23-10.16-4.14-15.66-.08-5.5,1.43-10.9,4.35-15.56,3.11-4.75,7.55-8.5,12.76-10.77,6.33-2.76,13.2-4.1,20.1-3.92,9.67,0,17.69,2.03,24.07,6.09v17.72c-2.43-1.63-5.08-2.9-7.87-3.77-3.13-.99-6.39-1.48-9.67-1.45-5.99,0-10.68,1.1-14.07,3.29-4.76,2.66-6.47,8.67-3.81,13.43.87,1.55,2.13,2.84,3.67,3.73,3.29,2.22,8.05,3.33,14.31,3.33,3.23,0,6.44-.46,9.53-1.38,2.82-.81,5.53-1.96,8.06-3.44v17.12c-7.47,4.36-16,6.56-24.65,6.38-6.93.19-13.81-1.21-20.12-4.09h0ZM441.95,78.85c-5.23-2.39-9.66-6.22-12.77-11.05-2.94-4.73-4.46-10.19-4.39-15.76-.09-5.5,1.43-10.9,4.39-15.53,3.13-4.7,7.54-8.41,12.72-10.69,12.71-5.16,26.92-5.16,39.63,0,5.15,2.26,9.55,5.95,12.66,10.63,2.94,4.65,4.45,10.06,4.35,15.56.07,5.56-1.44,11.02-4.35,15.75-3.08,4.83-7.49,8.67-12.7,11.05-12.62,5.41-26.91,5.41-39.53,0v.03h0ZM471.32,61.7c2.41-2.49,3.69-5.87,3.53-9.33.17-3.43-1.11-6.78-3.53-9.22-2.62-2.41-6.11-3.65-9.67-3.44-3.55-.19-7.03,1.05-9.67,3.44-2.41,2.45-3.69,5.79-3.52,9.22-.16,3.46,1.12,6.84,3.52,9.33,2.61,2.44,6.1,3.71,9.67,3.52,3.57.21,7.07-1.07,9.67-3.52ZM556.62,25.47v20.91c-2.89-1.72-6.21-2.56-9.57-2.42-5.15,0-9.13,1.57-11.89,4.69s-4.14,7.97-4.14,14.54v17.78h-23.68V24.43h23.2v17.95c1.29-6.57,3.37-11.42,6.24-14.54,2.82-3.11,6.91-4.74,11.05-4.69,3.06,0,6.15.72,8.8,2.32ZM635.3,11.38v69.59h-23.68v-12.7c-1.79,4.52-4.99,8.34-9.14,10.88-4.58,2.61-9.78,3.91-15.05,3.76-4.96.12-9.83-1.24-14.02-3.91-4.05-2.63-7.27-6.35-9.31-10.73-2.22-4.83-3.32-10.11-3.23-15.42-.16-5.51,1.02-10.98,3.42-15.95,2.21-4.55,5.66-8.38,9.96-11.05,4.41-2.67,9.49-4.05,14.65-3.96,11.27,0,18.84,4.9,22.71,14.69V11.38h23.68ZM608.14,61.37c2.44-2.4,3.76-5.72,3.63-9.14.1-3.32-1.22-6.53-3.63-8.81-5.58-4.55-13.59-4.55-19.18,0-2.41,2.33-3.71,5.58-3.58,8.93-.12,3.38,1.19,6.66,3.62,9.02,2.56,2.38,5.98,3.63,9.47,3.47,3.56.2,7.04-1.05,9.67-3.47h0ZM268.35,9.24c6.52,0,11.81,4.75,11.81,10.6s-5.29,10.6-11.81,10.6-11.81-4.75-11.81-10.6,5.29-10.6,11.81-10.6ZM256.53,37.75c7.56,3.18,16.07,3.18,23.63,0v43.52h-23.63v-43.52Z"/></svg>

                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-dark height-8 mx-3 mt-4" viewBox="0 0 895.49 304.82"><path d="M310.29,275.28c-9.8-7.2-22-17-23.2-34.2-.6-9.2,2-19.5,4.6-27,1.6-3.5,3-7.1,4.3-10.7v-.1c28.1-79.3-13.4-166.4-92.8-194.5C123.79-19.32,36.89,22.28,8.79,101.58c-28.1,79.3,13.4,166.4,92.8,194.5,43.8,15.5,92.3,10.2,131.7-14.5,4-1.9,7.4-3,9.3-2.2,20.2,8.5,42.3,11.1,63.9,7.3,3.8-.6,13.5-4.2,3.8-11.4ZM136.39,204.48c0,9.5-7.7,17.2-17.2,17.2h-11.7c-9.5,0-17.2-7.7-17.2-17.2v-.7c0-9.5,7.7-17.2,17.2-17.2h11.7c9.5,0,17.2,7.7,17.2,17.2v.7ZM189.39,153.38c0,9.5-7.7,17.2-17.2,17.2h-64.7c-9.5,0-17.2-7.7-17.2-17.2v-.8c0-9.5,7.7-17.2,17.2-17.2h64.7c9.5,0,17.2,7.7,17.2,17.2v.8ZM227.09,101.18c0,9.5-7.7,17.2-17.2,17.2h-102.4c-9.5,0-17.2-7.7-17.2-17.2v-.7c0-9.5,7.7-17.2,17.2-17.2h102.4c9.5,0,17.2,7.7,17.2,17.2v.7Z"/><path d="M440.99,86.78c-3.7,0-7-2.5-12.2-2.5-10.2,0-16.5,7-16.5,19.7v7.5h14.5c6.5,0,11.8,5.3,11.8,11.8s-5.3,11.8-11.8,11.8h-14.5v85.1c0,7.4-6,13.4-13.5,13.4s-13.4-6-13.4-13.4v-85.2h-10c-6.5,0-11.8-5.3-11.8-11.8s5.3-11.8,11.8-11.8h10v-7.5c0-25.4,15.2-40.9,38.4-40.9,13.7,0,26.7,5,26.7,13.5.2,5.4-4,10-9.3,10.2q-.1.1-.2.1Z"/><path d="M486.79,233.38c-7.2,0-13.2-5.8-13.2-13V77.08c0-7,5.7-13,13.7-13,7.1.1,12.9,5.9,13,13v143.1c-.1,7.3-6.1,13.2-13.5,13.2Z"/><path d="M586.79,234.88c-38.6,0-62.1-28.7-62.1-63.3s23.4-63.1,62.1-63.1,62,28.7,62,63.1-23.4,63.3-62,63.3ZM586.79,132.18c-22.2,0-34.4,18.4-34.4,39.4s12.2,39.6,34.4,39.6,34.1-18.4,34.1-39.6-12.2-39.4-34.1-39.4Z"/><path d="M767.39,221.38c-8.7,8-21.2,13.5-38.6,13.5-36.6,0-62.3-26.7-62.3-63.3s25.7-63.1,62.3-63.1c18.4,0,33.9,6.5,40.6,16.2,1.2,1.6,1.8,3.5,1.7,5.5,0,7-6.2,11.5-12.5,11.5-2.8.1-5.5-.8-7.7-2.5-6-4.6-13.3-7.1-20.9-7-21.7,0-36.1,16.2-36.1,39.4s14.5,39.6,36.1,39.6c7.6,0,15-2.5,20.9-7.2,2.2-1.6,4.9-2.5,7.7-2.5,6.5,0,12.7,4.7,12.7,11.7.1,3.2-1.3,6.3-3.9,8.2Z"/><path d="M882.39,233.38c-4.4.1-8.5-1.9-11-5.5l-33.4-45.4-16.5,17.2v20.5c-.1,7.4-6.1,13.3-13.5,13.2-7.2,0-13.2-5.8-13.2-13V77.08c0-7,5.7-13,13.7-13,7.1.1,12.9,5.9,13,13v92l50.1-55.3c2.2-2.5,5.4-3.9,8.7-4,6.9.2,12.5,5.8,12.7,12.7-.1,2.9-1.2,5.7-3,8l-32.9,35.6,35.9,46.4c1.5,2.2,2.4,4.8,2.5,7.5.1,6.4-5.2,13.4-13.1,13.4Z"/></svg>

                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-dark height-8 mx-3 mt-4" viewBox="0 0 431.45 120.11"><path d="M60.47,50.35c-5.37,9.02-10.51,17.76-15.76,26.43-1.35,2.23-2.01,4.04-.94,6.87,2.97,7.82-1.22,15.42-9.1,17.49-7.43,1.95-14.68-2.94-16.15-10.9-1.31-7.04,4.16-13.95,11.93-15.05.65-.09,1.32-.1,2.41-.19,3.81-6.38,7.71-12.92,11.81-19.81-7.43-7.39-11.85-16.03-10.88-26.73.69-7.57,3.67-14.1,9.1-19.46,10.42-10.26,26.3-11.92,38.57-4.05,11.78,7.57,17.17,22.3,12.57,34.92-3.47-.94-6.96-1.89-10.8-2.93,1.44-7.01.38-13.31-4.35-18.71-3.12-3.56-7.14-5.43-11.69-6.12-9.14-1.38-18.12,4.49-20.78,13.46-3.02,10.18,1.55,18.5,14.05,24.77Z" style="fill-rule:evenodd;"/><path d="M75.79,39.68c3.78,6.67,7.62,13.44,11.43,20.15,19.23-5.95,33.73,4.7,38.93,16.09,6.28,13.77,1.99,30.07-10.35,38.57-12.67,8.72-28.68,7.23-39.91-3.97,2.86-2.39,5.73-4.8,8.81-7.37,11.08,7.18,20.78,6.84,27.97-1.66,6.14-7.25,6-18.07-.31-25.17-7.29-8.19-17.05-8.44-28.85-.58-4.89-8.68-9.88-17.3-14.61-26.05-1.6-2.95-3.36-4.66-6.97-5.28-6.02-1.04-9.9-6.21-10.13-12-.23-5.72,3.14-10.9,8.41-12.91,5.22-2,11.35-.39,14.86,4.05,2.87,3.63,3.78,7.71,2.27,12.18-.42,1.25-.96,2.45-1.55,3.93Z" style="fill-rule:evenodd;"/><path d="M84.83,94.2h-23.15c-2.22,9.13-7.01,16.5-15.27,21.18-6.42,3.64-13.34,4.88-20.7,3.69C12.14,116.89,1.05,104.68.08,90.93c-1.11-15.57,9.6-29.41,23.86-32.52.99,3.58,1.98,7.19,2.97,10.76-13.09,6.68-17.62,15.09-13.96,25.61,3.23,9.26,12.39,14.33,22.33,12.37,10.16-2,15.28-10.44,14.65-23.99,9.63,0,19.27-.1,28.9.05,3.76.06,6.66-.33,9.5-3.65,4.66-5.46,13.25-4.96,18.27.19,5.13,5.26,4.89,13.74-.54,18.78-5.24,4.87-13.52,4.61-18.42-.64-1.01-1.08-1.8-2.36-2.8-3.69Z" style="fill-rule:evenodd;"/><path d="M158.29,47.38h8.89l5.11,24.16,5.23-24.16h9.14l-9.73,33.55h-9.02l-5.26-24.44-5.32,24.44h-9.11l-9.42-33.54h9.42l5.23,24.07,4.83-24.07Z"/><path d="M211.08,48.06c2.34,1.05,4.27,2.7,5.8,4.96,1.37,1.99,2.26,4.31,2.67,6.94.24,1.54.33,3.76.29,6.66h-24.43c.14,3.36,1.31,5.72,3.51,7.08,1.34.84,2.95,1.26,4.84,1.26,2,0,3.62-.51,4.87-1.54.68-.56,1.28-1.32,1.8-2.31h8.96c-.24,1.99-1.32,4.01-3.25,6.06-3.01,3.26-7.21,4.89-12.62,4.89-4.46,0-8.4-1.38-11.82-4.13-3.41-2.75-5.12-7.23-5.12-13.43,0-5.81,1.54-10.27,4.62-13.37,3.08-3.1,7.08-4.65,12-4.65,2.92,0,5.55.52,7.89,1.57ZM197.96,55.64c-1.24,1.28-2.02,3.01-2.34,5.19h15.11c-.16-2.33-.94-4.1-2.34-5.3-1.4-1.21-3.13-1.81-5.2-1.81-2.25,0-4,.64-5.24,1.92Z"/><path d="M252.19,51.41c2.51,3.18,3.76,7.28,3.76,12.31s-1.24,9.53-3.71,12.96c-2.48,3.43-5.93,5.14-10.36,5.14-2.79,0-5.02-.55-6.71-1.66-1.01-.66-2.1-1.81-3.28-3.45v4.22h-8.65v-45.3h8.77v16.13c1.12-1.56,2.34-2.75,3.69-3.57,1.59-1.03,3.61-1.54,6.07-1.54,4.44,0,7.91,1.59,10.42,4.77ZM244.86,71.85c1.26-1.82,1.89-4.23,1.89-7.2,0-2.38-.31-4.35-.93-5.91-1.18-2.95-3.35-4.43-6.52-4.43s-5.41,1.45-6.61,4.34c-.62,1.54-.93,3.53-.93,5.97,0,2.87.64,5.25,1.93,7.14,1.28,1.89,3.24,2.83,5.86,2.83,2.28,0,4.04-.91,5.31-2.74Z"/><path d="M284.46,47.57c1.68.72,3.07,1.82,4.15,3.3.92,1.25,1.48,2.54,1.69,3.87s.31,3.49.31,6.48v19.71h-8.95v-20.43c0-1.81-.31-3.27-.92-4.38-.79-1.56-2.3-2.34-4.52-2.34s-4.05.78-5.24,2.33c-1.19,1.55-1.79,3.76-1.79,6.64v18.18h-8.77v-45.21h8.77v16c1.27-1.95,2.73-3.31,4.4-4.08,1.67-.77,3.42-1.15,5.26-1.15,2.06,0,3.94.36,5.62,1.08Z"/><path d="M324.84,76.82c-2.83,3.5-7.13,5.24-12.89,5.24s-10.06-1.75-12.9-5.24c-2.83-3.5-4.25-7.7-4.25-12.62s1.42-9.03,4.25-12.58c2.83-3.55,7.13-5.32,12.9-5.32s10.06,1.77,12.89,5.32c2.83,3.55,4.25,7.74,4.25,12.58s-1.42,9.13-4.25,12.62ZM317.83,71.98c1.37-1.82,2.06-4.42,2.06-7.78s-.69-5.95-2.06-7.76c-1.38-1.81-3.34-2.72-5.91-2.72s-4.54.91-5.92,2.72-2.08,4.4-2.08,7.76.69,5.95,2.08,7.78,3.36,2.73,5.92,2.73,4.53-.91,5.91-2.73Z"/><path d="M361.63,76.82c-2.83,3.5-7.13,5.24-12.89,5.24s-10.06-1.75-12.89-5.24c-2.83-3.5-4.25-7.7-4.25-12.62s1.42-9.03,4.25-12.58c2.83-3.55,7.13-5.32,12.89-5.32s10.06,1.77,12.89,5.32c2.83,3.55,4.25,7.74,4.25,12.58,0,4.92-1.42,9.13-4.25,12.62ZM354.61,71.98c1.37-1.82,2.06-4.42,2.06-7.78s-.69-5.95-2.06-7.76c-1.38-1.81-3.34-2.72-5.91-2.72s-4.54.91-5.93,2.72-2.08,4.4-2.08,7.76.69,5.95,2.08,7.78,3.36,2.73,5.93,2.73,4.53-.91,5.91-2.73Z"/><path d="M370.22,35.72h8.62v24.47l11.06-12.65h10.91l-12.02,12.5,12.49,20.89h-10.67l-8.13-14.33-3.63,3.78v10.56h-8.62v-45.21Z"/><path d="M409.66,70.22c.19,1.56.59,2.67,1.21,3.32,1.09,1.17,3.11,1.75,6.06,1.75,1.73,0,3.11-.26,4.13-.77,1.02-.51,1.53-1.28,1.53-2.31s-.41-1.73-1.23-2.25c-.82-.51-3.88-1.39-9.18-2.65-3.81-.94-6.5-2.12-8.06-3.54-1.56-1.4-2.34-3.4-2.34-6.03,0-3.1,1.22-5.76,3.65-7.99,2.44-2.23,5.86-3.34,10.29-3.34,4.19,0,7.61.84,10.26,2.51,2.64,1.67,4.16,4.56,4.55,8.66h-8.77c-.12-1.13-.44-2.02-.96-2.68-.97-1.19-2.61-1.78-4.94-1.78-1.91,0-3.27.3-4.09.89-.81.6-1.22,1.29-1.22,2.09,0,1.01.43,1.73,1.3,2.19.86.47,3.92,1.28,9.16,2.43,3.49.82,6.11,2.06,7.86,3.72,1.72,1.68,2.59,3.79,2.59,6.31,0,3.32-1.24,6.04-3.72,8.14-2.48,2.1-6.31,3.15-11.49,3.15s-9.18-1.11-11.7-3.34c-2.52-2.23-3.78-5.06-3.78-8.51h8.9Z"/></svg>
                </div>--}}
            </div>
        </div>

        @if(enabledPaymentProcessors())
            <div class="bg-base-1">
                <div class="container py-5 py-md-7 position-relative z-1">
                    <div class="text-center">
                        <h2 class="h2 mb-3 font-weight-bold text-center">{{ __('Pricing') }}</h2>
                        <div class="m-auto">
                            <p class="text-muted font-weight-normal font-size-lg mb-0">{{ __('Simple pricing plans for everyone and every budget.') }}</p>
                        </div>
                    </div>

                    @include('shared.pricing')

                    <div class="d-flex justify-content-center">
                        <a href="{{ route('pricing') }}"
                           class="btn btn-outline-primary py-2 mt-5">{{ __('Learn more') }}<span
                                    class="sr-only"> {{ mb_strtolower(__('Pricing')) }}</span></a>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-base-1">
                <div class="container position-relative text-center py-5 py-md-7 d-flex flex-column z-1">
                    <div class="flex-grow-1">
                        <div class="badge badge-pill badge-success mb-3 px-3 py-2">{{ __('Join us') }}</div>
                        <div class="text-center">
                            <h4 class="mb-3 font-weight-bold">{{ __('Ready to get started?') }}</h4>
                            <div class="m-auto">
                                <p class="font-weight-normal text-muted font-size-lg mb-0">{{ __('Create an account in seconds.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div><a href="{{ config('settings.registration') ? route('register') : route('login') }}"
                            class="btn btn-primary btn-lg font-size-lg mt-5">{{ __('Get started') }}</a></div>
                </div>
            </div>
        @endif
    </div>
@endsection
