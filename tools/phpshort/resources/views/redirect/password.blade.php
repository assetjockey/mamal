@extends('layouts.minimal')

@section('site_title', formatTitle([__('Link protected'), parse_url(config('app.url'), PHP_URL_HOST) == request()->getHost() ? config('settings.title') : null]))

@section('head_content')
    <meta name="robots" content="noindex">
@endsection

@section('content')
<div class="bg-base-1 d-flex align-items-center flex-fill">
    <div class="container py-16 d-flex align-items-center justify-content-center">
        <div class="max-w-136 w-full">
            <form action="{{ route('redirect.password', ['id' => $link->id]) }}" method="post">
                @csrf

                <div class="text-center">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Link protected') }}</h1>
                    <p class="mb-12 mt-4 text-muted">{{ __('This link is password protected.') }}</p>
                </div>

                <div class="row mx-n1">
                    <div class="col-12 col-sm px-1">
                        <input id="i-password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password">
                        @if ($errors->has('password'))
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="col-12 col-sm-auto mt-4 mt-sm-0 px-1">
                        <button type="submit" class="btn btn-primary btn-block">{{ __('Validate') }}</button>
                    </div>
                </div>
            </form>

            @if(url()->previous() != url()->current())
                <div class="text-center mt-12">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">{{ __('Go back') }}</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection