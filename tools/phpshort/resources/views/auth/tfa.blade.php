@extends('layouts.app')

@section('site_title', formatTitle([__('Security check'), config('settings.title')]))

@section('content')
<div class="bg-base-1 d-flex align-items-start align-items-lg-center flex-fill">
    <div class="container h-full py-16">

        <div class="text-center d-block d-lg-none">
            <h1 class="fs-3xl fw-semibold  tracking-tight m-0">{{ __('Login') }}</h1>
            <div class="mx-auto mt-4">
                <p class="text-muted fw-normal fs-lg mb-0">{{ __('Welcome back.') }}</p>
            </div>
        </div>

        <div class="row h-full justify-content-center align-items-center mt-12 mt-lg-0">
            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="row no-gutters">
                        <div class="col-12 col-lg-5">
                            <div class="card-body p-lg-12">
                                @include('shared.message')

                                @if (!request()->session()->get('success'))
                                    <div class="alert alert-secondary">
                                        {{ __('A security code has been sent to your email address.') }}
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('login.tfa.validate') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="i-code">{{ __('Code') }}</label>
                                        <input id="i-code" type="text" dir="ltr" class="form-control{{ $errors->has('code') ? ' is-invalid' : '' }}" name="code" value="{{ old('code') }}" autofocus>
                                        @if ($errors->has('code'))
                                            <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('code') }}</strong>
                                        </span>
                                        @endif
                                    </div>

                                    <button type="submit" class="btn btn-block btn-primary py-2">
                                        {{ __('Validate') }}
                                    </button>
                                </form>
                            </div>
                            <div class="card-footer bg-base-2 border-0">
                                <div class="text-center text-muted my-2">
                                    <form class="d-inline" method="POST" action="{{ route('login.tfa.resend') }}" id="resend-form">
                                        @csrf

                                        {{ __('Didn\'t receive the email?') }} <a href="{{ route('login.tfa.resend') }}" onclick="event.preventDefault(); document.getElementById('resend-form').submit();">{{ __('Resend') }}</a>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-7 d-none d-lg-flex flex-fill background-size-cover bg-center" style="background-image: url({{ asset('img/login.svg') }})">
                            <div class="card-body p-lg-12 d-flex flex-column flex-fill position-absolute top-0 end-0 bottom-0 start-0">
                                <div class="d-flex align-items-center d-flex flex-fill">
                                    <div class="text-light ms-12">
                                        <div class="fs-3xl fw-bold  tracking-tight m-0">{{ __('Security check') }}</div>
                                        <div class="fs-lg fw-medium mt-2">
                                            {{ __('Confirm your identity.') }}
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
@endsection
