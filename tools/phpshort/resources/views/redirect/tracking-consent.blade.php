@extends('layouts.redirect')

@section('site_title', formatTitle([$link->title ?? $link->displayUrl]))

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
            <div class="card border-0 shadow-sm">
                <div class="card-body p-6">
                    <div class="alert alert-info">
                        {{ __('This website uses cookies, to customise content and advertising, to analyse traffic and provide a better experience on it.') }}
                    </div>

                    <div class="text-muted mt-8" data-toggle="collapse">
                        {{ __('Cookies') }}
                    </div>

                    <div class="mt-4 mb-8">
                        @foreach(config('pixels') as $key => $value)
                            @if($link->pixels->contains('type', $key))
                                <div class="d-flex align-items-center text-truncate mt-4">
                                    <img src="{{ asset('img/icons/pixels/' . md5(strtolower($key))) }}.svg" rel="noreferrer" class="w-4 h-4 me-4" alt="">
                                    <div class="text-truncate me-4">{{ $value['name'] }} <span class="text-muted">({{ $value['type'] ? __('Statistics') : __('Marketing') }})</span></div>
                                    <a href="{{ $value['policy_url'] }}" target="_blank" rel="nofollow noreferrer noopener" class="text-secondary ms-auto">{{ __('View policy') }}</a>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="mt-8 mb-4 text-muted small">
                        {!! __('By clicking :button, you agree to the storing of first and third party cookies on your device.', ['button' => '<strong>' . __('Accept') . '</strong>']) !!}
                    </div>

                    <form action="{{ route('redirect.tracking', ['id' => $link->id] + request()->query()) }}" method="post">
                        @csrf
                        <div class="row m-n1">
                            <div class="col-12 p-1">
                                <button name="consent" type="submit" value="1" class="btn btn-block btn-primary">{{ __('Accept') }}</button>
                            </div>

                            <div class="col-12 p-1">
                                <button name="consent" type="submit" value="0" class="btn btn-block btn-soft-secondary">{{ __('Decline') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center text-muted small mt-4">
                <a href="{{ config('app.url') . route('contact', ['subject' => __('Transfer abuse'), 'message' => $link->shortUrl], false) }}" class="text-secondary">{{ __('Report link') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection