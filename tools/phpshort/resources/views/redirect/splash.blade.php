@extends('layouts.redirect')

@section('site_title', formatTitle([__('Link preview'), $link->title ?? $link->displayUrl]))

@section('head_content')
    <meta name="robots" content="noindex">

    @if($link->title)
        <meta property="og:title" content="{{ $link->title }}">
    @endif

    @if($link->description)
        <meta name="description" content="{{ $link->description }}">
        <meta property="og:description" content="{{ $link->description }}">
    @endif

    @if($link->image)
        <meta property="og:image" content="{{ $link->image }}">
    @endif

    <meta property="og:url" content="{{ $url }}">
@endsection

@section('content')
    <div class="bg-base-1 d-flex align-items-center flex-fill">
        <div class="container py-16 d-flex flex-column align-items-center justify-content-center">
            @if (config('settings.ad_splash_header'))
                @if(!Auth::check() || !Auth::user()->active_plan->features->no_ads)
                    <div class="mb-4 d-print-none">{!! config('settings.ad_splash_header') !!}</div>
                @endif
            @endif

            @if(parse_url(config('app.url'), PHP_URL_HOST) == request()->getHost())
                <div class="mb-8">
                    <a href="{{ route('dashboard') }}" aria-label="{{ config('settings.title') }}" class="navbar-brand p-0">
                        <div class="h-10 w-auto">
                            <img src="{{ asset('uploads/brand/' . (config('settings.dark_mode') == 1 ? config('settings.logo_dark') : config('settings.logo'))) }}" alt="{{ config('settings.title') }}" width="auto" height="40" data-theme-dark="{{ asset('uploads/brand/' . config('settings.logo_dark')) }}" data-theme-light="{{ asset('uploads/brand/' . config('settings.logo')) }}" data-theme-target="src" class="h-full border-0 max-h-10 object-contain max-w-48">
                        </div>
                    </a>
                </div>
            @endif

            <div class="max-w-136 w-full">
                <div class="card border-0 shadow-sm rounded-lg">
                    <div class="card-body p-6">
                        <div class="border rounded p-4">
                            <div class="row m-n1">
                                <div class="col-auto p-1 d-flex align-items-center">
                                    <img src="{{ faviconUrl($link->url) }}" rel="noreferrer" class="w-4 h-4" alt="">
                                </div>
                                <div class="col p-1 d-flex align-items-center">
                                    <div class="text-primary">{{ preg_replace('/^https?:\/\/(www\.)?/i', '', $url) }}</div>
                                </div>
                                @if ($link->title)
                                    <div class="col-12 p-1">
                                        <div class="text-break fs-xl fw-medium">{{ $link->title }}</div>
                                    </div>
                                @endif

                                @if ($link->description)
                                    <div class="col-12 p-1">
                                        <div class="small text-muted">{{ $link->description }}</div>
                                    </div>
                                @endif

                                @if ($link->image)
                                    <div class="col-12 p-1">
                                        <div class="bg-secondary rounded w-full ratio-16x9 background-size-cover bg-center" style="background-image: url('{{ $link->image }}');"></div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-8">
                            <form action="{{ route('redirect.splash', ['id' => $link->id] + request()->query()) }}" method="post" id="splash-form">
                                @csrf
                                <div class="row m-n1">
                                    <div class="col-12 p-1">
                                        <button name="continue" type="submit" value="1" class="btn btn-block btn-primary" id="continue-button" data-redirect-text="{{ __('Continue') }}" data-delay-text="{{ __('Redirect in :seconds seconds', ['seconds' => '__placeholder__']) }}" data-delay-seconds="{{ config('settings.short_splash_redirect_delay_seconds') }}" data-button-loader @if(!config('settings.short_splash_redirect_skipping') && config('settings.short_splash_redirect_delay_seconds') > 0) disabled @endif>
                                            <span class="position-absolute top-0 end-0 bottom-0 start-0 d-flex align-items-center justify-content-center">
                                                <span class="d-none spinner-border spinner-border-sm w-4 h-4" role="status"></span>
                                            </span>
                                            <span class="spinner-text">
                                                @if (config('settings.short_splash_redirect_delay_seconds') > 0)
                                                    {{ __('Redirect in :seconds seconds', ['seconds' => config('settings.short_splash_redirect_delay_seconds')]) }}
                                                @else
                                                    {{ __('Continue') }}
                                                @endif
                                            </span>
                                            &#8203;
                                        </button>
                                    </div>

                                    @if(url()->previous() != url()->current())
                                        <div class="col-12 p-1">
                                            <a href="{{ url()->previous() }}" class="btn btn-block btn-soft-secondary">{{ __('Go back') }}</a>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="text-center text-muted small mt-4">
                    <a href="{{ config('app.url') . route('contact', ['subject' => __('Transfer abuse'), 'message' => $link->shortUrl], false) }}" class="text-secondary">{{ __('Report link') }}</a>
                </div>
            </div>

            @if (config('settings.ad_splash_header'))
                @if(!Auth::check() || !Auth::user()->active_plan->features->no_ads)
                    <div class="mt-4 d-print-none">{!! config('settings.ad_splash_header') !!}</div>
                @endif
            @endif
        </div>
    </div>

    <script>
        'use strict';

        window.addEventListener('DOMContentLoaded', function () {
            if (document.querySelector('#splash-form')) {
                let splashRedirectSeconds = parseInt(document.querySelector('#continue-button').dataset.delaySeconds);

                if (splashRedirectSeconds > 0) {
                    let splashRedirectInterval = setInterval(() => {
                        splashRedirectSeconds--;
                        document.querySelector('#continue-button > span:nth-child(2)').textContent = document.querySelector('#continue-button').dataset.delayText.replace('__placeholder__', splashRedirectSeconds);

                        if (splashRedirectSeconds <= 0) {
                            document.querySelector('#continue-button > span:nth-child(2)').textContent = document.querySelector('#continue-button').dataset.redirectText;
                            document.querySelector('[name="continue"]').removeAttribute('disabled');
                            document.querySelector('[name="continue"]').click();
                            clearInterval(splashRedirectInterval);
                        }
                    }, 1000);
                }
            }
        });
    </script>
@endsection