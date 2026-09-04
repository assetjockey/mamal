@section('site_title', formatTitle([config('settings.title'), __(config('settings.tagline'))]))

@extends('layouts.app')

@section('content')
<div class="flex-fill">
    <div class="bg-base-0 position-relative py-16 pb-16 pb-md-24">
        <div class="container position-relative pt-sm-12 z-1">
            <h1 class="fs-5xl fw-bold tracking-tight text-center text-break mb-0">
                {{ __('Smart and powerful short links') }}
            </h1>

            <p class="text-muted text-center text-break fs-xl fw-normal mt-6 mb-12">
                {{ __('Stay in control of your links with advanced features for shortening, targeting, and tracking.') }}
            </p>

            <div class="row">
                <div class="col-2 d-none d-lg-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15.95 16" class="position-absolute blur-2xs w-6 h-6 top-2 end-8 rotate-n17"><path d="M10,.42,8.46,0,7.14,5,5.94.48,4.37.9,5.66,5.73,2.44,2.51,1.29,3.66,4.82,7.19.42,6,0,7.59,4.81,8.87a2.92,2.92,0,0,1-.09-.73,3.26,3.26,0,1,1,6.52,0,3.55,3.55,0,0,1-.08.73L15.52,10,16,8.47l-4.83-1.3L15.52,6,15.1,4.42l-4.83,1.3,3.22-3.23L12.34,1.34,8.86,4.83Z" style="fill:#f97316"/><path d="M11.15,8.89a3.2,3.2,0,0,1-.81,1.49l3.17,3.17,1.15-1.15Z" style="fill:#f97316"/><path d="M10.31,10.41a3.3,3.3,0,0,1-1.46.87L10,15.57l1.58-.42Z" style="fill:#f97316"/><path d="M8.79,11.29a3.1,3.1,0,0,1-.81.1,3.58,3.58,0,0,1-.87-.11L6,15.58,7.53,16Z" style="fill:#f97316"/><path d="M7.06,11.26a3.18,3.18,0,0,1-1.43-.87L2.45,13.56,3.6,14.71Z" style="fill:#f97316"/><path d="M5.6,10.36a3.23,3.23,0,0,1-.79-1.48L.43,10.06l.42,1.57Z" style="fill:#f97316"/></svg>

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" class="position-absolute blur-2xs w-6.5 h-6.5 top-n6 end-24 rotate-22"><polygon points="0 0 50 0 0 50 0 0" style="fill:#009cea"/><polygon points="0 50 50 50 0 100 0 50" style="fill:#009cea"/><polygon points="50 0 100 0 50 50 50 0" style="fill:#009cea"/><circle cx="75" cy="75" r="25" style="fill:#009cea"/></svg>

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 45 45" class="position-absolute blur-2xs w-5 h-5 top-10 end-18 rotate-n5"><path d="M22.5,11.25A11.25,11.25,0,0,1,11.25,22.5H0V11.25a11.25,11.25,0,0,1,22.5,0Z" style="fill:#f5718b"/><path d="M22.5,33.75A11.25,11.25,0,0,1,33.75,22.5H45V33.75a11.25,11.25,0,0,1-22.5,0Z" style="fill:#f5718b"/><path d="M0,33.75A11.25,11.25,0,0,0,11.25,45H22.5V33.75a11.25,11.25,0,0,0-22.5,0Z" style="fill:#f5718b"/><path d="M45,11.25A11.25,11.25,0,0,0,33.75,0H22.5V11.25a11.25,11.25,0,0,0,22.5,0Z" style="fill:#f5718b"/></svg>
                </div>

                @if(config('settings.short_guest'))
                    <div class="col-12 col-lg-8">
                        <div class="form-group mb-0" id="short-form-container"@if($link) style="display: none;"@endif>
                            <form action="{{ route('guest') }}" method="post" enctype="multipart/form-data" id="short-form">
                                @csrf
                                <div class="row mx-n1">
                                    <div class="col-12 col-sm px-1">
                                        <input type="text" dir="ltr" autocomplete="off" autocapitalize="none" spellcheck="false" name="url" class="form-control form-control-lg fs-lg{{ $errors->has('url') || $errors->has('domain_id') || $errors->has(captchaFieldName()) ? ' is-invalid' : '' }}" placeholder="{{ __('https://example.com') }}" autofocus>
                                        @if ($errors->has('url'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->first('url') }}</strong>
                                            </span>
                                        @endif

                                        @if ($errors->has('domain_id'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->first('domain_id') }}</strong>
                                            </span>
                                        @endif

                                        @if ($errors->has(captchaFieldName()))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ __($errors->first(captchaFieldName())) }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-12 col-sm-auto mt-4 mt-sm-0 px-1">
                                        @if(config('settings.captcha_driver'))
                                            <x-captcha-js lang="{{ __('lang_code') }}"></x-captcha-js>

                                            @include('shared.captcha', ['id' => 'short-form'])

                                            <x-captcha-button data-callback="{{ (config('settings.captcha_driver') == 'turnstile' ? '' : 'captchaFormSubmit') }}" form-id="short-form" class="btn btn-primary btn-lg btn-block fs-lg" data-sitekey="{{ config('settings.captcha_site_key') }}" data-theme="{{ (config('settings.dark_mode') == 1 ? 'dark' : 'light') }}">{{ __('Shorten') }}</x-captcha-button>
                                        @else
                                            <button class="btn btn-primary btn-lg btn-block fs-lg position-relative" type="submit" data-button-loader>
                                                <span class="position-absolute top-0 end-0 bottom-0 start-0 d-flex align-items-center justify-content-center">
                                                    <span class="d-none spinner-border spinner-border-sm w-4 h-4" role="status"></span>
                                                </span>
                                                <span class="spinner-text">{{ __('Shorten') }}</span>&#8203;
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <input type="hidden" name="domain_id" value="{{ $defaultDomain }}">
                            </form>
                        </div>

                        @include('home.link')
                    </div>
                @else
                    <div class="col-12 col-lg-8">
                        <div class="row justify-content-md-center {{ !config('settings.report_guest') ? 'm-n2' : '' }}">
                            <div class="col-12 col-md-auto p-2">
                                <a href="{{ config('settings.registration') ? route('register') : route('login') }}" class="btn btn-primary btn-lg btn-block fs-lg d-inline-flex align-items-center justify-content-center">{{ __('Get started') }}</a>
                            </div>
                            <div class="col-12 col-md-auto p-2">
                                <a href="#features" class="btn btn-outline-primary btn-lg btn-block fs-lg d-inline-flex align-items-center justify-content-center">{{ __('Learn more') }}</a>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-2 d-none d-lg-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" class="position-absolute blur-2xs w-6 h-6 top-2 start-8 rotate-7"><path d="M8.55,3.6A20,20,0,0,0,4.71,7.11c4.58-.42,10.42.27,17.18,3.66,7.23,3.61,13,3.73,17.1,3a20.14,20.14,0,0,0-1.37-3.2C33,11,27,10.36,20.11,6.9A29.64,29.64,0,0,0,8.55,3.6ZM34.91,6.67A20,20,0,0,0,15,.64a37,37,0,0,1,6.93,2.68A28.82,28.82,0,0,0,34.91,6.67Zm5,11c-4.89,1-11.65.77-19.75-3.29C12.53,10.56,6.5,10.6,2.43,11.51l-.61.14A19.82,19.82,0,0,0,.56,15.29c.32-.08.66-.17,1-.24C6.5,14,13.47,14,21.89,18.21,29.47,22,35.5,22,39.57,21.05L40,21c0-.31,0-.63,0-.95A20.66,20.66,0,0,0,39.86,17.63Zm-.54,7.54c-4.84.85-11.4.52-19.21-3.38C12.53,18,6.5,18.05,2.43,19A19.75,19.75,0,0,0,0,19.66V20a20,20,0,0,0,39.32,5.17Z" style="fill:#10d08f;fill-rule:evenodd"/></svg>

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" class="position-absolute blur-2xs w-6.5 h-6.5 top-n4 start-24 rotate-22"><path d="M20,40A20,20,0,1,0,0,20,20,20,0,0,0,20,40ZM26.24,9.32c.3-1.08-.74-1.72-1.7-1L11.19,17.79c-1,.74-.87,2.21.25,2.21H15v0H21.8l-5.58,2-2.46,8.74c-.3,1.08.74,1.72,1.7,1l13.35-9.51c1-.74.87-2.21-.25-2.21H23.23Z" style="fill:#f15757;fill-rule:evenodd"/></svg>

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" class="position-absolute blur-2xs w-5 h-5 top-10 start-20 rotate-n20"><path d="M20,40A20,20,0,1,0,0,20,20,20,0,0,0,20,40Zm3.09-24.55a4.37,4.37,0,1,0-6.18,0L20,18.54Zm1.46,7.64a4.37,4.37,0,1,0,0-6.18L21.46,20Zm-1.46,7.63a4.37,4.37,0,0,0,0-6.17L20,21.46l-3.09,3.09a4.37,4.37,0,0,0,6.18,6.17ZM9.28,23.09a4.37,4.37,0,1,1,6.17-6.18L18.54,20l-3.09,3.09A4.37,4.37,0,0,1,9.28,23.09Z" style="fill:#946fff;fill-rule:evenodd"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="scroll-mt-18" id="features"></div>

    <div class="bg-base-1">
        <div class="container py-16 py-md-24">
            <div class="text-center">
                <h2 class="fs-3xl fw-bold tracking-tight m-0">{{ __('Features') }}</h2>
                <div class="mx-auto mt-4 mb-12">
                    <p class="text-muted fw-normal fs-lg mb-0">{{ __('Enhance your links with advanced customizations and detailed statistics.') }}</p>
                </div>
            </div>

            <div class="row m-n2 m-sm-n4">
                <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-4">
                    <div class="card border-0 shadow-sm h-full rounded-xl">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex w-8 h-8 position-relative align-items-center justify-content-center flex-shrink-0 me-4" style="color: #fb7185;">
                                <div class="position-absolute opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg" style="background: #fb7185;"></div>
                                @include('icons.abc', ['class' => 'fill-current w-4 h-4'])
                            </div>
                            <div class="d-block w-full"><div class="d-inline-block fw-bold">{{ __('Custom aliases') }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-4">
                    <div class="card border-0 shadow-sm h-full rounded-xl">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex w-8 h-8 position-relative align-items-center justify-content-center flex-shrink-0 me-4" style="color: #f472b6;">
                                <div class="position-absolute opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg" style="background: #f472b6;"></div>
                                @include('icons.website', ['class' => 'fill-current w-4 h-4'])
                            </div>
                            <div class="d-block w-full"><div class="d-inline-block fw-bold">{{ __('Branded domains') }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-4">
                    <div class="card border-0 shadow-sm h-full rounded-xl">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex w-8 h-8 position-relative align-items-center justify-content-center flex-shrink-0 me-4" style="color: #e879f9;">
                                <div class="position-absolute opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg" style="background: #e879f9;"></div>
                                @include('icons.bar-chart', ['class' => 'fill-current w-4 h-4'])
                            </div>
                            <div class="d-block w-full"><div class="d-inline-block fw-bold">{{ __('Statistics') }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-4">
                    <div class="card border-0 shadow-sm h-full rounded-xl">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex w-8 h-8 position-relative align-items-center justify-content-center flex-shrink-0 me-4" style="color: #c084fc;">
                                <div class="position-absolute opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg" style="background: #c084fc;"></div>
                                @include('icons.filter-center-focus', ['class' => 'fill-current w-4 h-4'])
                            </div>
                            <div class="d-block w-full"><div class="d-inline-block fw-bold">{{ __('Retargeting pixels') }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-4">
                    <div class="card border-0 shadow-sm h-full rounded-xl">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex w-8 h-8 position-relative align-items-center justify-content-center flex-shrink-0 me-4" style="color: #a78bfa;">
                                <div class="position-absolute opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg" style="background: #a78bfa;"></div>
                                @include('icons.cached', ['class' => 'fill-current w-4 h-4'])
                            </div>
                            <div class="d-block w-full"><div class="d-inline-block fw-bold">{{ __('Traffic splitting') }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-4">
                    <div class="card border-0 shadow-sm h-full rounded-xl">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex w-8 h-8 position-relative align-items-center justify-content-center flex-shrink-0 me-4" style="color: #818cf8;">
                                <div class="position-absolute opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg" style="background: #818cf8;"></div>
                                @include('icons.password', ['class' => 'fill-current w-4 h-4'])
                            </div>
                            <div class="d-block w-full"><div class="d-inline-block fw-bold">{{ __('Password protection') }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-4">
                    <div class="card border-0 shadow-sm h-full rounded-xl">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex w-8 h-8 position-relative align-items-center justify-content-center flex-shrink-0 me-4" style="color: #60a5fa;">
                                <div class="position-absolute opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg" style="background: #60a5fa;"></div>
                                @include('icons.flag', ['class' => 'fill-current w-4 h-4'])
                            </div>
                            <div class="d-block w-full"><div class="d-inline-block fw-bold">{{ __('Country targeting') }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-4">
                    <div class="card border-0 shadow-sm h-full rounded-xl">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex w-8 h-8 position-relative align-items-center justify-content-center flex-shrink-0 me-4" style="color: #38bdf8;">
                                <div class="position-absolute opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg" style="background: #38bdf8;"></div>
                                @include('icons.devices', ['class' => 'fill-current w-4 h-4'])
                            </div>
                            <div class="d-block w-full"><div class="d-inline-block fw-bold">{{ __('Platform targeting') }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-4">
                    <div class="card border-0 shadow-sm h-full rounded-xl">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex w-8 h-8 position-relative align-items-center justify-content-center flex-shrink-0 me-4" style="color: #06b6d4;">
                                <div class="position-absolute opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg" style="background: #06b6d4;"></div>
                                @include('icons.language', ['class' => 'fill-current w-4 h-4'])
                            </div>
                            <div class="d-block w-full"><div class="d-inline-block fw-bold">{{ __('Language targeting') }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-4">
                    <div class="card border-0 shadow-sm h-full rounded-xl">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex w-8 h-8 position-relative align-items-center justify-content-center flex-shrink-0 me-4" style="color: #2dd4bf;">
                                <div class="position-absolute opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg" style="background: #2dd4bf;"></div>
                                @include('icons.label', ['class' => 'fill-current w-4 h-4'])
                            </div>
                            <div class="d-block w-full"><div class="d-inline-block fw-bold">{{ __('UTM builder') }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-4">
                    <div class="card border-0 shadow-sm h-full rounded-xl">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex w-8 h-8 position-relative align-items-center justify-content-center flex-shrink-0 me-4" style="color: #34d399;">
                                <div class="position-absolute opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg" style="background: #34d399;"></div>
                                @include('icons.date-range', ['class' => 'fill-current w-4 h-4'])
                            </div>
                            <div class="d-block w-full"><div class="d-inline-block fw-bold">{{ __('Expiration dates') }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4 p-2 p-sm-4">
                    <div class="card border-0 shadow-sm h-full rounded-xl">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex w-8 h-8 position-relative align-items-center justify-content-center flex-shrink-0 me-4" style="color: #4ade80;">
                                <div class="position-absolute opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg" style="background: #4ade80;"></div>
                                @include('icons.qr', ['class' => 'fill-current w-4 h-4'])
                            </div>
                            <div class="d-block w-full"><div class="d-inline-block fw-bold">{{ __('QR codes') }}</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-base-0 overflow-hidden">
        <div class="container py-16 py-md-24 position-relative z-1">
            <div class="row mx-n12">
                <div class="col-12 col-lg-6 px-12">
                    <div class="row">
                        <div class="col-12 text-center text-lg-start">
                            <h2 class="fs-3xl fw-bold tracking-tight m-0">{{ __('Link management') }}</h2>
                            <div class="mx-auto mt-4">
                                <p class="text-muted fw-normal fs-lg mb-0">{{ __('Complete link management platform to brand, track and share your short links.') }}</p>
                            </div>
                        </div>

                        <div class="col-12 pt-6 mt-6">
                            <div class="d-flex flex-row">
                                <div class="d-flex w-12 h-12 position-relative align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-xl"></div>
                                    @include('icons.link', ['class' => 'fill-current w-6 h-6 text-primary'])
                                </div>
                                <div class="ms-1">
                                    <div class="d-block w-full"><h3 class="fs-xl fw-bold d-inline-block mt-0 mb-1">{{ __('Links') }}</h3></div>
                                    <div class="d-block w-full text-muted">{{ __('Shorten, share, and export your links with our advanced set of features.') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 pt-6 mt-6">
                            <div class="d-flex flex-row">
                                <div class="d-flex w-12 h-12 position-relative align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-xl"></div>
                                    @include('icons.workspaces', ['class' => 'fill-current w-6 h-6 text-primary'])
                                </div>
                                <div class="ms-1">
                                    <div class="d-block w-full"><h3 class="fs-xl fw-bold d-inline-block mt-0 mb-1">{{ __('Spaces') }}</h3></div>
                                    <div class="d-block w-full text-muted">{{ __('Group your links and keep them well organized through custom spaces.') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 pt-6 mt-6">
                            <div class="d-flex flex-row">
                                <div class="d-flex w-12 h-12 position-relative align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-xl"></div>
                                    @include('icons.website', ['class' => 'fill-current w-6 h-6 text-primary'])
                                </div>
                                <div class="ms-1">
                                    <div class="d-block w-full"><h3 class="fs-xl fw-bold d-inline-block mt-0 mb-1">{{ __('Domains') }}</h3></div>
                                    <div class="d-block w-full text-muted">{{ __('Brand your links with your domains, inspire trust and increase your click-through rate.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6 position-relative px-12 mt-12 mt-lg-0">
                    <div class="position-relative">
                        <div class="position-absolute top-4 end-n4 bottom-n4 start-4 bg-primary opacity-10 rounded-xl"></div>

                        <div class="position-relative">
                            <div class="position-absolute top-0 end-0 bottom-0 start-0 shadow-lg rounded-xl z-0"></div>
                            <div class="card position-relative border-0 rounded-xl overflow-hidden cursor-default z-1">
                                <div class="card-body px-5 py-4">
                                    <div class="row align-items-center">
                                        <div class="col d-flex text-truncate">
                                            <div class="text-truncate">
                                                <div class="d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-4" viewBox="0 0 15.95 16"><path d="M10,.42,8.46,0,7.14,5,5.94.48,4.37.9,5.66,5.73,2.44,2.51,1.29,3.66,4.82,7.19.42,6,0,7.59,4.81,8.87a2.92,2.92,0,0,1-.09-.73,3.26,3.26,0,1,1,6.52,0,3.55,3.55,0,0,1-.08.73L15.52,10,16,8.47l-4.83-1.3L15.52,6,15.1,4.42l-4.83,1.3,3.22-3.23L12.34,1.34,8.86,4.83Z" style="fill:#f97316"/><path d="M11.15,8.89a3.2,3.2,0,0,1-.81,1.49l3.17,3.17,1.15-1.15Z" style="fill:#f97316"/><path d="M10.31,10.41a3.3,3.3,0,0,1-1.46.87L10,15.57l1.58-.42Z" style="fill:#f97316"/><path d="M8.79,11.29a3.1,3.1,0,0,1-.81.1,3.58,3.58,0,0,1-.87-.11L6,15.58,7.53,16Z" style="fill:#f97316"/><path d="M7.06,11.26a3.18,3.18,0,0,1-1.43-.87L2.45,13.56,3.6,14.71Z" style="fill:#f97316"/><path d="M5.6,10.36a3.23,3.23,0,0,1-.79-1.48L.43,10.06l.42,1.57Z" style="fill:#f97316"/></svg>

                                                    <div class="text-truncate me-2">
                                                        <div class="text-primary text-truncate" dir="ltr">example.com/b6vxe</div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <div class="w-4 flex-shrink-0 me-4"></div>
                                                    <div class="text-muted text-truncate small">
                                                        <span class="text-muted">Consectetur - Adipiscing</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="row mx-n1">
                                                <div class="col px-1">
                                                    <div class="btn btn-sm text-primary d-flex align-items-center cursor-default">
                                                        @include('icons.copy-link', ['class' => 'fill-current w-4 h-4'])&#8203;
                                                    </div>
                                                </div>
                                                <div class="col px-1">
                                                    <div class="btn text-primary btn-sm d-flex align-items-center cursor-default">
                                                        @include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="position-relative mt-4">
                            <div class="position-absolute top-0 end-0 bottom-0 start-0 shadow-lg rounded-xl z-0"></div>
                            <div class="card position-relative border-0 rounded-xl overflow-hidden cursor-default z-1">
                                <div class="card-body px-5 py-4">
                                    <div class="row align-items-center">
                                        <div class="col d-flex text-truncate">
                                            <div class="text-truncate">
                                                <div class="d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-4" viewBox="0 0 100 100"><polygon points="0 0 50 0 0 50 0 0" style="fill:#009cea"/><polygon points="0 50 50 50 0 100 0 50" style="fill:#009cea"/><polygon points="50 0 100 0 50 50 50 0" style="fill:#009cea"/><circle cx="75" cy="75" r="25" style="fill:#009cea"/></svg>

                                                    <div class="text-truncate me-2">
                                                        <div class="text-primary text-truncate" dir="ltr">example.org/e362o</div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <div class="w-4 flex-shrink-0 me-4"></div>
                                                    <div class="text-muted text-truncate small">
                                                        <span class="text-muted">Fusce - Vehicula</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="row mx-n1">
                                                <div class="col px-1">
                                                    <div class="btn btn-sm text-primary d-flex align-items-center cursor-default">
                                                        @include('icons.copy-link', ['class' => 'fill-current w-4 h-4'])&#8203;
                                                    </div>
                                                </div>
                                                <div class="col px-1">
                                                    <div class="btn text-primary btn-sm d-flex align-items-center cursor-default">
                                                        @include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="position-relative mt-4">
                            <div class="position-absolute top-0 end-0 bottom-0 start-0 shadow-lg rounded-xl z-0"></div>
                            <div class="card position-relative border-0 rounded-xl overflow-hidden cursor-default z-1">
                                <div class="card-body px-5 py-4">
                                    <div class="row align-items-center">
                                        <div class="col d-flex text-truncate">
                                            <div class="text-truncate">
                                                <div class="d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-4" viewBox="0 0 40 40"><path d="M8.55,3.6A20,20,0,0,0,4.71,7.11c4.58-.42,10.42.27,17.18,3.66,7.23,3.61,13,3.73,17.1,3a20.14,20.14,0,0,0-1.37-3.2C33,11,27,10.36,20.11,6.9A29.64,29.64,0,0,0,8.55,3.6ZM34.91,6.67A20,20,0,0,0,15,.64a37,37,0,0,1,6.93,2.68A28.82,28.82,0,0,0,34.91,6.67Zm5,11c-4.89,1-11.65.77-19.75-3.29C12.53,10.56,6.5,10.6,2.43,11.51l-.61.14A19.82,19.82,0,0,0,.56,15.29c.32-.08.66-.17,1-.24C6.5,14,13.47,14,21.89,18.21,29.47,22,35.5,22,39.57,21.05L40,21c0-.31,0-.63,0-.95A20.66,20.66,0,0,0,39.86,17.63Zm-.54,7.54c-4.84.85-11.4.52-19.21-3.38C12.53,18,6.5,18.05,2.43,19A19.75,19.75,0,0,0,0,19.66V20a20,20,0,0,0,39.32,5.17Z" style="fill:#10d08f;fill-rule:evenodd"/></svg>

                                                    <div class="text-truncate me-2">
                                                        <div class="text-primary text-truncate" dir="ltr">example.com/gmyux</div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <div class="w-4 flex-shrink-0 me-4"></div>
                                                    <div class="text-muted text-truncate small">
                                                        <span class="text-muted">Consequat - Elit Ornare</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="row mx-n1">
                                                <div class="col px-1">
                                                    <div class="btn btn-sm text-primary d-flex align-items-center cursor-default">
                                                        @include('icons.copy-link', ['class' => 'fill-current w-4 h-4'])&#8203;
                                                    </div>
                                                </div>
                                                <div class="col px-1">
                                                    <div class="btn text-primary btn-sm d-flex align-items-center cursor-default">
                                                        @include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="position-relative mt-4">
                            <div class="position-absolute top-0 end-0 bottom-0 start-0 shadow-lg rounded-xl z-0"></div>
                            <div class="card position-relative border-0 rounded-xl overflow-hidden cursor-default z-1">
                                <div class="card-body px-5 py-4">
                                    <div class="row align-items-center">
                                        <div class="col d-flex text-truncate">
                                            <div class="text-truncate">
                                                <div class="d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-4" viewBox="0 0 40 40"><path d="M20,40A20,20,0,1,0,0,20,20,20,0,0,0,20,40Zm3.09-24.55a4.37,4.37,0,1,0-6.18,0L20,18.54Zm1.46,7.64a4.37,4.37,0,1,0,0-6.18L21.46,20Zm-1.46,7.63a4.37,4.37,0,0,0,0-6.17L20,21.46l-3.09,3.09a4.37,4.37,0,0,0,6.18,6.17ZM9.28,23.09a4.37,4.37,0,1,1,6.17-6.18L18.54,20l-3.09,3.09A4.37,4.37,0,0,1,9.28,23.09Z" style="fill:#946fff;fill-rule:evenodd"/></svg>

                                                    <div class="text-truncate me-2">
                                                        <div class="text-primary text-truncate" dir="ltr">example.net/qyd8s</div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <div class="w-4 flex-shrink-0 me-4"></div>
                                                    <div class="text-muted text-truncate small">
                                                        <span class="text-muted">Sit - Amet</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="row mx-n1">
                                                <div class="col px-1">
                                                    <div class="btn btn-sm text-primary d-flex align-items-center cursor-default">
                                                        @include('icons.copy-link', ['class' => 'fill-current w-4 h-4'])&#8203;
                                                    </div>
                                                </div>
                                                <div class="col px-1">
                                                    <div class="btn text-primary btn-sm d-flex align-items-center cursor-default">
                                                        @include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="position-relative mt-4">
                            <div class="position-absolute top-0 end-0 bottom-0 start-0 shadow-lg rounded-xl z-0"></div>
                            <div class="card position-relative border-0 rounded-xl overflow-hidden cursor-default z-1">
                                <div class="card-body px-5 py-4">
                                    <div class="row align-items-center">
                                        <div class="col d-flex text-truncate">
                                            <div class="text-truncate">
                                                <div class="d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-4" viewBox="0 0 40 40"><path d="M20,40A20,20,0,1,0,0,20,20,20,0,0,0,20,40ZM26.24,9.32c.3-1.08-.74-1.72-1.7-1L11.19,17.79c-1,.74-.87,2.21.25,2.21H15v0H21.8l-5.58,2-2.46,8.74c-.3,1.08.74,1.72,1.7,1l13.35-9.51c1-.74.87-2.21-.25-2.21H23.23Z" style="fill:#f15757;fill-rule:evenodd"/></svg>

                                                    <div class="text-truncate me-2">
                                                        <div class="text-primary text-truncate" dir="ltr">example.com/bqh6e</div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <div class="w-4 flex-shrink-0 me-4"></div>
                                                    <div class="text-muted text-truncate small">
                                                        <span class="text-muted">Lorem - Ipsum Dolorem</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="row mx-n1">
                                                <div class="col px-1">
                                                    <div class="btn btn-sm text-primary d-flex align-items-center cursor-default">
                                                        @include('icons.copy-link', ['class' => 'fill-current w-4 h-4'])&#8203;
                                                    </div>
                                                </div>
                                                <div class="col px-1">
                                                    <div class="btn text-primary btn-sm d-flex align-items-center cursor-default">
                                                        @include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;
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
        <div class="container py-16 py-md-24 position-relative z-1">
            <div class="row mx-n12">
                <div class="col-12 col-lg-6 px-12 order-1 order-lg-2">
                    <div class="row">
                        <div class="col-12 text-center text-lg-start">
                            <h2 class="fs-3xl fw-bold tracking-tight m-0">{{ __('Statistics') }}</h2>
                            <div class="mx-auto mt-4 mb-6">
                                <p class="text-muted fw-normal fs-lg mb-0">{{ __('Get to know your audience with our detailed statistics and better understand the performance of your links, while also being GDPR, CCPA and PECR compliant.') }}</p>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 mt-6">
                            <div class="d-flex align-items-center">
                                <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                    @include('icons.assesment', ['class' => 'fill-current w-4 h-4'])
                                </div>
                                <div>
                                    <div class="d-block w-full"><div class="d-inline-block fw-bold fs-lg">{{ __('Overview') }}</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 mt-6">
                            <div class="d-flex align-items-center">
                                <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                    @include('icons.link', ['class' => 'fill-current w-4 h-4'])
                                </div>
                                <div>
                                    <div class="d-block w-full"><div class="d-inline-block fw-bold fs-lg">{{ __('Referrers') }}</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 mt-6">
                            <div class="d-flex align-items-center">
                                <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                    @include('icons.flag', ['class' => 'fill-current w-4 h-4'])
                                </div>
                                <div>
                                    <div class="d-block w-full"><div class="d-inline-block fw-bold fs-lg">{{ __('Countries') }}</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 mt-6">
                            <div class="d-flex align-items-center">
                                <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                    @include('icons.business', ['class' => 'fill-current w-4 h-4'])
                                </div>
                                <div>
                                    <div class="d-block w-full"><div class="d-inline-block fw-bold fs-lg">{{ __('Cities') }}</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 mt-6">
                            <div class="d-flex align-items-center">
                                <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                    @include('icons.language', ['class' => 'fill-current w-4 h-4'])
                                </div>
                                <div>
                                    <div class="d-block w-full"><div class="d-inline-block fw-bold fs-lg">{{ __('Languages') }}</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 mt-6">
                            <div class="d-flex align-items-center">
                                <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                    @include('icons.devices', ['class' => 'fill-current w-4 h-4'])
                                </div>
                                <div>
                                    <div class="d-block w-full"><div class="d-inline-block fw-bold fs-lg">{{ __('Platforms') }}</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 mt-6">
                            <div class="d-flex align-items-center">
                                <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                    @include('icons.tab', ['class' => 'fill-current w-4 h-4'])
                                </div>
                                <div>
                                    <div class="d-block w-full"><div class="d-inline-block fw-bold fs-lg">{{ __('Browsers') }}</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 mt-6">
                            <div class="d-flex align-items-center">
                                <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                    @include('icons.devices-other', ['class' => 'fill-current w-4 h-4'])
                                </div>
                                <div>
                                    <div class="d-block w-full"><div class="d-inline-block fw-bold fs-lg">{{ __('Devices') }}</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6 px-12 position-relative order-2 order-lg-1 mt-12 mt-lg-0">
                    <div class="row">
                        <div class="col-12">
                            <div class="position-relative">
                                <div class="position-absolute top-4 end-4 bottom-n4 start-n4 bg-primary opacity-10 rounded-xl"></div>

                                <div class="position-relative">
                                    <div class="position-absolute top-0 end-0 bottom-0 start-0 shadow-lg rounded-xl z-0"></div>
                                    <div class="card position-relative border-0 rounded-xl overflow-hidden cursor-default z-1">
                                        <div class="card-body px-5 py-4">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <div class="d-flex text-truncate align-items-center">
                                                        <div class="d-flex align-items-center me-2"><img src="{{ asset('img/icons/countries/us.svg') }}" class="w-4 h-4" alt="{{ __('United States') }}"></div>
                                                        <div class="text-truncate">
                                                            United States
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-baseline ms-4 text-end">
                                                        <div>
                                                            {{ number_format(12, 0, __('.'), __(',')) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="progress h-1.25 w-full">
                                                    <div class="progress-bar rounded" style="width: 18%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="position-relative mt-4">
                                    <div class="position-absolute top-0 end-0 bottom-0 start-0 shadow-lg rounded-xl z-0"></div>
                                    <div class="card position-relative border-0 rounded-xl overflow-hidden cursor-default z-1">
                                        <div class="card-body px-5 py-4">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <div class="d-flex text-truncate align-items-center">
                                                        <div class="d-flex align-items-center me-2"><img src="{{ asset('img/icons/os/windows.svg') }}" class="w-4 h-4" alt="Windows"></div>
                                                        <div class="text-truncate">
                                                            Windows
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-baseline ms-4 text-end">
                                                        <div>
                                                            {{ number_format(30, 0, __('.'), __(',')) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="progress h-1.25 w-full">
                                                    <div class="progress-bar rounded" style="width: 60%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="position-relative mt-4">
                                    <div class="position-absolute top-0 end-0 bottom-0 start-0 shadow-lg rounded-xl z-0"></div>
                                    <div class="card position-relative border-0 rounded-xl overflow-hidden cursor-default z-1">
                                        <div class="card-body px-5 py-4">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <div class="d-flex text-truncate align-items-center">
                                                        <div class="d-flex align-items-center me-2"><img src="{{ asset('img/icons/browsers/chrome.svg') }}" class="w-4 h-4" alt="Chrome"></div>
                                                        <div class="text-truncate">
                                                            Chrome
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-baseline ms-4 text-end">
                                                        <div>
                                                            {{ number_format(25, 0, __('.'), __(',')) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="progress h-1.25 w-full">
                                                    <div class="progress-bar rounded" style="width: 48%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="position-relative mt-4">
                                    <div class="position-absolute top-0 end-0 bottom-0 start-0 shadow-lg rounded-xl z-0"></div>
                                    <div class="card position-relative border-0 rounded-xl overflow-hidden cursor-default z-1">
                                        <div class="card-body px-5 py-4">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <div class="d-flex text-truncate align-items-center">
                                                        <div class="d-flex align-items-center me-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 40 40"><path d="M20,40A20,20,0,1,0,0,20,20,20,0,0,0,20,40ZM26.24,9.32c.3-1.08-.74-1.72-1.7-1L11.19,17.79c-1,.74-.87,2.21.25,2.21H15v0H21.8l-5.58,2-2.46,8.74c-.3,1.08.74,1.72,1.7,1l13.35-9.51c1-.74.87-2.21-.25-2.21H23.23Z" style="fill:#f15757;fill-rule:evenodd"/></svg>
                                                        </div>

                                                        <div class="d-flex text-truncate">
                                                            <div class="text-truncate" dir="ltr">example.com</div> <span class="text-secondary d-flex align-items-center ms-2"><svg xmlns="http://www.w3.org/2000/svg" class="fill-current w-3 h-3" viewBox="0 0 18 18"><path d="M16,16H2V2H9V0H2A2,2,0,0,0,0,2V16a2,2,0,0,0,2,2H16a2,2,0,0,0,2-2V9H16ZM11,0V2h3.59L4.76,11.83l1.41,1.41L16,3.41V7h2V0Z"></path></svg>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-baseline ms-4 text-end">
                                                        <div>
                                                            {{ number_format(18, 0, __('.'), __(',')) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="progress h-1.25 w-full">
                                                    <div class="progress-bar rounded" style="width: 22%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="position-relative mt-4">
                                    <div class="position-absolute top-0 end-0 bottom-0 start-0 shadow-lg rounded-xl z-0"></div>
                                    <div class="card position-relative border-0 rounded-xl overflow-hidden cursor-default z-1">
                                        <div class="card-body px-5 py-4">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <div class="d-flex text-truncate align-items-center">
                                                        <div class="d-flex align-items-center me-2"><img src="{{ asset('img/icons/devices/desktop.svg') }}" class="w-4 h-4" alt="{{ __('Desktop') }}"></div>
                                                        <div class="text-truncate">
                                                            Desktop
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-baseline ms-4 text-end">
                                                        <div>
                                                            {{ number_format(36, 0, __('.'), __(',')) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="progress h-1.25 w-full">
                                                    <div class="progress-bar rounded" style="width: 66%"></div>
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
        <div class="container position-relative text-center py-16 py-md-24 d-flex flex-column z-1">
            <div class="text-center">
                <h2 class="fs-3xl fw-bold tracking-tight m-0">{{ __('Integrations') }}</h2>
                <div class="mx-auto mt-4 text-center">
                    <p class="text-muted fw-normal fs-lg mb-0">{{ __('Easily integrates with your favorite retargeting platforms.') }}</p>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-center justify-content-lg-between mt-6 mx-n4">
                <div class="bg-base-1 d-flex w-20 h-20 position-relative align-items-center justify-content-center flex-shrink-0 rounded-3xl mx-4 mt-4" data-tooltip="true" title="Google Ads">
                    <img src="{{ asset('img/icons/pixels/' . md5('google-ads')) }}.svg" class="w-8 h-8" alt="Google Ads">
                </div>

                <div class="bg-base-1 d-flex w-20 h-20 position-relative align-items-center justify-content-center flex-shrink-0 rounded-3xl mx-4 mt-4" data-tooltip="true" title="Google Analytics">
                    <img src="{{ asset('img/icons/pixels/' . md5('google-analytics')) }}.svg" class="w-8 h-8" alt="Google Analytics">
                </div>

                <div class="bg-base-1 d-flex w-20 h-20 position-relative align-items-center justify-content-center flex-shrink-0 rounded-3xl mx-4 mt-4" data-tooltip="true" title="Google Tag Manager">
                    <img src="{{ asset('img/icons/pixels/' . md5('google-tag-manager')) }}.svg" class="w-8 h-8" alt="Google Tag Manager">
                </div>

                <div class="bg-base-1 d-flex w-20 h-20 position-relative align-items-center justify-content-center flex-shrink-0 rounded-3xl mx-4 mt-4" data-tooltip="true" title="Facebook">
                    <img src="{{ asset('img/icons/pixels/' . md5('facebook')) }}.svg" class="w-8 h-8" alt="Facebook">
                </div>

                <div class="bg-base-1 d-flex w-20 h-20 position-relative align-items-center justify-content-center flex-shrink-0 rounded-3xl mx-4 mt-4" data-tooltip="true" title="Bing">
                    <img src="{{ asset('img/icons/pixels/' . md5('bing')) }}.svg" class="w-8 h-8" alt="Bing">
                </div>

                <div class="bg-base-1 d-flex w-20 h-20 position-relative align-items-center justify-content-center flex-shrink-0 rounded-3xl mx-4 mt-4" data-tooltip="true" title="X">
                    <img src="{{ asset('img/icons/pixels/' . md5('x')) }}.svg" class="w-8 h-8" alt="X">
                </div>

                <div class="bg-base-1 d-flex w-20 h-20 position-relative align-items-center justify-content-center flex-shrink-0 rounded-3xl mx-4 mt-4" data-tooltip="true" title="Pinterest">
                    <img src="{{ asset('img/icons/pixels/' . md5('pinterest')) }}.svg" class="w-8 h-8" alt="Pinterest">
                </div>

                <div class="bg-base-1 d-flex w-20 h-20 position-relative align-items-center justify-content-center flex-shrink-0 rounded-3xl mx-4 mt-4" data-tooltip="true" title="LinkedIn">
                    <img src="{{ asset('img/icons/pixels/' . md5('linkedin')) }}.svg" class="w-8 h-8" alt="LinkedIn">
                </div>

                <div class="bg-base-1 d-flex w-20 h-20 position-relative align-items-center justify-content-center flex-shrink-0 rounded-3xl mx-4 mt-4" data-tooltip="true" title="Quora">
                    <img src="{{ asset('img/icons/pixels/' . md5('quora')) }}.svg" class="w-8 h-8" alt="Quora">
                </div>

                <div class="bg-base-1 d-flex w-20 h-20 position-relative align-items-center justify-content-center flex-shrink-0 rounded-3xl mx-4 mt-4" data-tooltip="true" title="Adroll">
                    <img src="{{ asset('img/icons/pixels/' . md5('adroll')) }}.svg" class="w-8 h-8" alt="Adroll">
                </div>
            </div>
        </div>
    </div>

    @if(enabledPaymentProcessors())
        <div class="bg-base-1">
            <div class="container py-16 py-md-24 position-relative z-1">
                <div class="text-center">
                    <h2 class="fs-3xl fw-bold tracking-tight m-0">{{ __('Pricing') }}</h2>
                    <div class="mx-auto mt-4">
                        <p class="text-muted fw-normal fs-lg mb-0">{{ __('Simple pricing plans for everyone and every budget.') }}</p>
                    </div>
                </div>

                @include('shared.pricing')

                <div class="d-flex justify-content-center">
                    <a href="{{ route('pricing') }}" class="btn btn-outline-primary py-2 mt-12">{{ __('Learn more') }}<span class="sr-only"> {{ mb_strtolower(__('Pricing')) }}</span></a>
                </div>
            </div>
        </div>
    @else
        <div class="bg-base-1">
            <div class="container position-relative text-center py-16 py-md-24 d-flex flex-column z-1">
                <div class="flex-grow-1">
                    <div class="badge badge-pill badge-success mb-4 px-4 py-2">{{ __('Join us') }}</div>
                    <div class="text-center">
                        <h4 class="fs-3xl fw-bold tracking-tight mb-4">{{ __('Ready to get started?') }}</h4>
                        <div class="m-auto">
                            <p class="fw-normal text-muted fs-lg mb-0">{{ __('Create an account in seconds.') }}</p>
                        </div>
                    </div>
                </div>

                <div><a href="{{ config('settings.registration') ? route('register') : route('login') }}" class="btn btn-primary btn-lg fs-lg mt-12">{{ __('Get started') }}</a></div>
            </div>
        </div>
    @endif
</div>
@if($link)
    @include('shared.modals.share-link')
@endif
@endsection
