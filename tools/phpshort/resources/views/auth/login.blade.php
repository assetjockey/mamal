@extends('layouts.auth')

@section('site_title', formatTitle([__('Login'), config('settings.title')]))

@section('head_content')

@endsection

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

                                <form method="POST" action="{{ route('login') }}">
                                    @csrf

                                    <div class="form-group">
                                        <label for="i-email">{{ __('Email address') }}</label>
                                        <input id="i-email" type="text" dir="ltr" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" autofocus>
                                        @if ($errors->has('email'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label for="i-password">{{ __('Password') }}</label>
                                        <input id="i-password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password">
                                        @if ($errors->has('password'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-6">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="checkbox" name="remember" id="i-remember" {{ old('remember') ? 'checked' : '' }}>

                                                <label class="custom-control-label" for="i-remember">
                                                    {{ __('Remember me') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-6 text-end">
                                            @if (Route::has('password.request'))
                                                <a href="{{ route('password.request') }}">
                                                    {{ __('Forgot password?') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-block btn-primary py-2">
                                        {{ __('Login') }}
                                    </button>
                                </form>

                                @include('auth.partials.social')
                            </div>
                            @if(config('settings.registration'))
                                <div class="card-footer bg-base-2 border-0">
                                    <div class="text-center text-muted my-2">{{ __('Don\'t have an account?') }} <a href="{{ route('register') }}" role="button">{{ __('Register') }}</a></div>
                                </div>
                            @endif
                        </div>
                        <div class="col-12 col-lg-7 d-none d-lg-flex flex-fill background-size-cover bg-center" style="background-image: url({{ asset('img/login.svg') }})">
                            <div class="card-body p-lg-12 d-flex flex-column flex-fill position-absolute top-0 end-0 bottom-0 start-0">
                                <div class="d-flex align-items-center d-flex flex-fill">
                                    <div class="text-light ms-12">
                                        <div class="fs-3xl fw-bold  tracking-tight m-0">{{ __('Login') }}</div>
                                        <div class="fs-lg fw-medium mt-2">
                                            {{ __('Welcome back.') }}
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
