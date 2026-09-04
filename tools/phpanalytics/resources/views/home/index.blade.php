@section('site_title', formatTitle([config('settings.title'), __(config('settings.tagline'))]))

@extends('layouts.app')

@section('head_content')

@endsection

@section('content')
    <div class="flex-fill">
        <div class="bg-base-0 position-relative pt-5 pt-sm-6">
            <div class="container position-relative py-sm-5 z-1">
                <h1 class="display-4 text-center text-break font-weight-bold mb-0">
                    {{ __('Privacy focused web analytics') }}
                </h1>

                <p class="text-muted text-center text-break font-size-xl font-weight-normal mt-4 mb-5">
                    {{ __('Track your visitors in realtime, without compromising their privacy.') }}
                </p>

                <div class="row justify-content-md-center m-n2">
                    <div class="col-12 col-md-auto p-2">
                        <a href="{{ config('settings.registration') ? route('register') : route('login') }}" class="btn btn-primary btn-lg btn-block font-size-lg d-inline-flex align-items-center justify-content-center">{{ __('Get started') }}</a>
                    </div>
                    <div class="col-12 col-md-auto p-2">
                        @if(config('settings.demo_url'))
                            <a href="{{ config('settings.demo_url') }}" target="_blank" class="btn btn-outline-primary btn-lg btn-block font-size-lg d-inline-flex align-items-center justify-content-center">{{ __('Demo') }} @include('icons.external', ['class' => 'fill-current width-3 height-3 ' . (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                        @else
                            <a href="#features" class="btn btn-outline-primary btn-lg btn-block font-size-lg d-inline-flex align-items-center justify-content-center">{{ __('Learn more') }}</a>
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
                        <img src="{{ (config('settings.dark_mode') == 1 ? asset('img/hero-dark.png') : asset('img/hero.png')) }}" class="position-relative img-fluid shadow-lg border-top-left-radius-xl border-top-right-radius-xl image-rendering-optimize-contrast" data-theme-dark="{{ asset('img/hero-dark.png') }}" data-theme-light="{{ asset('img/hero.png') }}" width="1512" height="700" data-theme-target="src" alt="{{ config('settings.title') }}">
                    </div>
                </div>
            </div>

            <div class="bg-base-0 container position-relative pt-5 pb-5 pb-md-7 z-1 overflow-hidden">
                <div class="row m-n4 m-lg-n5">
                    <div class="col-12 col-lg-4 p-4 p-lg-5 d-flex">
                        <div class="d-flex position-relative text-primary width-4 height-4 align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }} mt-1">
                            @include('icons.account-circle', ['class' => 'fill-current width-4 height-4'])
                        </div>
                        <div>
                            <div class="d-block w-100 d-inline-block">
                                <span class="font-weight-bold">
                                    {{ __('Privacy') }}.
                                </span>
                                <span class="text-muted">
                                    {{ __('No IP addresses, sessions, or cookies stored.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 p-4 p-lg-5 d-flex">
                        <div class="d-flex position-relative text-primary width-4 height-4 align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }} mt-1">
                            @include('icons.security', ['class' => 'fill-current width-4 height-4'])
                        </div>
                        <div>
                            <div class="d-block w-100 d-inline-block">
                                <span class="font-weight-bold">
                                    {{ __('Compliancy') }}.
                                </span>
                                <span class="text-muted">
                                    {{ __('Meets GDPR, CCPA, and PECR regulations.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 p-4 p-lg-5 d-flex">
                        <div class="d-flex position-relative text-primary width-4 height-4 align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }} mt-1">
                            @include('icons.bolt', ['class' => 'fill-current width-4 height-4'])
                        </div>
                        <div>
                            <div class="d-block w-100 d-inline-block">
                                <span class="font-weight-bold">
                                    {{ __('Speed') }}.
                                </span>
                                <span class="text-muted">
                                    {{ __('Tracking code smaller than 1kb in size.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="scroll-margin-top-18" id="features"></div>

        <div class="bg-base-1">
            <div class="container py-5 py-md-7 position-relative z-1">
                <h2 class="h2 mb-3 font-weight-bold text-center">{{ __('Analytics') }}</h2>
                <div class="m-auto text-center">
                    <p class="text-muted font-weight-normal font-size-lg mb-0">{{ __('Get to know your visitors with our advanced analytics.') }}</p>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-4">
                        <div class="row mx-lg-n4">
                            @php
                                $features = [
                                    [
                                        'icon' => 'adjust',
                                        'title' => __('Realtime'),
                                        'description' => __('See a detailed report of your website traffic in realtime.')
                                    ],
                                    [
                                        'icon' => 'assesment',
                                        'title' => __('Overview'),
                                        'description' => __('Get a comprehensive overview of your website statistics.')
                                    ],
                                    [
                                        'icon' => 'web',
                                        'title' => __('Behavior'),
                                        'description' => __('Analyze what pages perform the best on your website.')
                                    ],
                                    [
                                        'icon' => 'acquisition',
                                        'title' => __('Acquisitions'),
                                        'description' => __('Learn through which traffic channels you acquire your visitors.')
                                    ]
                                ];
                            @endphp

                            @foreach($features as $feature)
                                <div class="col-12 pr-md-3 pl-md-3 pr-lg-4 pl-lg-4 mt-5">
                                    <div class="d-flex">
                                        <div class="d-flex position-relative text-primary width-8 height-8 align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                            <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-lg"></div>
                                            @include('icons.' . $feature['icon'], ['class' => 'fill-current width-4 height-4'])
                                        </div>
                                        <div>
                                            <div class="d-block w-100">
                                                <div class="mt-1 mb-1 d-inline-block font-weight-bold font-size-lg">{{ $feature['title'] }}</div>
                                            </div>
                                            <div class="d-block w-100 text-muted">{{ $feature['description'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 mt-5 position-relative">
                        <div class="position-relative">
                            <div class="position-absolute top-4 left-4 bottom-n4 right-4 bg-primary opacity-10 border-radius-xl"></div>
                            <div class="position-relative">
                                <div class="position-absolute top-0 right-0 bottom-0 left-0 shadow-lg border-radius-xl z-0"></div>
                                <div class="card position-relative border-0 border-radius-xl overflow-hidden cursor-default z-1">
                                    <div class="card-body">
                                        <div class="list-group list-group-flush my-n3">
                                            <div class="list-group-item px-0 border-0">
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <div class="d-flex text-truncate align-items-center">
                                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="width-4 height-4" viewBox="0 0 40 40">
                                                                    <path d="m20,0C8.95,0,0,8.95,0,20c11.05,0,20-8.95,20-20Z" style="fill:#e23a33; stroke-width:0px;"/>
                                                                    <path d="m20,40c11.05,0,20-8.95,20-20-11.05,0-20,8.95-20,20Z" style="fill:#32a04c; stroke-width:0px;"/>
                                                                    <path d="m20,0c11.05,0,20,8.95,20,20-11.05,0-20-8.95-20-20Z" style="fill:#4389ed; stroke-width:0px;"/>
                                                                    <path d="m20,40C8.95,40,0,31.05,0,20c11.05,0,20,8.95,20,20Z" style="fill:#f4b10b;"/>
                                                                </svg>

                                                            </div>
                                                            <div class="d-flex text-truncate">
                                                                <div class="text-truncate" dir="ltr">example.com</div>
                                                                <div class="text-secondary d-flex align-items-center  {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-current width-3 height-3" viewBox="0 0 18 18">
                                                                        <path d="M16,16H2V2H9V0H2A2,2,0,0,0,0,2V16a2,2,0,0,0,2,2H16a2,2,0,0,0,2-2V9H16ZM11,0V2h3.59L4.76,11.83l1.41,1.41L16,3.41V7h2V0Z"></path>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }} text-right">
                                                            <div>
                                                                <span>{{ number_format(277, 0, __('.'), __(',')) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="progress height-1.25 w-100">
                                                        <div class="progress-bar bg-primary rounded" style="width: 76%"></div>
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
                                            <div class="list-group-item px-0 border-0">
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <div class="d-flex text-truncate align-items-center">
                                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                                <img src="{{ asset('img/icons/countries/us.svg') }}" class="width-4 height-4" alt="{{ __('United States') }}">
                                                            </div>
                                                            <div class="text-truncate">
                                                                United States
                                                            </div>
                                                        </div>

                                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }} text-right">
                                                            <div>
                                                                <span>{{ number_format(73, 0, __('.'), __(',')) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="progress height-1.25 w-100">
                                                        <div class="progress-bar bg-primary rounded" style="width: 11%"></div>
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
                                            <div class="list-group-item px-0 border-0">
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <div class="d-flex text-truncate align-items-center">
                                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                                <img src="{{ asset('img/icons/countries/de.svg') }}" class="width-4 height-4" alt="{{ __('Berlin') }}">
                                                            </div>
                                                            <div class="text-truncate">
                                                                Berlin
                                                            </div>
                                                        </div>

                                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }} text-right">
                                                            <div>
                                                                <span>{{ number_format(55, 0, __('.'), __(',')) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="progress height-1.25 w-100">
                                                        <div class="progress-bar bg-primary rounded" style="width: 10%"></div>
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
                                            <div class="list-group-item px-0 border-0">
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <div class="d-flex text-truncate align-items-center">
                                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                                <img src="{{ asset('img/icons/devices/desktop.svg') }}" class="width-4 height-4" alt="{{ __('Desktop') }}">
                                                            </div>
                                                            <div class="text-truncate">
                                                                Desktop
                                                            </div>
                                                        </div>

                                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }} text-right">
                                                            <div>
                                                                <span>{{ number_format(546, 0, __('.'), __(',')) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="progress height-1.25 w-100">
                                                        <div class="progress-bar bg-primary rounded" style="width: 86%"></div>
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
                                            <div class="list-group-item px-0 border-0">
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <div class="d-flex text-truncate align-items-center">
                                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                                <img src="{{ asset('img/icons/browsers/chrome.svg') }}" class="width-4 height-4" alt="Chrome">
                                                            </div>
                                                            <div class="text-truncate">
                                                                Chrome
                                                            </div>
                                                        </div>

                                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }} text-right">
                                                            <div>
                                                                <span>{{ number_format(469, 0, __('.'), __(',')) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="progress height-1.25 w-100">
                                                        <div class="progress-bar bg-primary rounded" style="width: 74%"></div>
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
                                            <div class="list-group-item px-0 border-0">
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <div class="d-flex text-truncate align-items-center">
                                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                                <img src="{{ asset('img/icons/os/windows.svg') }}" class="width-4 height-4" alt="Windows">
                                                            </div>
                                                            <div class="text-truncate">
                                                                Windows
                                                            </div>
                                                        </div>

                                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }} text-right">
                                                            <div>
                                                                <span>{{ number_format(379, 0, __('.'), __(',')) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="progress height-1.25 w-100">
                                                        <div class="progress-bar bg-primary rounded" style="width: 60%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="row mx-lg-n4">
                            @php
                                $features = [
                                    [
                                        'icon' => 'map',
                                        'title' => __('Geographic'),
                                        'description' => __('Find out where your visitors are from, down to the city level.')
                                    ],
                                    [
                                        'icon' => 'devices',
                                        'title' => __('Technology'),
                                        'description' => __('Know the devices and software your visitors are using.')
                                    ],
                                    [
                                        'icon' => 'filter-center-focus',
                                        'title' => __('Events'),
                                        'description' => __('Create custom events and track their conversions.')
                                    ],
                                    [
                                        'icon' => 'file-download',
                                        'title' => __('Export'),
                                        'description' => __('Export all your website\'s statistics in CSV format.')
                                    ]
                                ];
                            @endphp

                            @foreach($features as $feature)
                                <div class="col-12 pr-md-3 pl-md-3 pr-lg-4 pl-lg-4 mt-5">
                                    <div class="d-flex">
                                        <div class="d-flex position-relative text-primary width-8 height-8 align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                            <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-lg"></div>
                                            @include('icons.' . $feature['icon'], ['class' => 'fill-current width-4 height-4'])
                                        </div>
                                        <div>
                                            <div class="d-block w-100">
                                                <div class="mt-1 mb-1 d-inline-block font-weight-bold font-size-lg">{{ $feature['title'] }}</div>
                                            </div>
                                            <div class="d-block w-100 text-muted">{{ $feature['description'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-base-0">
            <div class="container position-relative text-center py-5 py-md-7 d-flex flex-column z-1">
                <h2 class="h2 mb-3 font-weight-bold text-center">{{ __('Integrations') }}</h2>
                <div class="m-auto text-center">
                    <p class="text-muted font-weight-normal font-size-lg mb-0">{{ __('Tracking code compatible with your favorite platforms.') }}</p>
                </div>

                <div class="d-flex flex-wrap justify-content-center justify-content-lg-between mt-4 mx-n3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-dark height-8 mx-3 mt-4" viewBox="0 0 370.1 84">
                        <path d="M214.6,33.1H200v1.5c4.6,0,5.3,1,5.3,6.8V51.8c0,5.8-.7,6.8-5.3,6.8-3.5-.5-5.9-2.4-9.1-5.9l-3.7-4.1c5-.9,7.7-4.1,7.7-7.6,0-4.5-3.8-7.9-11-7.9H169.6v1.5c4.6,0,5.3,1,5.3,6.8V51.8c0,5.8-.7,6.8-5.3,6.8v1.5h16.2V58.6c-4.6,0-5.3-1.1-5.3-6.8V48.9h1.4l9,11.3h23.7c11.6,0,16.7-6.2,16.7-13.6S226.2,33.1,214.6,33.1ZM180.5,46.3V35.5h3.3c3.7,0,5.3,2.5,5.3,5.5,0,2.8-1.6,5.4-5.3,5.4l-3.3-.1Zm34.3,11.3h-.6c-2.9,0-3.3-.7-3.3-4.5V35.4h3.9c8.5,0,10,6.2,10,11C224.8,51.5,223.3,57.6,214.8,57.6Z"/>
                        <path d="M124.3,49.3l5.6-16.6c1.6-4.8.9-6.2-4.3-6.2V24.9h15.3v1.6c-5.1,0-6.3,1.2-8.4,7.2l-9.3,27.8h-1.1L113.8,36l-8.5,25.5h-1.1l-9-27.8c-2-5.9-3.3-7.2-8-7.2V24.9h18.1v1.6c-4.8,0-6.1,1.1-4.4,6.2l5.5,16.6,8.2-24.4h1.5Z"/>
                        <path d="M151.9,61.2c-8.9,0-16.3-6.6-16.3-14.6S142.9,32,151.9,32s16.3,6.6,16.3,14.6S160.9,61.2,151.9,61.2Zm0-26.7c-7.5,0-10.1,6.8-10.1,12,0,5.4,2.6,12,10.1,12s10.2-6.7,10.2-12S159.5,34.5,151.9,34.5Z"/>
                        <path d="M251.4,58.5v1.6H232.8V58.5c5.5,0,6.4-1.4,6.4-9.5V36c0-8.1-1-9.4-6.4-9.4V25h16.8c8.4,0,13,4.3,13,10.1,0,5.6-4.6,10-13,10H245v4C244.9,57.2,245.9,58.5,251.4,58.5Zm-1.8-30.6H245V42h4.6c4.6,0,6.7-3.2,6.7-7S254.1,27.9,249.6,27.9Z"/>
                        <path d="M318.5,52.5l-.4,1.5c-.7,2.7-1.6,3.7-7.4,3.7h-1.1c-4.2,0-5-1-5-6.8V47.2c6.3,0,6.8.6,6.8,4.8h1.5V39.9h-1.5c0,4.2-.5,4.8-6.8,4.8V35.6h4.5c5.8,0,6.7,1,7.4,3.7l.4,1.5h1.3l-.6-7.6H293.7v1.5c4.6,0,5.3,1,5.3,6.8V51.9c0,5.3-.6,6.6-4.2,6.8-3.4-.5-5.8-2.4-9-5.9l-3.7-4.1c5-.9,7.7-4.1,7.7-7.6,0-4.5-3.8-7.9-11-7.9H264.5v1.5c4.6,0,5.3,1,5.3,6.8V51.9c0,5.8-.7,6.8-5.3,6.8v1.5h16.2V58.7c-4.6,0-5.3-1.1-5.3-6.8V49h1.4l9,11.3h33.4l.5-7.6-1.2-.2Zm-43.1-6.2V35.5h3.3c3.7,0,5.3,2.5,5.3,5.5,0,2.8-1.6,5.4-5.3,5.4l-3.3-.1Z"/>
                        <path d="M335,61.2a11.54,11.54,0,0,1-7.3-2.8,6.08,6.08,0,0,0-1.3,2.8h-1.5V50h1.6c.7,5.4,4.4,8.5,9.2,8.5,2.6,0,4.7-1.5,4.7-3.9,0-2.1-1.9-3.7-5.2-5.3l-4.6-2.2c-3.3-1.5-5.7-4.2-5.7-7.8,0-3.9,3.7-7.2,8.7-7.2a10.05,10.05,0,0,1,6.3,2.1A4.93,4.93,0,0,0,341,32h1.5v9.6h-1.7c-.6-3.8-2.7-7-7-7-2.3,0-4.4,1.3-4.4,3.3s1.7,3.3,5.6,5l4.5,2.2c3.9,1.9,5.5,4.9,5.5,7.3C344.9,57.6,340.5,61.2,335,61.2Z"/>
                        <path d="M360.1,61.2a11.54,11.54,0,0,1-7.3-2.8,6.08,6.08,0,0,0-1.3,2.8H350V50h1.6c.7,5.4,4.4,8.5,9.2,8.5,2.6,0,4.7-1.5,4.7-3.9,0-2.1-1.9-3.7-5.2-5.3l-4.6-2.2c-3.3-1.5-5.7-4.2-5.7-7.8,0-3.9,3.7-7.2,8.7-7.2a10.05,10.05,0,0,1,6.3,2.1,4.93,4.93,0,0,0,1.1-2.2h1.5v9.6h-1.7c-.6-3.8-2.7-7-7-7-2.3,0-4.4,1.3-4.4,3.3s1.7,3.3,5.6,5l4.5,2.2c3.9,1.9,5.5,4.9,5.5,7.3C370,57.6,365.5,61.2,360.1,61.2Z"/>
                        <path d="M6,42A35.93,35.93,0,0,0,26.3,74.4L9.1,27.3A36.19,36.19,0,0,0,6,42Z"/>
                        <path d="M66.3,40.2a18.43,18.43,0,0,0-3-9.9c-1.8-3-3.5-5.5-3.5-8.4a6.23,6.23,0,0,1,6-6.4h.5A35.67,35.67,0,0,0,42,6,36.2,36.2,0,0,0,11.9,22.2h2.3c3.8,0,9.6-.5,9.6-.5a1.5,1.5,0,0,1,.2,3s-2,.2-4.1.3L33,64l7.9-23.6L35.3,25c-1.9-.1-3.8-.3-3.8-.3-1.9-.1-1.7-3.1.2-3,0,0,5.9.5,9.5.5,3.8,0,9.6-.5,9.6-.5a1.5,1.5,0,0,1,.2,3s-2,.2-4.1.3l13,38.7,3.6-12C65.1,46.8,66.3,43.3,66.3,40.2Z"/>
                        <path d="M42.6,45.1,31.8,76.5A37.51,37.51,0,0,0,42,78a36.84,36.84,0,0,0,12-2c-.1-.2-.2-.3-.3-.5Z"/>
                        <path d="M73.6,24.7a21.75,21.75,0,0,1,.2,3.7,34.62,34.62,0,0,1-2.7,12.9l-11,31.8A36,36,0,0,0,73.6,24.7Z"/>
                        <path d="M42,0A42,42,0,1,0,84,42,42.06,42.06,0,0,0,42,0Zm0,82.1A40.05,40.05,0,1,1,82,42,40.16,40.16,0,0,1,42,82.1Z"/>
                    </svg>

                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-dark height-8 mx-3 mt-4" viewBox="0 0 165.96 23.83">
                        <path d="M6.18,12.67l9.36-9.35a3.37,3.37,0,0,1,4.76,0l.73.73,1.45-1.46-.72-.73a5.44,5.44,0,0,0-7.69,0L4.72,11.21Z"/>
                        <path d="M18.67,6.4,17.22,4.94,7.86,14.3A3.37,3.37,0,0,1,3.09,9.53h0l8.08-8.07L9.71,0,1.64,8.07a5.43,5.43,0,0,0,7.59,7.78l.09-.09ZM28,8.07a5.45,5.45,0,0,0-7.68,0L11,17.43l1.46,1.46,9.36-9.36a3.37,3.37,0,1,1,4.75,4.77l-8.06,8.07,1.46,1.46L28,15.76A5.44,5.44,0,0,0,28,8.07Z"/>
                        <path d="M23.44,11.17l-9.36,9.35a3.37,3.37,0,0,1-4.76,0l-.73-.73L7.13,21.25l.73.73a5.45,5.45,0,0,0,7.68,0l9.36-9.36Z"/>
                        <path d="M38.13,14.43a2.19,2.19,0,0,0,.84,1.4,2.89,2.89,0,0,0,1.76.5,2.49,2.49,0,0,0,1.66-.5,1.62,1.62,0,0,0,.58-1.3,1.26,1.26,0,0,0-.15-.66,1.3,1.3,0,0,0-.44-.46,2.61,2.61,0,0,0-.69-.34l-.92-.27-.88-.24A7.81,7.81,0,0,1,38.47,12a4.24,4.24,0,0,1-1.07-.71,2.74,2.74,0,0,1-.67-1,3.1,3.1,0,0,1-.23-1.24,3.08,3.08,0,0,1,.3-1.37,2.92,2.92,0,0,1,.84-1.07A3.86,3.86,0,0,1,39,6a5.4,5.4,0,0,1,1.74-.26,4.46,4.46,0,0,1,2.9.87,3.42,3.42,0,0,1,1.26,2.32l-2,.16a2,2,0,0,0-.72-1.15,2.48,2.48,0,0,0-1.52-.41,2.16,2.16,0,0,0-1.41.42A1.32,1.32,0,0,0,38.7,9a1.36,1.36,0,0,0,.15.63,1.54,1.54,0,0,0,.43.45,3.14,3.14,0,0,0,.69.33c.26.1.57.2.91.31l.84.27a14.64,14.64,0,0,1,1.43.51,4.33,4.33,0,0,1,1.08.67,2.62,2.62,0,0,1,.68.9,3.27,3.27,0,0,1,.24,1.31,3.53,3.53,0,0,1-.33,1.5,3.39,3.39,0,0,1-.93,1.16,4.26,4.26,0,0,1-1.41.75,5.77,5.77,0,0,1-1.8.26,5.16,5.16,0,0,1-3-.86,3.73,3.73,0,0,1-1.52-2.6ZM53.55,5.77A6.4,6.4,0,0,1,56,6.22a5.1,5.1,0,0,1,1.87,1.27,5.71,5.71,0,0,1,1.21,1.95,7.06,7.06,0,0,1,.43,2.49A6.87,6.87,0,0,1,59,14.46a5.68,5.68,0,0,1-1.29,1.95l1.58,1.85v.57h-2l-1.11-1.35a6.9,6.9,0,0,1-1.25.45,6.11,6.11,0,0,1-1.42.14,6.3,6.3,0,0,1-2.46-.47,5.3,5.3,0,0,1-1.86-1.28,5.71,5.71,0,0,1-1.19-1.94,7.11,7.11,0,0,1-.42-2.46,7.18,7.18,0,0,1,.42-2.48,5.63,5.63,0,0,1,1.2-1.95,5.35,5.35,0,0,1,1.87-1.27A6.13,6.13,0,0,1,53.55,5.77Zm0,1.78a3.63,3.63,0,0,0-1.59.33,3.29,3.29,0,0,0-1.18.91A4.13,4.13,0,0,0,50,10.17a5.68,5.68,0,0,0-.25,1.74A5.4,5.4,0,0,0,50,13.65a4.41,4.41,0,0,0,.75,1.4A3.49,3.49,0,0,0,52,16a3.63,3.63,0,0,0,1.59.33A3.78,3.78,0,0,0,55.14,16a3.38,3.38,0,0,0,1.18-.9,3.91,3.91,0,0,0,.75-1.39,5.7,5.7,0,0,0,.27-1.78,5.78,5.78,0,0,0-.27-1.76,4.08,4.08,0,0,0-.75-1.39,3.41,3.41,0,0,0-1.19-.9A3.66,3.66,0,0,0,53.53,7.55Zm13.79,8.84a2.51,2.51,0,0,0,2-.78,3.44,3.44,0,0,0,.71-2.4V6h2.07v7.28a6.56,6.56,0,0,1-.33,2.2,3.74,3.74,0,0,1-.94,1.5,3.82,3.82,0,0,1-1.5.86,7.47,7.47,0,0,1-4,0A3.72,3.72,0,0,1,63.85,17a3.81,3.81,0,0,1-.94-1.5,6.57,6.57,0,0,1-.34-2.2V6h2.08v7.21a3.49,3.49,0,0,0,.71,2.4A2.51,2.51,0,0,0,67.32,16.39Zm6.84.87L78.36,6h3.12l4.15,11.25v.57h-2L82.4,14.45H77.28l-1.21,3.38H74.15Zm3.74-4.58h3.91l-.65-1.88L80.55,9c-.15-.47-.29-.87-.41-1.22h-.51l-.2.56c-.06.19-.14.39-.22.62l-.27.8c-.1.3-.22.64-.36,1Zm19.33,5.15h-2l-3.61-4.65H90.3v4.65h-2V6H92.1a11.05,11.05,0,0,1,1.72.12,3.53,3.53,0,0,1,1.47.57,3.09,3.09,0,0,1,1.4,2.78,3.77,3.77,0,0,1-.24,1.41,3.06,3.06,0,0,1-.63,1,3.31,3.31,0,0,1-.92.69,5.08,5.08,0,0,1-1.07.39l3.4,4.28ZM90.29,11.4h1.8A3.4,3.4,0,0,0,94,11a1.53,1.53,0,0,0,.68-1.42A1.44,1.44,0,0,0,94,8.17a3.76,3.76,0,0,0-1.86-.39h-1.8ZM99.87,6h7.66V7.81h-5.65v3.1h5.24v1.78h-5.23V16h5.75v1.82H99.87Zm12.36,8.43a2.19,2.19,0,0,0,.84,1.4,2.89,2.89,0,0,0,1.76.5,2.49,2.49,0,0,0,1.66-.5,1.62,1.62,0,0,0,.58-1.3,1.26,1.26,0,0,0-.15-.66,1.3,1.3,0,0,0-.44-.46,2.77,2.77,0,0,0-.69-.34l-.93-.27-.88-.25a7.78,7.78,0,0,1-1.42-.52,4.57,4.57,0,0,1-1.07-.72,2.7,2.7,0,0,1-.67-.94,3.36,3.36,0,0,1,.07-2.62,3,3,0,0,1,.84-1.07,4,4,0,0,1,1.33-.7,5.39,5.39,0,0,1,1.74-.25,4.46,4.46,0,0,1,2.9.86A3.4,3.4,0,0,1,119,8.91l-2,.17a2,2,0,0,0-.71-1.16,2.55,2.55,0,0,0-1.52-.41,2.18,2.18,0,0,0-1.42.42A1.34,1.34,0,0,0,112.8,9a1.15,1.15,0,0,0,.15.63,1.32,1.32,0,0,0,.43.45,2.85,2.85,0,0,0,.68.33l.92.32.84.26c.52.17,1,.34,1.42.52a4.12,4.12,0,0,1,1.08.66,2.53,2.53,0,0,1,.68.91,2.89,2.89,0,0,1,.24,1.3,3.29,3.29,0,0,1-.33,1.51,3.25,3.25,0,0,1-.92,1.16,4.43,4.43,0,0,1-1.41.74,5.87,5.87,0,0,1-1.81.26,5.18,5.18,0,0,1-3-.86,3.73,3.73,0,0,1-1.51-2.59Zm10-8.43h4.32a6.06,6.06,0,0,1,2,.28,3.38,3.38,0,0,1,1.32.77,2.83,2.83,0,0,1,.73,1.16,4.46,4.46,0,0,1,.22,1.44,4,4,0,0,1-.3,1.61,3,3,0,0,1-.88,1.13,3.88,3.88,0,0,1-1.41.66,6.82,6.82,0,0,1-1.86.23h-2.09v4.55H122.2Zm2.06,5.5h2a5.63,5.63,0,0,0,1-.09,2.38,2.38,0,0,0,.79-.3,1.5,1.5,0,0,0,.52-.57,1.83,1.83,0,0,0,.19-.89,1.87,1.87,0,0,0-.19-.9,1.46,1.46,0,0,0-.52-.58,2.18,2.18,0,0,0-.78-.3,4.15,4.15,0,0,0-1-.09h-2Zm7.12,5.76L135.57,6h3.12l4.15,11.25v.57h-2l-1.18-3.38h-5.14l-1.19,3.38h-1.93Zm3.74-4.58H139l-.65-1.88c-.25-.73-.45-1.32-.6-1.79l-.42-1.22h-.52l-.2.56-.22.62-.27.8c-.1.3-.22.64-.37,1Zm20.29,2a6,6,0,0,1-.74,1.3,4.81,4.81,0,0,1-1.09,1.07,5.26,5.26,0,0,1-1.49.72,6.4,6.4,0,0,1-1.89.27,6.17,6.17,0,0,1-2.4-.46,5.39,5.39,0,0,1-1.85-1.28,5.69,5.69,0,0,1-1.18-1.94,7.08,7.08,0,0,1-.41-2.45,7.08,7.08,0,0,1,.41-2.45A5.76,5.76,0,0,1,146,7.53a5.45,5.45,0,0,1,1.85-1.3,5.92,5.92,0,0,1,2.42-.46,5.59,5.59,0,0,1,3.25.9,4.93,4.93,0,0,1,1.86,2.41l-2.1.41A3.45,3.45,0,0,0,152,8.06a3.72,3.72,0,0,0-3.41-.18,3.36,3.36,0,0,0-1.16.92,4.12,4.12,0,0,0-.71,1.4,5.62,5.62,0,0,0-.25,1.73,5.66,5.66,0,0,0,.25,1.73A4.29,4.29,0,0,0,147.5,15a3.51,3.51,0,0,0,1.19.9,3.77,3.77,0,0,0,1.58.33,3.06,3.06,0,0,0,1.9-.56,4,4,0,0,0,1.17-1.37ZM158.18,6h7.66V7.81H160.2v3.1h5.25v1.78h-5.24V16H166v1.82h-7.78Z"/>
                    </svg>

                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-dark height-8 mx-3 mt-4" viewBox="0 0 512 193.24">
                        <path d="M352.8.08a76.45,76.45,0,0,0-15.2.8l64,96-64,94.4s28.8,4.8,41.6-8c8-8,12.8-16,40-54.4,4.8-8,9.6,0,9.6,0,24,32,30.4,44.8,41.6,54.4,14.4,11.2,41.6,8,41.6,8l-64-94.4,62.4-96s-27.2-4.8-40,8c-9.6,9.6-19.2,22.4-41.6,56,0,0-4.8,8-9.6,0-22.4-32-32-46.4-41.6-56C371.2,2.48,361.2.48,352.8.08ZM6.7.78A49.5,49.5,0,0,0,0,1L51.2,193s16,0,24-3.2c11.2-4.8,16-9.6,22.4-35.2,6.4-22.4,22.4-91.2,24-96,3.2-11.2,8-11.2,11.2,0,1.6,4.8,17.6,72,24,96,6.4,25.6,11.2,30.4,22.4,35.2,9.6,4.8,25.6,3.2,25.6,3.2L256,1c-17.6-1.6-38.4,8-40,27.2l-27.2,102.4-22.4-83.2c-4.8-24-16-36.8-36.8-36.8s-30.4,11.2-36.8,36.8l-22.4,83.2L43.2,28.08C39,9.88,22.6,1.48,6.7.78Zm306.9.1s-12.8,0-20.8,3.2c-9.6,4.8-12.8,14.4-12.8,38.4a41.33,41.33,0,0,1,12.8-8C315.2,26.48,313.6,10.48,313.6.88Zm-1.6,32v0Zm0,1.2c-.9,1.6-4,4.5-9.6,6.8-4.8,3.2-9.6,4.8-14.4,6.4-11.2,4.8-9.6,11.2-9.6,27.2v118.4s12.8,1.6,20.8-3.2c11.2-4.8,12.8-11.2,12.8-35.2V34.08Z"/>
                    </svg>

                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-dark height-8 mx-3 mt-4" viewBox="0 0 214.13 61">
                        <path d="M46.38,11.55a.6.6,0,0,0-.53-.49L41.36,11,37.44,7.16A1.47,1.47,0,0,0,36.13,7l-1.8.56a12.19,12.19,0,0,0-.86-2.11C32.2,3,30.34,1.73,28.09,1.72h0c-.16,0-.31,0-.47,0s-.13-.16-.2-.23A4.74,4.74,0,0,0,23.66,0c-2.9.09-5.8,2.19-8.14,5.91a23.38,23.38,0,0,0-3.27,8.47L6.53,16.16c-1.69.53-1.74.58-2,2.17C4.41,19.53,0,53.61,0,53.61L36.94,60l16-4S46.42,11.86,46.38,11.55ZM27.68,9.61l-6.16,1.91a14.5,14.5,0,0,1,3.11-6A6.31,6.31,0,0,1,26.72,4,13.76,13.76,0,0,1,27.68,9.61Zm-4-7.67a3,3,0,0,1,1.75.45,8.61,8.61,0,0,0-2.26,1.76,17,17,0,0,0-3.82,8l-5.07,1.57C15.32,9.07,19.24,2.07,23.72,1.94Zm-5.65,26.6c.19,3.11,8.38,3.79,8.84,11.08.36,5.73-3,9.65-7.95,10a11.91,11.91,0,0,1-9.12-3.1l1.25-5.3s3.26,2.46,5.87,2.29A2.31,2.31,0,0,0,19.21,41c-.26-4.06-6.92-3.82-7.34-10.49-.36-5.61,3.33-11.3,11.46-11.81a10,10,0,0,1,4.74.6l-1.86,7a10.68,10.68,0,0,0-4.54-.79C18.07,25.69,18,28,18.07,28.54ZM29.61,9a15.74,15.74,0,0,0-.88-5.29c2.21.42,3.29,2.91,3.75,4.4Z"/>
                        <path d="M74,33.86c-1.84-1-2.78-1.84-2.78-3,0-1.48,1.31-2.42,3.36-2.42a12,12,0,0,1,4.52,1l1.69-5.16s-1.55-1.21-6.1-1.21C68.38,23.08,64,26.71,64,31.81c0,2.89,2.05,5.1,4.78,6.68,2.21,1.26,3,2.16,3,3.47s-1.1,2.47-3.15,2.47a14.19,14.19,0,0,1-6-1.58L60.89,48A13.65,13.65,0,0,0,68,49.8c6.52,0,11.2-3.21,11.2-9C79.24,37.7,76.87,35.49,74,33.86Z"/>
                        <path d="M100,23a9.8,9.8,0,0,0-7.67,3.84l-.11-.05L95,12.25H87.76L80.71,49.32H88l2.42-12.67c.94-4.79,3.42-7.73,5.73-7.73,1.63,0,2.26,1.11,2.26,2.68a16.62,16.62,0,0,1-.31,3.21L95.33,49.33h7.26l2.84-15A26.72,26.72,0,0,0,106,29.6C106,25.5,103.8,23,100,23Z"/>
                        <path d="M122.36,23c-8.73,0-14.51,7.89-14.51,16.67,0,5.63,3.47,10.15,10,10.15,8.57,0,14.36-7.68,14.36-16.67C132.2,28,129.15,23,122.36,23Zm-3.57,21.25c-2.47,0-3.53-2.11-3.53-4.74,0-4.15,2.16-10.93,6.1-10.93,2.58,0,3.42,2.2,3.42,4.36C124.78,37.44,122.63,44.28,118.79,44.28Z"/>
                        <path d="M150.76,23c-4.9,0-7.68,4.31-7.68,4.31H143l.42-3.89H137c-.31,2.63-.89,6.63-1.47,9.62l-5,26.56h7.26l2-10.73h.16a8.43,8.43,0,0,0,4.26,1c8.52,0,14.09-8.73,14.09-17.57C158.23,27.4,156.07,23,150.76,23Zm-6.94,21.35a4.64,4.64,0,0,1-3-1.05L142,36.55c.84-4.53,3.21-7.53,5.73-7.53,2.21,0,2.9,2.06,2.9,4C150.66,37.7,147.87,44.38,143.82,44.38Z"/>
                        <path d="M158.44,49.32h7.26l4.94-25.71h-7.31Z"/>
                        <path d="M189.1,23.56h-5l.26-1.21c.43-2.48,1.9-4.68,4.32-4.68a7.69,7.69,0,0,1,2.31.36l1.42-5.68a9.6,9.6,0,0,0-4-.63,10.77,10.77,0,0,0-7.09,2.42c-2.48,2.11-3.63,5.16-4.21,8.21l-.21,1.21h-3.37L172.48,29h3.37L172,49.33h7.26L183.11,29h5Z"/>
                        <path d="M206.56,23.61S202,35,200,41.28h-.11c-.14-2-1.79-17.67-1.79-17.67h-7.62l4.36,23.61a1.53,1.53,0,0,1-.15,1.21,12.59,12.59,0,0,1-4,4.37,16,16,0,0,1-4.1,2.05l2,6.15a16.74,16.74,0,0,0,7.1-3.94c3.31-3.11,6.36-7.89,9.51-14.41l8.89-19Z"/>
                    </svg>

                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-dark height-8 mx-3 mt-4"
                         viewBox="0 0 101.94 30.99">
                        <path d="M65.27,8.8a6.23,6.23,0,0,1,5-2.23c4.51,0,7.66,3.66,7.66,8.9s-3.15,9-7.66,9a6.22,6.22,0,0,1-5-2.26v1.88H61V0h4.31Zm4.14,11.81c3,0,4-2.62,4-5.08s-1.06-5.14-4-5.14S65.27,13,65.27,15.53C65.27,18.62,66.9,20.61,69.41,20.61ZM79,24.05V0h4.37V24.05ZM42.08,15v1.64H30c.09,2.44,1.75,3.95,4.35,3.95a5.32,5.32,0,0,0,4.3-2l.09-.12,2.82,2.65-.09.1a9.13,9.13,0,0,1-7.34,3.27c-5.34,0-8.79-3.48-8.79-8.87s3.43-9,8.53-9C38.78,6.6,42.08,10,42.08,15ZM30.14,13.4h7.74a3.63,3.63,0,0,0-3.83-3A3.78,3.78,0,0,0,30.14,13.4ZM59.7,15v1.64H47.62c.09,2.44,1.74,3.95,4.35,3.95a5.3,5.3,0,0,0,4.29-2l.1-.12,2.81,2.65-.08.1a9.16,9.16,0,0,1-7.35,3.27C46.4,24.46,43,21,43,15.59s3.43-9,8.54-9C56.4,6.6,59.7,10,59.7,15Zm-12-1.59H55.5a3.63,3.63,0,0,0-3.83-3A3.79,3.79,0,0,0,47.75,13.4ZM21,7h4.65l-4.68,17.1H16l-3.14-11-3.21,11h-5l0-.11L0,7H4.67l2.6,10.89L10.43,7h4.81l3.15,10.88Zm76.3,0h4.68L92.78,31H88.59l2.6-6.94L84.42,7h4.91l4,11.39Z" style="fill-rule:evenodd"/>
                    </svg>

                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-dark height-8 mx-3 mt-4" viewBox="0 0 364.8 117.21">
                        <rect y="76.81" width="24.9" height="12.5"/>
                        <rect x="37.4" y="76.81" width="24.9" height="12.5"/>
                        <rect y="51.81" width="62.3" height="12.5"/>
                        <rect y="26.91" width="37.4" height="12.5"/>
                        <rect x="49.9" y="26.91" width="12.5" height="12.5"/>
                        <path d="M243.3,26.81c-20,0-30.4,14.2-30.4,31.6S223,90,243.3,90s30.4-14.2,30.4-31.6S263.3,26.81,243.3,26.81Zm15.2,31.7h0c0,11.2-3.8,20.3-15.1,20.3s-15.1-9.1-15.1-20.3h0c0-11.2,3.8-20.3,15.1-20.3s15.1,9,15.1,20.3Z"/>
                        <path d="M153.7,89.21V1.81S165.2.21,166.1,0a2.15,2.15,0,0,1,2.4,2v32.4a30.34,30.34,0,0,1,7.9-5.5,22.77,22.77,0,0,1,10.1-2.1,22.42,22.42,0,0,1,8.9,1.7,16.5,16.5,0,0,1,6.5,4.8,21.94,21.94,0,0,1,4,7.4,31.42,31.42,0,0,1,1.3,9.4v39.1H192.4V50.11c0-3.8-.9-6.7-2.6-8.7s-4.3-3.1-7.8-3.1a16.22,16.22,0,0,0-7.2,1.7,25.24,25.24,0,0,0-6.3,4.7v44.4H153.7Z"/>
                        <path d="M351,90.11c-10.2,0-16.6-5.9-16.6-17V40.41H322.9l2.6-9.1a1.93,1.93,0,0,1,1.7-1.5c1-.1,7.1-1,7.1-1l2.8-17.9s8.3-1.2,9.6-1.4a2.07,2.07,0,0,1,2.4,2.1v17.2h14.5v11.6H349.1v32.3c0,4.4,2.7,6.1,5.3,6.1a13.47,13.47,0,0,0,5.3-1.6,2.07,2.07,0,0,1,2.9,1.3c.4,1.2,2.2,7.7,2.2,7.7A25.13,25.13,0,0,1,351,90.11Z"/>
                        <path d="M314.9,40.51a48.15,48.15,0,0,0-13.4-2.4c-5.2,0-9.4,1.8-9.4,6.2,0,5.5,8.9,7,15,9.3,4.1,1.5,15,4.4,15,16.1,0,14.3-11.9,20.4-24.5,20.4s-20.1-4.7-20.1-4.7,2-6.9,2.4-8.3a2.11,2.11,0,0,1,2.7-1.3,44.54,44.54,0,0,0,15.8,3c6.7,0,10-2.1,10-6.4,0-5.8-9.1-7.6-15.1-9.5-4.1-1.3-15.1-4.3-15.1-17.3,0-12.7,11.2-18.7,23.1-18.7,10.1,0,15.1,2.1,18.8,4,0,0-2.1,7.2-2.4,8.3A2,2,0,0,1,314.9,40.51Z"/>
                        <path d="M147.9,29a2,2,0,0,0-2.3-2,18.57,18.57,0,0,0-10.8,5.1c-4.5-3.5-10.7-5.2-17.7-5.2-14,0-25,6.8-25,21.6,0,8.5,3.6,14.3,9.3,17.8a12.06,12.06,0,0,0-7.1,10.5A9.73,9.73,0,0,0,99.9,86s-9.7,4.7-9.7,14.2c0,12.1,11.1,17,24.7,17,19.6,0,33.1-8.1,33.1-22.9,0-9.1-7-14.2-22.2-14.8-9-.4-14.9-.7-16.4-1.2a4,4,0,0,1-2.9-3.9c0-1.9,1.5-3.7,4-4.9a35.53,35.53,0,0,0,6.7.6c14.1,0,25-6.8,25-21.6a22.85,22.85,0,0,0-1.8-9.4,16.1,16.1,0,0,1,7.6-1.9C147.9,37.11,147.9,30.21,147.9,29Zm-38.7,60.3,14.7.6c8.3.4,10.9,2.2,10.9,6.5,0,5.2-7.2,10.3-17.3,10.3-9.5,0-14.3-3.3-14.3-8.9A9.19,9.19,0,0,1,109.2,89.31Zm7.9-29.7c-6.7,0-11.8-3.5-11.8-11.2s5.2-11.2,11.8-11.2,11.8,3.5,11.8,11.2S123.8,59.61,117.1,59.61Z"/>
                    </svg>
                </div>
            </div>
        </div>

        @if(paymentProcessors())
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
                        <a href="{{ route('pricing') }}" class="btn btn-outline-primary py-2 mt-5">{{ __('Learn more') }}<span class="sr-only"> {{ mb_strtolower(__('Pricing')) }}</span></a>
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

                    <div><a href="{{ config('settings.registration') ? route('register') : route('login') }}" class="btn btn-primary btn-lg font-size-lg mt-5">{{ __('Get started') }}</a></div>
                </div>
            </div>
        @endif
    </div>
@endsection
